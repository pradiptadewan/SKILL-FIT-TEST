<?php

namespace App\Services;

use App\Enums\DueStatus;
use App\Models\FeeType;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private MonthlyDueService $monthlyDues)
    {
    }

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $startMonth = Carbon::createFromFormat('Y-m', $data['start_month'])->startOfMonth();
            $feeTypes = FeeType::query()->whereIn('id', $data['fee_type_ids'])->where('is_active', true)->get();

            if ($feeTypes->count() !== count($data['fee_type_ids'])) {
                throw ValidationException::withMessages(['fee_type_ids' => 'Jenis iuran tidak valid atau sudah nonaktif.']);
            }

            $dues = collect(range(0, $data['month_count'] - 1))->flatMap(function (int $offset) use ($data, $startMonth, $feeTypes) {
                $month = $startMonth->copy()->addMonths($offset);
                $history = $this->monthlyDues->findOccupancy($data['house_id'], $data['resident_id'], $month);

                if (! $history) {
                    throw ValidationException::withMessages([
                        'start_month' => "Penghuni tidak tercatat menempati rumah pada bulan {$month->format('Y-m')}.",
                    ]);
                }

                return $feeTypes->map(fn (FeeType $feeType) => $this->monthlyDues->firstOrCreate($history, $feeType, $month));
            });

            $dues = $dues->pluck('id')
                ->pipe(fn ($ids) => \App\Models\MonthlyDue::query()->whereIn('id', $ids)->lockForUpdate()->get());

            if ($dues->contains(fn ($due) => $due->status === DueStatus::Paid)) {
                throw ValidationException::withMessages(['start_month' => 'Sebagian tagihan pada periode tersebut sudah lunas.']);
            }

            $payment = Payment::create([
                'resident_id' => $data['resident_id'],
                'house_id' => $data['house_id'],
                'paid_at' => $data['paid_at'],
                'total_amount' => $dues->sum('amount'),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($dues as $due) {
                $due->update(['status' => DueStatus::Paid, 'paid_at' => $data['paid_at']]);
                $payment->details()->create(['monthly_due_id' => $due->id, 'amount' => $due->amount]);
            }

            return $payment->load(['resident', 'house', 'details.monthlyDue.feeType']);
        });
    }
}

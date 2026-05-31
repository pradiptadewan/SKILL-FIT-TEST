<?php

namespace Tests\Feature;

use App\Models\FeeType;
use App\Models\House;
use App\Models\Resident;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdministrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_rt_administration_flow_supports_multi_month_payment_and_reports(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $residentResponse = $this->postJson('/api/residents', [
            'full_name' => 'Budi Santoso',
            'resident_status' => 'permanent',
            'phone_number' => '081234567890',
            'is_married' => true,
            'ktp_photo' => UploadedFile::fake()->image('ktp.jpg'),
        ])->assertCreated();
        $resident = Resident::query()->findOrFail($residentResponse->json('data.id'));
        Storage::disk('public')->assertExists($resident->ktp_photo_path);

        $house = House::query()->create(['house_number' => 'A-01']);
        $this->postJson("/api/houses/{$house->id}/occupancies", [
            'resident_id' => $resident->id,
            'started_at' => '2026-01-01',
        ])->assertCreated();

        $this->postJson('/api/monthly-dues/generate', ['month' => '2026-01'])
            ->assertOk()
            ->assertJson(['generated_count' => 2]);

        $feeTypeIds = FeeType::query()->pluck('id')->all();
        $this->postJson('/api/payments', [
            'resident_id' => $resident->id,
            'house_id' => $house->id,
            'start_month' => '2026-01',
            'month_count' => 2,
            'fee_type_ids' => $feeTypeIds,
            'paid_at' => '2026-01-10',
        ])->assertCreated()->assertJsonPath('data.total_amount', 230000);

        $this->postJson('/api/expenses', [
            'name' => 'Gaji satpam',
            'amount' => 100000,
            'expense_date' => '2026-01-20',
            'description' => 'Pembayaran rutin Januari',
        ])->assertCreated();

        $this->getJson('/api/reports/monthly?month=2026-01')
            ->assertOk()
            ->assertJsonPath('data.summary.income', 230000)
            ->assertJsonPath('data.summary.expenses', 100000)
            ->assertJsonPath('data.summary.ending_balance', 130000);

        Carbon::setTestNow('2026-01-25');
        $this->getJson('/api/dashboard?year=2026')
            ->assertOk()
            ->assertJsonPath('data.summary.monthly_income', 230000)
            ->assertJsonPath('data.summary.monthly_expenses', 100000)
            ->assertJsonPath('data.summary.ending_balance', 130000);
        Carbon::setTestNow();
    }

    public function test_house_rejects_second_active_resident(): void
    {
        $house = House::query()->create(['house_number' => 'A-01']);
        $first = Resident::query()->create($this->residentData('Budi'));
        $second = Resident::query()->create($this->residentData('Siti'));

        $this->postJson("/api/houses/{$house->id}/occupancies", [
            'resident_id' => $first->id,
            'started_at' => '2026-01-01',
        ])->assertCreated();

        $this->postJson("/api/houses/{$house->id}/occupancies", [
            'resident_id' => $second->id,
            'started_at' => '2026-02-01',
        ])->assertUnprocessable()->assertJsonValidationErrors('house_id');
    }

    private function residentData(string $name): array
    {
        return [
            'full_name' => $name,
            'ktp_photo_path' => "ktp-photos/{$name}.jpg",
            'resident_status' => 'contract',
            'phone_number' => '081234567890',
            'is_married' => false,
        ];
    }
}

<?php

namespace App\Repositories;

use App\Enums\DueStatus;
use App\Models\Expense;
use App\Models\MonthlyDue;
use App\Models\Payment;
use App\Repositories\Contracts\MonthlyReportRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class EloquentMonthlyReportRepository implements MonthlyReportRepositoryInterface
{
    public function getMonthlyReport(CarbonInterface $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $dues = MonthlyDue::query()
            ->with(['house', 'resident', 'feeType'])
            ->where('billing_month', $start->toDateString())
            ->orderBy('billing_month')
            ->get();
        $payments = Payment::query()
            ->with(['house', 'resident', 'details.monthlyDue.feeType'])
            ->whereBetween('paid_at', [$start->toDateString(), $end->toDateString()])
            ->orderBy('paid_at')
            ->get();
        $expenses = Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('expense_date')
            ->get();

        return [
            'month' => $month->format('Y-m'),
            'summary' => [
                'income' => $payments->sum('total_amount'),
                'expenses' => $expenses->sum('amount'),
                'unpaid' => $dues->filter(fn (MonthlyDue $due) => $due->status === DueStatus::Unpaid)->sum('amount'),
                'ending_balance' => $this->balanceUntil($end),
            ],
            'dues' => $dues,
            'payments' => $payments,
            'expenses' => $expenses,
        ];
    }

    public function getDashboard(int $year): array
    {
        $months = collect(range(1, 12))->map(function (int $month) use ($year) {
            $date = Carbon::create($year, $month)->startOfMonth();
            $end = $date->copy()->endOfMonth();

            return [
                'month' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M'),
                'income' => Payment::query()
                    ->whereBetween('paid_at', [$date->toDateString(), $end->toDateString()])
                    ->sum('total_amount'),
                'expenses' => Expense::query()
                    ->whereBetween('expense_date', [$date->toDateString(), $end->toDateString()])
                    ->sum('amount'),
            ];
        });
        $currentMonth = Carbon::now()->year($year)->startOfMonth();
        $current = $months->firstWhere('month', $currentMonth->format('Y-m')) ?? $months->last();

        return [
            'year' => $year,
            'current_month' => $currentMonth->format('Y-m'),
            'summary' => [
                'monthly_income' => $current['income'],
                'monthly_expenses' => $current['expenses'],
                'ending_balance' => $this->balanceUntil($currentMonth->copy()->endOfMonth()),
            ],
            'chart' => $months,
        ];
    }

    private function balanceUntil(CarbonInterface $date): int
    {
        $income = Payment::query()->whereDate('paid_at', '<=', $date)->sum('total_amount');
        $expenses = Expense::query()->whereDate('expense_date', '<=', $date)->sum('amount');

        return $income - $expenses;
    }
}

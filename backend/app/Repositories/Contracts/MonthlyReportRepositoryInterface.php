<?php

namespace App\Repositories\Contracts;

use Carbon\CarbonInterface;

interface MonthlyReportRepositoryInterface
{
    public function getMonthlyReport(CarbonInterface $month): array;

    public function getDashboard(int $year): array;
}

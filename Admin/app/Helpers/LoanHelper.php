<?php

namespace App\Helpers;

use Carbon\Carbon;

class LoanHelper
{
    /**
     * Calculate monthly EMI (Equated Monthly Installment)
     * Formula: EMI = [P × R × (1+R)^N] / [(1+R)^N - 1]
     * Where:
     * P = Principal (loan amount)
     * R = Monthly interest rate (annual rate / 12 / 100)
     * N = Number of months (tenor)
     *
     * @param float $principal Principal loan amount
     * @param float $annualInterestRate Annual interest rate as percentage (e.g., 24 for 24%)
     * @param int $tenorInMonths Number of months
     * @return float Monthly EMI amount
     */
    public static function calculateEMI(float $principal, float $annualInterestRate, int $tenorInMonths): float
    {
        $monthlyRate = $annualInterestRate / 12 / 100;

        if ($monthlyRate == 0) {
            return round($principal / $tenorInMonths, 2);
        }

        $numerator = $principal * $monthlyRate * pow(1 + $monthlyRate, $tenorInMonths);
        $denominator = pow(1 + $monthlyRate, $tenorInMonths) - 1;

        if ($denominator == 0) {
            return round($principal / $tenorInMonths, 2);
        }

        return round($numerator / $denominator, 2);
    }

    /**
     * Generate payment schedule dates
     * Uses proper date calculation to avoid month overflow issues
     *
     * @param Carbon $startDate Application date
     * @param int $tenorInMonths Number of months
     * @param float $emiAmount Monthly payment amount
     * @return array Array of ['dueDate' => Carbon, 'amount' => float]
     */
    public static function generatePaymentSchedule(Carbon $startDate, int $tenorInMonths, float $emiAmount): array
    {
        $schedule = [];
        $targetDay = $startDate->day;

        for ($i = 1; $i <= $tenorInMonths; $i++) {
            // Create due date by adding months (matching TypeScript implementation)
            // Start with first day of target month, then set to target day or last day of month
            $dueDate = $startDate->copy()->addMonths($i);
            $dueDate->startOfMonth(); // Set to first day of target month

            // Handle month overflow - if target day doesn't exist, use last day of month
            // e.g., Jan 31 -> Feb (use Feb 28/29, not Mar 3)
            $lastDayOfMonth = $dueDate->copy()->endOfMonth()->day;
            $dueDate->day(min($targetDay, $lastDayOfMonth));

            // Reset time to midnight for date-only storage
            $dueDate->startOfDay();

            $schedule[] = [
                'dueDate' => $dueDate->copy(),
                'amount' => round($emiAmount * 100) / 100, // Round to 2 decimal places for precision (matching TypeScript)
            ];
        }

        return $schedule;
    }

    /**
     * Generate loan reference (MF-YYYY-XXXX format)
     *
     * @return string Loan reference
     */
    public static function generateLoanReference(): string
    {
        $year = date('Y');
        $lastLoan = \App\Models\Loan::where('reference', 'like', "MF-{$year}-%")
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastLoan) {
            if (preg_match('/MF-\d{4}-(\d+)/', $lastLoan->reference, $matches)) {
                $sequence = (int) $matches[1] + 1;
            }
        }

        return sprintf('MF-%s-%04d', $year, $sequence);
    }

    /**
     * Calculate maturity date from application date and tenor
     *
     * @param Carbon $applicationDate
     * @param int $tenorInMonths
     * @return Carbon
     */
    public static function calculateMaturityDate(Carbon $applicationDate, int $tenorInMonths): Carbon
    {
        $targetDay = $applicationDate->day;
        $maturityDate = $applicationDate->copy()->addMonths($tenorInMonths)->startOfMonth();

        $lastDayOfMonth = $maturityDate->copy()->endOfMonth()->day;
        $maturityDate->day(min($targetDay, $lastDayOfMonth));

        return $maturityDate->startOfDay();
    }
}


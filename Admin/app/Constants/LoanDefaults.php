<?php

namespace App\Constants;

/**
 * Default values for loan creation
 * These should match the defaults used in User side
 */
class LoanDefaults
{
    // Interest rate as percentage (24 = 24%)
    public const INTEREST_RATE_PERCENT = 24;
    
    // Interest rate as decimal (0.24 = 24%)
    public const INTEREST_RATE_DECIMAL = 0.24;
    
    // Penalty settings
    public const PENALTY_GRACE_DAYS = 0;
    public const PENALTY_DAILY_RATE = 0.001000; // 0.1% per day
    
    // Loan amounts
    public const MIN_LOAN_AMOUNT = 3500;
    public const MAX_LOAN_AMOUNT = 50000;
    
    // Tenor
    public const MIN_TENOR = 1;
    public const MAX_TENOR = 18;
    
    // Remarks
    public const REMARKS_MOBILE_APP = 'Mobile app application';
    public const REMARKS_ADMIN_PANEL = 'Admin panel application';
}


/**
 * Default values for loan creation
 * These should match the defaults used in Admin side
 */
export const LoanDefaults = {
  // Interest rate as decimal (0.24 = 24%)
  INTEREST_RATE: 0.24,
  
  // Penalty settings
  PENALTY_GRACE_DAYS: 0,
  PENALTY_DAILY_RATE: 0.001000, // 0.1% per day
  
  // Loan amounts
  MIN_LOAN_AMOUNT: 3500,
  MAX_LOAN_AMOUNT: 50000,
  
  // Tenor
  MIN_TENOR: 1,
  MAX_TENOR: 18,
  
  // Remarks
  REMARKS_MOBILE_APP: 'Mobile app application',
  REMARKS_ADMIN_PANEL: 'Admin panel application',
} as const;


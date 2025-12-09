/**
 * Loan Application Configuration
 * Centralized configuration for loan rules, rates, and validation
 */

export const LOAN_CONFIG = {
  // Interest Rate (Annual Percentage Rate)
  INTEREST_RATE: {
    ANNUAL_PERCENT: 24, // 24% annual interest rate
    ANNUAL_DECIMAL: 0.24, // Decimal representation (0.24 = 24%)
  },

  // Loan Amount Limits
  AMOUNT: {
    MIN: 3500, // Minimum loan amount in PHP
    MAX: 50000, // Maximum loan amount in PHP
  },

  // Tenor Options (in months)
  TENOR: {
    OPTIONS: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18] as const, // Allowed tenor values (1-18 months)
    MIN: 1, // Minimum tenor
    MAX: 18, // Maximum tenor
  },

  // EMI Calculation
  EMI: {
    ROUNDING_DECIMALS: 2, // Number of decimal places for EMI
  },
} as const;

/**
 * Validates if a tenor value is allowed
 */
export const isValidTenor = (tenor: number): boolean => {
  return (
    tenor >= LOAN_CONFIG.TENOR.MIN &&
    tenor <= LOAN_CONFIG.TENOR.MAX &&
    Number.isInteger(tenor)
  );
};

/**
 * Calculates monthly EMI (Equated Monthly Installment)
 * Formula: EMI = [P × R × (1+R)^N] / [(1+R)^N - 1]
 * Where:
 *   P = Principal (loan amount)
 *   R = Monthly interest rate (annual rate / 12 / 100)
 *   N = Number of months (tenor)
 * 
 * @param principal - Loan amount
 * @param tenor - Number of months
 * @returns EMI amount rounded to 2 decimal places
 */
export const calculateEMI = (principal: number, tenor: number): number => {
  const annualRate = LOAN_CONFIG.INTEREST_RATE.ANNUAL_PERCENT;
  const monthlyRate = annualRate / 12 / 100;

  // Handle zero interest rate
  if (monthlyRate === 0) {
    return Math.round((principal / tenor) * 100) / 100;
  }

  const numerator = principal * monthlyRate * Math.pow(1 + monthlyRate, tenor);
  const denominator = Math.pow(1 + monthlyRate, tenor) - 1;

  if (denominator === 0) {
    return Math.round((principal / tenor) * 100) / 100;
  }

  // Round to 2 decimal places for currency precision
  return Math.round((numerator / denominator) * 100) / 100;
};

/**
 * Validates loan amount
 */
export const isValidLoanAmount = (amount: number): boolean => {
  return (
    amount >= LOAN_CONFIG.AMOUNT.MIN &&
    amount <= LOAN_CONFIG.AMOUNT.MAX
  );
};

/**
 * Gets interest rate as decimal (for backend API)
 */
export const getInterestRateDecimal = (): number => {
  return LOAN_CONFIG.INTEREST_RATE.ANNUAL_DECIMAL;
};

/**
 * Gets interest rate as percentage (for display)
 */
export const getInterestRatePercent = (): number => {
  return LOAN_CONFIG.INTEREST_RATE.ANNUAL_PERCENT;
};


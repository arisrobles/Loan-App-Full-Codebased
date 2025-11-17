/**
 * Calculate monthly EMI (Equated Monthly Installment)
 * Formula: EMI = [P × R × (1+R)^N] / [(1+R)^N - 1]
 * Where:
 * P = Principal (loan amount)
 * R = Monthly interest rate (annual rate / 12 / 100)
 * N = Number of months (tenor)
 */
export const calculateEMI = (
  principal: number,
  annualInterestRate: number,
  tenorInMonths: number
): number => {
  const monthlyRate = annualInterestRate / 12 / 100;
  const numerator = principal * monthlyRate * Math.pow(1 + monthlyRate, tenorInMonths);
  const denominator = Math.pow(1 + monthlyRate, tenorInMonths) - 1;

  if (denominator === 0) {
    return Math.round((principal / tenorInMonths) * 100) / 100; // Round to 2 decimal places
  }

  // Round to 2 decimal places to match currency precision
  return Math.round((numerator / denominator) * 100) / 100;
};

/**
 * Calculate disbursed amount (typically 90% of loan amount)
 */
export const calculateDisbursedAmount = (
  loanAmount: number,
  disbursementPercentage: number = 0.9
): number => {
  return loanAmount * disbursementPercentage;
};

/**
 * Generate payment schedule dates
 * Uses proper date calculation to avoid month overflow issues
 */
export const generatePaymentSchedule = (
  startDate: Date,
  tenorInMonths: number,
  emiAmount: number
): Array<{ dueDate: Date; amount: number }> => {
  const schedule = [];
  const start = new Date(startDate);
  const targetDay = start.getDate(); // Day of month to use for due dates

  for (let i = 1; i <= tenorInMonths; i++) {
    // Create due date by adding months
    const dueDate = new Date(start.getFullYear(), start.getMonth() + i, 1);
    
    // Set to same day of month as application date, or last day of month if doesn't exist
    // This handles cases like Jan 31 -> Feb (use Feb 28/29, not Mar 3)
    const lastDayOfMonth = new Date(dueDate.getFullYear(), dueDate.getMonth() + 1, 0).getDate();
    dueDate.setDate(Math.min(targetDay, lastDayOfMonth));
    
    // Reset time to midnight for date-only storage
    dueDate.setHours(0, 0, 0, 0);
    
    schedule.push({
      dueDate,
      amount: Math.round(emiAmount * 100) / 100, // Round to 2 decimal places for precision
    });
  }

  return schedule;
};



/**
 * Loan Status Constants
 * These match the LoanStatus enum in Prisma schema and should be kept in sync
 * with Admin/app/Models/Loan.php constants
 */
export const LoanStatus = {
  NEW_APPLICATION: 'new_application',
  UNDER_REVIEW: 'under_review',
  APPROVED: 'approved',
  FOR_RELEASE: 'for_release',
  DISBURSED: 'disbursed',
  CLOSED: 'closed',
  REJECTED: 'rejected',
  CANCELLED: 'cancelled',
  RESTRUCTURED: 'restructured',
} as const;

export type LoanStatusType = typeof LoanStatus[keyof typeof LoanStatus];

/**
 * Get display name for loan status
 */
export const getLoanStatusDisplay = (status: string): string => {
  const statusMap: Record<string, string> = {
    [LoanStatus.NEW_APPLICATION]: 'New Application',
    [LoanStatus.UNDER_REVIEW]: 'Under Review',
    [LoanStatus.APPROVED]: 'Approved',
    [LoanStatus.FOR_RELEASE]: 'For Release',
    [LoanStatus.DISBURSED]: 'Disbursed',
    [LoanStatus.CLOSED]: 'Closed',
    [LoanStatus.REJECTED]: 'Rejected',
    [LoanStatus.CANCELLED]: 'Cancelled',
    [LoanStatus.RESTRUCTURED]: 'Restructured',
  };
  return statusMap[status] || status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

/**
 * Check if status is a pending/active status (not closed, rejected, or cancelled)
 */
export const isActiveLoanStatus = (status: string): boolean => {
  return [
    LoanStatus.NEW_APPLICATION,
    LoanStatus.UNDER_REVIEW,
    LoanStatus.APPROVED,
    LoanStatus.FOR_RELEASE,
    LoanStatus.DISBURSED,
  ].includes(status as LoanStatusType);
};

/**
 * Check if status allows cancellation
 */
export const isCancellableStatus = (status: string): boolean => {
  return [
    LoanStatus.NEW_APPLICATION,
    LoanStatus.UNDER_REVIEW,
  ].includes(status as LoanStatusType);
};


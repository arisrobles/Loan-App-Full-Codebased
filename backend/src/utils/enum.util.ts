/**
 * Utility functions to handle enum conversions between Prisma and MySQL database
 * MySQL enums can have spaces/special chars that Prisma enum identifiers cannot
 */

// BorrowerSex enum mapping
export const BorrowerSexMap = {
  Male: 'Male',
  Female: 'Female',
  PreferNotToSay: 'Prefer not to say',
} as const;

export const BorrowerSexMapReverse = {
  'Male': 'Male',
  'Female': 'Female',
  'Prefer not to say': 'PreferNotToSay',
} as const;

// ChartReport enum mapping
export const ChartReportMap = {
  BalanceSheets: 'Balance Sheets',
  ProfitAndLosses: 'Profit and Losses',
} as const;

export const ChartReportMapReverse = {
  'Balance Sheets': 'BalanceSheets',
  'Profit and Losses': 'ProfitAndLosses',
} as const;

// ChartGroupAccount enum mapping
export const ChartGroupAccountMap = {
  Assets: 'Assets',
  Liabilities: 'Liabilities',
  Equity: 'Equity',
  Revenue: 'Revenue (Income)',
  ExpenseCOGS: 'Expense (COGS)',
  Expenses: 'Expenses',
} as const;

export const ChartGroupAccountMapReverse = {
  'Assets': 'Assets',
  'Liabilities': 'Liabilities',
  'Equity': 'Equity',
  'Revenue (Income)': 'Revenue',
  'Expense (COGS)': 'ExpenseCOGS',
  'Expenses': 'Expenses',
} as const;

/**
 * Convert Prisma enum value to MySQL database value
 */
export function toDbEnum<T extends keyof typeof BorrowerSexMap>(value: T): string {
  return BorrowerSexMap[value] || value;
}

/**
 * Convert MySQL database value to Prisma enum value
 */
export function fromDbEnum(value: string): string {
  return (BorrowerSexMapReverse as any)[value] || value;
}


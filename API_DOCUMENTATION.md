# API Documentation - Loan Management System

## Interest Rate Format

### Standard Format
- **API (User Backend)**: Accepts and stores as **decimal** (e.g., `0.24` for 24%)
- **Admin Forms**: Accepts as **percentage** (e.g., `24` for 24%) and converts to decimal for storage

### Examples
- 24% annual rate:
  - API input: `0.24`
  - Admin form input: `24`
  - Database storage: `0.24`

### Conversion
- Form to API: `percentage / 100` (e.g., 24 → 0.24)
- API to Form: `decimal * 100` (e.g., 0.24 → 24)

---

## Application Date

### Standard Format
- **Format**: ISO 8601 date string (`YYYY-MM-DD`)
- **User API**: Optional parameter. If not provided, uses current date
- **Admin**: Required field, can be any date

### Examples
- `"2025-01-15"` - January 15, 2025
- If omitted in user API, defaults to today's date

---

## Loan Status Values

All status values use snake_case format:

- `new_application` - New loan application
- `under_review` - Application under review
- `approved` - Loan approved
- `for_release` - Ready for release
- `disbursed` - Loan disbursed
- `closed` - Loan closed/paid off
- `rejected` - Application rejected
- `cancelled` - Application cancelled
- `restructured` - Loan restructured

---

## Tenor (Loan Term)

- **Range**: 1-18 months
- **Type**: Integer
- **Validation**: Must be between 1 and 18 (inclusive)

---

## Loan Amount

- **Range**: ₱3,500 - ₱50,000
- **Type**: Decimal (2 decimal places)
- **Validation**: Minimum 3500, Maximum 50000

---

## Guarantor Information

### Required Fields (if guarantor is provided)
- `fullName` (string, min 1 character)
- `address` (string, min 1 character)
- `civilStatus` (string, optional)

### Validation
- If guarantor is provided, both `fullName` and `address` must be non-empty
- If either is empty, guarantor will not be created

---

## Location Information

### Fields
- `latitude` (number, optional): Decimal between -90 and 90
- `longitude` (number, optional): Decimal between -180 and 180
- `locationAddress` (string, optional): Max 255 characters

---

## Error Response Format

### User API (Zod Validation)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": [
    {
      "path": "field.name",
      "message": "Error message",
      "code": "error_code"
    }
  ]
}
```

### Admin (Laravel Validation)
- Returns HTML form with error messages
- Errors accessible via `$errors` variable in Blade templates

---

## Default Values

### Interest Rate
- **Default**: 24% (0.24 as decimal)
- **Admin**: Can be overridden in form
- **User API**: Can be provided, defaults to 0.24

### Penalty Settings
- **Penalty Grace Days**: 0 (default)
- **Penalty Daily Rate**: 0.001000 (0.1% per day, default)

### Loan Status
- **New Loans**: `new_application`
- **Is Active**: `true` (for new loans)

---

## Loan Reference Format

- **Format**: `MF-YYYY-XXXX`
- **Example**: `MF-2025-0001`
- **Generation**: Auto-generated, sequential per year
- **Uniqueness**: Guaranteed via database locking

---

## Notes

1. All monetary values are stored with 2 decimal places
2. All dates are stored as date-only (no time component)
3. Interest rate calculations use annual rate converted to monthly: `(annualRate / 12) / 100`
4. EMI calculation uses standard formula: `[P × R × (1+R)^N] / [(1+R)^N - 1]`


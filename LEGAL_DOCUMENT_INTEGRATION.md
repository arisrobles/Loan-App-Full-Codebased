# Legal Document Integration - Loan Agreement System

## Overview
This document describes the integration of a comprehensive legal document generation system for loan agreements and demand letters, following Philippine legal standards.

## Features Implemented

### 1. Backend Document Generation Service
**Location:** `User/backend/src/utils/documentGenerator.util.ts`

- **Loan Agreement Generator**: Creates legally compliant Personal Loan Agreements with:
  - Borrower and Lender information
  - Loan amount in words and figures
  - Interest rate specifications
  - Payment schedule
  - Default and penalty clauses
  - Notarization acknowledgment section

- **Demand Letter Generator**: Generates final demand letters for overdue loans with:
  - Missed payment details
  - Total amount due calculation
  - Legal compliance notice
  - Deadline for payment

### 2. API Endpoints
**Location:** `User/backend/src/routes/legal.routes.ts`

- `POST /api/v1/legal/agreement` - Generate loan agreement
- `POST /api/v1/legal/demand-letter` - Generate demand letter

Both endpoints require authentication and validate borrower information completeness.

### 3. Frontend Integration

#### Loan Application Details Screen
**Location:** `User/frontend/app/(tabs)/loan_application_details.tsx`

New screen that allows borrowers to:
- Update their profile information (required for legal documents)
- Fill in missing details (address, civil status, etc.)
- Generate the loan agreement after loan application
- View/download the generated agreement

#### Updated Loan Request Flow
**Location:** `User/frontend/app/(tabs)/loan_request.tsx`

After successful loan submission, users are prompted to:
- Complete their profile information
- Generate the legal loan agreement
- Navigate to the application details screen

## Required Borrower Information

For legal document generation, borrowers must provide:
- **Full Name** (required)
- **Complete Address** (required)
- **Civil Status** (required): Single, Married, Widowed, Divorced, Separated
- **Phone Number** (optional)
- **Email** (optional)

## Lender Configuration

Lender information can be configured via environment variables:
- `LENDER_NAME` - Company/Lender name (default: "MasterFunds")
- `LENDER_ADDRESS` - Lender address (default: "Manila City, Philippines")
- `LENDER_EMAIL` - Contact email (default: "info@masterfunds.com")
- `LENDER_PHONE` - Contact phone (default: "0998-765-4321")

## Document Format

### Loan Agreement Includes:
1. Header with Republic of the Philippines and City
2. Parties section (Lender and Borrower details)
3. Loan amount in words and figures
4. Interest rate specification
5. Payment schedule
6. Default clause
7. Penalty for late payment (default: 10%)
8. Cost and fees clause
9. Separability clause
10. Miscellaneous provisions
11. Signature section
12. Notarization acknowledgment

### Demand Letter Includes:
1. Date and borrower information
2. Loan reference and amount
3. List of missed payments
4. Total amount due
5. Compliance deadline
6. Legal action warning
7. Contact information

## Usage Flow

1. **Loan Application**: User submits loan application via `loan_request.tsx`
2. **Profile Completion**: System prompts user to complete profile if missing required fields
3. **Agreement Generation**: User navigates to `loan_application_details.tsx` to:
   - Update profile information
   - Generate the loan agreement
   - View/download the agreement
4. **Document Storage**: Generated agreements are saved as documents in the database

## API Usage Examples

### Generate Loan Agreement
```typescript
POST /api/v1/legal/agreement
{
  "loanId": "123",
  "city": "Manila",
  "penaltyRate": 0.10  // Optional, default 10%
}

Response:
{
  "success": true,
  "data": {
    "agreement": "...", // Full agreement text
    "loanReference": "MF-2025-0001",
    "generatedAt": "2025-01-30T..."
  }
}
```

### Generate Demand Letter
```typescript
POST /api/v1/legal/demand-letter
{
  "loanId": "123",
  "daysToComply": 5  // Optional, default 5 days
}

Response:
{
  "success": true,
  "data": {
    "letter": "...", // Full demand letter text
    "loanReference": "MF-2025-0001",
    "totalDue": 22000.00,
    "missedPaymentsCount": 2,
    "generatedAt": "2025-01-30T..."
  }
}
```

## Error Handling

The system validates:
- Borrower authentication
- Loan ownership (borrower must own the loan)
- Required borrower information (address, civil status)
- Loan existence
- Overdue payments (for demand letters)

## Future Enhancements

1. PDF generation for documents
2. Digital signature integration
3. Email delivery of documents
4. Document templates customization
5. Multi-language support
6. Integration with notary services

## Notes

- All amounts are formatted in Philippine Peso (Php)
- Dates follow Philippine date format
- Legal text follows standard Philippine contract language
- Documents are generated as plain text (can be converted to PDF in future)


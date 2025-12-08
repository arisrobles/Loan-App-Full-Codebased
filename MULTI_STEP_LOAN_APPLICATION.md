# Multi-Step Loan Application System

## Overview
The loan application process has been redesigned as a **required multi-step wizard** that ensures all necessary documents and information are collected before loan submission.

## Application Flow

### Step 1: Loan Details
**Screen:** `loan_application_wizard.tsx` (Step 1)

**Required Actions:**
- Select loan amount (₱3,500 - ₱50,000)
- Choose tenor (6, 12, or 36 months)
- Get application location

**Validation:**
- Loan amount must be between ₱3,500 and ₱50,000
- Tenor must be selected

**Next:** User can proceed to Step 2 only after completing this step.

---

### Step 2: Required Documents
**Screen:** `loan_application_wizard.tsx` (Step 2)

**Required Actions:**
- Upload **Primary ID** (e.g., Driver's License, Passport, National ID)
- Upload **Secondary ID** (e.g., TIN, SSS, PhilHealth, Postal ID)

**Validation:**
- Both Primary ID and Secondary ID must be uploaded
- Documents are uploaded immediately to the backend
- Documents are stored with type `PRIMARY_ID` and `SECONDARY_ID`

**Next:** User can proceed to Step 3 only after uploading both documents.

---

### Step 3: Borrower Information
**Screen:** `loan_application_wizard.tsx` (Step 3)

**Required Actions:**
- Enter **Full Name** (required)
- Enter **Complete Address** (required)
- Select **Civil Status** (required): Single, Married, Widowed, Divorced, Separated
- Enter Phone Number (optional)
- Enter Email (optional)

**Validation:**
- Full Name, Address, and Civil Status are required
- Profile is updated in the backend before proceeding

**Next:** User can proceed to Step 4 only after completing required fields.

---

### Step 4: Generate Loan Agreement
**Screen:** `loan_application_wizard.tsx` (Step 4)

**Required Actions:**
- Enter City for agreement (default: Manila)
- Generate loan agreement

**Process:**
1. **Loan Creation:** Loan is created in the backend (validates all requirements)
2. **Agreement Generation:** Legal loan agreement is generated using the document generator
3. **Document Storage:** Agreement is saved as a document linked to the loan

**Backend Validation (Before Loan Creation):**
- ✅ Required documents exist (Primary ID, Secondary ID)
- ✅ Borrower information is complete (address, civil status)
- ✅ No existing pending/active loans

**Next:** User can proceed to Step 5 after agreement is generated.

---

### Step 5: Review & Submit
**Screen:** `loan_application_wizard.tsx` (Step 5)

**Display:**
- Loan details summary
- Document upload status
- Borrower information summary
- Agreement generation status

**Final Action:**
- User reviews all information
- Confirms submission
- Application is finalized

**Result:**
- Loan application is submitted
- User is redirected to dashboard
- Loan status: `new_application`

---

## Backend Validation

### Loan Creation Validation (`loan.controller.ts`)

Before creating a loan, the backend validates:

1. **Existing Loans Check:**
   - No pending/active loans for the borrower
   - Prevents duplicate applications

2. **Required Documents Check:**
   ```typescript
   - PRIMARY_ID document must exist
   - SECONDARY_ID document must exist
   ```

3. **Borrower Information Check:**
   ```typescript
   - Address must be provided
   - Civil Status must be provided
   ```

**Error Responses:**
- `400` - Missing documents or incomplete information
- `400` - Existing loan found
- `401` - Not authenticated

---

## User Experience

### Progress Indicator
- Visual progress bar showing current step (1-5)
- Active steps are highlighted
- Completed steps are marked

### Navigation
- **Back Button:** Available from step 2 onwards
- **Next Button:** Only enabled when current step is complete
- **Validation:** Real-time validation prevents progression with incomplete data

### Error Handling
- Clear error messages for missing requirements
- Automatic navigation to relevant step when validation fails
- Helpful guidance on what needs to be completed

---

## File Structure

```
User/frontend/app/(tabs)/
├── loan_application_wizard.tsx  # Main multi-step wizard
└── loan_request.tsx             # Redirects to wizard

User/backend/src/
├── controllers/
│   ├── loan.controller.ts       # Validates documents before loan creation
│   └── legal.controller.ts      # Generates agreements
└── utils/
    └── documentGenerator.util.ts # Legal document generation
```

---

## Key Features

1. **Required Documents:** Cannot proceed without uploading Primary and Secondary IDs
2. **Complete Information:** Borrower details must be filled before agreement generation
3. **Legal Compliance:** Agreement is automatically generated with all required information
4. **Validation:** Backend validates all requirements before loan creation
5. **User Guidance:** Clear step-by-step process with progress indicators

---

## Migration Notes

- Old `loan_request.tsx` now redirects to the wizard
- Existing loan applications are not affected
- New applications must follow the multi-step process
- Documents uploaded in Step 2 are immediately saved to the database

---

## Future Enhancements

1. Save progress (allow users to resume later)
2. Document preview before upload
3. Agreement PDF download
4. Email agreement to borrower
5. Digital signature integration


# Payment History Investigation & Implementation Guide

## Current Implementation

### How Payments Work Currently:

1. **Loan Creation:**
   - When a loan is created, repayment schedules are automatically generated
   - Each repayment has: `amountDue`, `amountPaid` (starts at 0), `dueDate`, `paidAt`

2. **Payment Processing:**
   - **Backend Endpoint:** `POST /api/v1/payments`
   - Accepts: `loanId`, `amount`, `repaymentId` (optional), `receiptUrl` (optional)
   - **Current Flow:**
     - If `repaymentId` provided → Updates specific repayment's `amountPaid`
     - Updates loan's `totalPaid` field
     - **Issue:** No separate payment transaction log is created
     - **Issue:** Multiple partial payments are aggregated into one repayment record

3. **Payment History Display:**
   - **Backend Endpoint:** `GET /api/v1/payments`
   - Returns repayments where `amountPaid > 0`
   - Shows: repayment ID, loan reference, amount paid, due date, paid date
   - **Issue:** Shows repayments, not individual payment transactions
   - **Issue:** If user made 3 payments of ₱500 each, only shows total ₱1,500

4. **Receipt Upload:**
   - **Current Flow:** User uploads receipt → Only creates document record
   - **Issue:** Receipt upload doesn't create/update payment
   - **Issue:** No automatic connection between receipt and payment
   - Receipt can have `loanId` but not linked to specific payment/repayment

## Problems Identified

### 1. **No Payment Transaction Log**
   - Payments are just updates to `repayment.amountPaid`
   - Can't track individual payment transactions
   - Can't see payment history (when, how much, how many times)

### 2. **Receipt-Payment Disconnection**
   - Receipts are uploaded separately
   - No automatic payment creation when receipt is uploaded
   - Manual payment creation required via separate API call

### 3. **Payment History Limitations**
   - Shows repayments (payment schedules) not payments
   - If repayment has multiple payments, only final total shown
   - No way to see individual payment amounts and dates

### 4. **Missing Features**
   - No payment method tracking
   - No payment status (pending, confirmed, rejected)
   - No link between receipt document and payment record

## Recommended Implementation

### Option 1: Create Separate Payment Transactions Table (Recommended)

**New Database Schema:**
```prisma
model Payment {
  id            BigInt    @id @default(autoincrement())
  loanId        BigInt
  repaymentId   BigInt?   // Optional - if paying specific installment
  amount        Decimal
  paidAt        DateTime
  paymentMethod String?   // "bank_transfer", "cash", "receipt", etc.
  status        PaymentStatus @default(pending)
  receiptId     BigInt?   // Link to document
  notes         String?
  createdAt     DateTime
  updatedAt     DateTime
  
  loan          Loan      @relation(...)
  repayment     Repayment? @relation(...)
  receipt       Document? @relation(...)
}

enum PaymentStatus {
  pending
  confirmed
  rejected
}
```

**Flow:**
1. User uploads receipt → Creates Document + Creates Payment (pending)
2. Admin/System confirms payment → Updates Payment status + Updates Repayment
3. Payment History shows all Payment transactions

### Option 2: Enhance Current System (Simpler)

**Keep repayments as-is, but:**
1. When receipt uploaded with `loanId` → Auto-create payment record
2. Add payment creation endpoint that also updates repayment
3. Store payment transactions separately for history

**Changes Needed:**
1. Link receipt upload to payment creation
2. Create payment transaction when receipt uploaded
3. Update `getPayments` to return payment transactions, not just repayments

## Current Frontend Implementation

### Payment History Display (`loan_history.tsx`):
- ✅ Fetches payments from `/api/v1/payments`
- ✅ Displays payment amount, loan reference, paid date
- ⚠️ **Issue:** Field name mismatch - uses `payment.amountPaid` but backend returns `payment.amount`
- ⚠️ **Issue:** Uses `payment.loan?.reference` but backend returns `loanReference` (not nested)

### Receipt Upload (`attatch_receipt.tsx`):
- ✅ Uploads receipt document
- ❌ **Missing:** Doesn't create payment record
- ❌ **Missing:** Should link receipt to loan/payment

## Immediate Fixes Needed

### 1. Fix Frontend Payment Display
```typescript
// Current (incorrect):
payment.amountPaid
payment.loan?.reference

// Should be:
payment.amount
payment.loanReference
```

### 2. Enhance Receipt Upload Flow
```typescript
// After uploading receipt, optionally create payment:
if (loanId) {
  await api.post('/payments', {
    loanId,
    amount: parseFloat(amountInput), // User enters amount
    receiptUrl: document.fileUrl,
  });
}
```

### 3. Improve Payment History Query
- Return individual payment transactions
- Include receipt information
- Show payment status and method

## Suggested User Flow

### Current Flow (Broken):
1. User uploads receipt → ✅ Document created
2. User wants to record payment → ❌ No way to do this
3. Payment history → Shows repayments, not individual payments

### Improved Flow:
1. User uploads receipt → ✅ Document created
2. System prompts: "Record payment amount?" → ✅ User enters amount
3. System creates payment record linked to receipt → ✅ Payment created
4. Payment history → ✅ Shows individual payment transactions with receipt links

## Next Steps

1. **Fix immediate bugs:**
   - Fix field name mismatch in frontend
   - Add payment creation after receipt upload

2. **Enhance backend:**
   - Create payment transactions table (or enhance current system)
   - Link receipts to payments
   - Return proper payment history

3. **Improve UX:**
   - Add payment amount input when uploading receipt
   - Show payment status
   - Link receipts to payments in UI


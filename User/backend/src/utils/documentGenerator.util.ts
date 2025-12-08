/**
 * Document Generator Utility
 * Generates legal loan agreements and demand letters based on Philippine law templates
 */

interface BorrowerDetails {
  fullName: string;
  address: string;
  civilStatus: string;
  email?: string;
  phone?: string;
  birthday?: Date;
  sex?: string; // For title (Ms./Mr.) in demand letters
  barangay?: string; // For demand letter address formatting
  city?: string; // For demand letter address formatting
}

interface LenderDetails {
  name: string;
  address: string;
  email?: string;
  phone?: string;
  civilStatus?: string;
}

interface LoanAgreementData {
  borrower: BorrowerDetails;
  lender: LenderDetails;
  loanAmount: number;
  interestRate: number; // Monthly rate as decimal (e.g., 0.07 for 7%)
  monthlyPayment: number;
  tenor: number; // Number of months (used for payment schedule calculation)
  applicationDate: Date;
  city: string;
  penaltyRate?: number; // Late payment penalty as decimal (e.g., 0.10 for 10%)
  loanPurpose?: string; // Purpose of the loan
  paymentPlace?: string; // Where payments are made
  venueCity?: string; // Venue city for legal disputes
}

interface GuarantorDetails {
  fullName: string;
  address: string;
  civilStatus: string;
}

interface GuarantyAgreementData {
  guarantor: GuarantorDetails;
  creditor: LenderDetails; // Lender/Creditor
  principalDebtor: BorrowerDetails; // Borrower
  loanAmount: number;
  loanAgreementDate: Date;
  city: string;
  venueCity?: string; // Venue city for legal disputes
}

interface DemandLetterData {
  borrower: BorrowerDetails;
  lender: LenderDetails;
  loanReference: string; // Used in letter generation
  loanAmount: number;
  monthlyPayment: number;
  missedPayments: Array<{
    dueDate: Date;
    amount: number;
  }>;
  totalDue: number;
  demandDate: Date;
  daysToComply: number;
  loanAgreementDate: Date; // Date of the original loan agreement
  paymentDueDay?: number; // Day of month for payment (default: 1)
  penaltyRate?: number; // Penalty rate as decimal
  firstMissedPaymentDate?: Date; // Date of first missed payment
}

/**
 * Convert number to words (Philippine Peso format)
 */
function numberToWords(num: number): string {
  const ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE'];
  const teens = ['TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
  const tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
  const scales = ['', 'THOUSAND', 'MILLION', 'BILLION'];

  if (num === 0) return 'ZERO';

  function convertHundreds(n: number): string {
    let result = '';
    if (n >= 100) {
      result += ones[Math.floor(n / 100)] + ' HUNDRED ';
      n %= 100;
    }
    if (n >= 20) {
      result += tens[Math.floor(n / 10)] + ' ';
      n %= 10;
    }
    if (n >= 10) {
      result += teens[n - 10] + ' ';
      n = 0;
    }
    if (n > 0) {
      result += ones[n] + ' ';
    }
    return result.trim();
  }

  let words = '';
  let scaleIndex = 0;
  let remaining = Math.floor(num);

  while (remaining > 0) {
    const chunk = remaining % 1000;
    if (chunk > 0) {
      words = convertHundreds(chunk) + (scaleIndex > 0 ? scales[scaleIndex] + ' ' : '') + words;
    }
    remaining = Math.floor(remaining / 1000);
    scaleIndex++;
  }

  return words.trim();
}

/**
 * Format date in Philippine format (e.g., "30th day of July, 2025")
 */
function formatDate(date: Date): string {
  const day = date.getDate();
  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();

  // Add ordinal suffix
  const getOrdinal = (n: number) => {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
  };

  return `${getOrdinal(day)} day of ${month}, ${year}`;
}

/**
 * Format date in short format with period (e.g., "October. 30, 2025")
 */
function formatDateShort(date: Date): string {
  const day = date.getDate();
  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();

  return `${month}. ${day}, ${year}`;
}

/**
 * Format date as full date (e.g., "30th day of July, 2025")
 * Same as formatDate, but kept for template compatibility
 */
function formatDateFull(date: Date): string {
  return formatDate(date);
}

/**
 * Format date as month and year (e.g., "August 2025")
 */
function formatMonthYear(date: Date): string {
  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  return `${monthNames[date.getMonth()]} ${date.getFullYear()}`;
}

/**
 * Format currency in Philippine Peso format (e.g., "Php 100,000.00")
 * Kept for backward compatibility
 */
export function formatCurrency(amount: number): string {
  return `Php ${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

/**
 * Format currency numeric only (e.g., "100,000.00")
 */
function formatCurrencyNumeric(amount: number): string {
  return amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Get day of month with ordinal (e.g., "1st", "15th")
 */
function getDayWithOrdinal(day: number): string {
  const s = ['th', 'st', 'nd', 'rd'];
  const v = day % 100;
  return day + (s[(v - 20) % 10] || s[v] || s[0]);
}

/**
 * Generate Personal Loan Agreement document using new template
 */
export function generateLoanAgreement(data: LoanAgreementData): string {
  const {
    borrower,
    lender,
    loanAmount,
    interestRate,
    monthlyPayment,
    tenor: _tenor,
    applicationDate,
    city,
    penaltyRate = 0.10,
    loanPurpose = 'personal use',
    paymentPlace = 'the Lender\'s office',
    venueCity,
  } = data;

  // Format values
  const agreementCity = city;
  const agreementDateFull = formatDateFull(applicationDate);
  const lenderName = lender.name.toUpperCase();
  const lenderCivilStatus = (lender.civilStatus || 'single').toLowerCase();
  const lenderAddress = lender.address;
  const borrowerName = borrower.fullName.toUpperCase();
  const borrowerCivilStatus = (borrower.civilStatus || 'single').toLowerCase();
  const borrowerAddress = borrower.address;
  const loanAmountWords = numberToWords(loanAmount) + ' PESOS';
  const loanAmountNumeric = formatCurrencyNumeric(loanAmount);
  const interestRateMonthlyPercent = (interestRate * 100).toFixed(0);
  const monthlyAmortWords = numberToWords(monthlyPayment) + ' PESOS';
  const monthlyAmortNumeric = formatCurrencyNumeric(monthlyPayment);
  const penaltyInterestPercent = (penaltyRate * 100).toFixed(0);
  const executionDateFull = formatDateFull(applicationDate);
  const executionPlace = city;
  const venueCityFinal = venueCity || city;
  const ackCity = city;
  const ackYear = applicationDate.getFullYear().toString();
  const ackPlace = city;

  // Calculate first payment date (1st of next month)
  const firstPaymentDate = new Date(applicationDate);
  firstPaymentDate.setMonth(firstPaymentDate.getMonth() + 1);
  firstPaymentDate.setDate(1);
  const paymentDueDay = 1; // Default to 1st of the month
  const paymentDueDayOrdinal = getDayWithOrdinal(paymentDueDay);
  const paymentStartMonthYear = formatMonthYear(firstPaymentDate);

  const agreement = `Republic of the Philippines    }

City of ${agreementCity}     } S.S.

PERSONAL LOAN AGREEMENT

KNOW ALL MEN BY THESE PRESENTS:

This Loan Agreement ("Agreement") is made and entered into this ${agreementDateFull}, at ${agreementCity}, Philippines, by and between:

${lenderName}, of legal age, Filipino, ${lenderCivilStatus}, and residing at ${lenderAddress}, hereinafter referred to as the "Lender";

- and -

${borrowerName}, of legal age, Filipino, ${borrowerCivilStatus}, and residing at ${borrowerAddress}, hereinafter referred to as the "Borrower".

WITNESSETH:

WHEREAS, the Lender agrees to loan to the Borrower, and the Borrower agrees to borrow from the Lender, the amount of ${loanAmountWords} (Php ${loanAmountNumeric}), Philippine currency, under the terms and conditions set forth below;

NOW, THEREFORE, for and in consideration of the foregoing premises, the parties agree as follows:

1. Loan Amount.

The Lender agrees to loan and advance to the Borrower, and the Borrower acknowledges receipt of, the amount of ${loanAmountWords} (Php ${loanAmountNumeric}), Philippine currency (the "Loan").

2. Purpose of the Loan.

The Loan shall be used by the Borrower exclusively for ${loanPurpose}.

3. Interest.

The Loan shall earn interest at the rate of ${interestRateMonthlyPercent}% per month, computed on the outstanding principal balance, until the Loan is fully paid.

4. Payment Terms.

4.1. The Borrower shall pay the Loan in monthly installments of ${monthlyAmortWords} (Php ${monthlyAmortNumeric}), starting on the ${paymentDueDayOrdinal} day of ${paymentStartMonthYear} and every ${paymentDueDayOrdinal} day of each succeeding month thereafter, until the Loan and all accrued interests are fully paid.

4.2. Payments shall first be applied to interest, penalties, and other charges, and the remainder to the principal.

5. Manner of Payment.

All payments shall be made in cash or cleared funds to the Lender at ${paymentPlace} or such other place as the Lender may designate in writing.

6. Prepayment.

The Borrower may, at any time, prepay the Loan in whole or in part without penalty, provided that any partial prepayment shall not postpone or reduce the amount of the next monthly installment unless agreed upon in writing by the Lender.

7. Late Payment and Penalty.

7.1. Any installment not paid on its due date shall be considered in default.

7.2. In case of delay or default in payment, the unpaid amount shall be subject to penalty interest at the rate of ${penaltyInterestPercent}% per month, in addition to the regular interest, computed from the date of default until fully paid.

8. Events of Default.

The following shall constitute events of default:

(a) Failure of the Borrower to pay any installment or any amount due under this Agreement on its due date;

(b) Breach by the Borrower of any term or condition of this Agreement;

(c) Insolvency, bankruptcy, or inability of the Borrower to pay debts as they fall due.

9. Consequences of Default.

Upon the occurrence of any event of default, the Lender may, at its option and without need of notice or demand:

(a) Declare the entire outstanding principal, accrued interest, penalties, and other charges immediately due and payable;

(b) Initiate legal action to collect the amount due, at the cost and expense of the Borrower.

10. Waiver.

Failure of the Lender to exercise any right or remedy under this Agreement shall not constitute a waiver of such right or remedy, nor shall it prevent the subsequent exercise thereof.

11. Assignment.

The Borrower shall not assign or transfer its rights and obligations under this Agreement without the prior written consent of the Lender. The Lender may assign its rights under this Agreement upon written notice to the Borrower.

12. Governing Law and Venue.

This Agreement shall be governed by and construed in accordance with the laws of the Republic of the Philippines. In case of any dispute arising from or in connection with this Agreement, the parties agree to submit to the exclusive jurisdiction of the proper courts of ${venueCityFinal}, to the exclusion of all other venues.

13. Cost and Fees.

Upon the occurrence of a default by the Borrower, the Borrower agrees to pay all costs and expenses incurred by the Lender in enforcing this Agreement, including, but not limited to, attorney's fees, filing fees, and additional damages, as allowed by Philippine law.

14. Separability Clause.

Should any provision of this Agreement be held invalid or unenforceable, the other provisions shall remain in full force and effect.

15. Miscellaneous.

Any amendment to this Agreement must be in writing and signed by both parties.

IN WITNESS WHEREOF, the parties have executed this Agreement this ${executionDateFull} in ${executionPlace}, Philippines.

___________________________                    ___________________________

${borrowerName}                               ${lenderName}

Borrower                                        Lender

ACKNOWLEDGMENT

Republic of the Philippines    )

City/Province of ${ackCity}  ) S.S.

BEFORE ME, this ________________ day of ______________, ${ackYear}, at ${ackPlace}, Philippines, personally appeared:

${borrowerName} and ${lenderName},

who are identified by me through competent evidence of identity, known to me to be the same persons who executed the foregoing Personal Loan Agreement and acknowledged to me that the same is their free and voluntary act and deed.

IN WITNESS WHEREOF, I have hereunto set my hand and affixed my notarial seal on the date and place first above written.

Notary Public

Name: ___________________________

PTR No.: ________________________

IBP No.: ________________________

Commission No.: _________________

Until: __________________________`;

  return agreement;
}

/**
 * Generate Final Demand Letter using new template
 */
export function generateDemandLetter(data: DemandLetterData): string {
  const {
    borrower,
    lender,
    loanReference: _loanReference,
    loanAmount,
    monthlyPayment,
    missedPayments: _missedPayments, // Not used in new template but kept for interface compatibility
    totalDue,
    demandDate: _demandDate, // Not used in new template but kept for interface compatibility
    daysToComply = 5,
    loanAgreementDate,
    paymentDueDay = 1,
    penaltyRate = 0.10,
    firstMissedPaymentDate: _firstMissedPaymentDate, // Not used in new template but kept for interface compatibility
  } = data;

  // Format values
  const creditorName = lender.name.toUpperCase();
  const creditorEmail = lender.email || 'N/A';
  const creditorPhone = lender.phone || 'N/A';
  const debtorName = borrower.fullName.toUpperCase();
  const debtorBarangay = borrower.barangay || '';
  const debtorCity = borrower.city || '';
  const loanAgreementDateFormatted = formatDateShort(loanAgreementDate);
  const loanAmountWords = numberToWords(loanAmount) + ' PESOS';
  const loanAmountNumeric = formatCurrencyNumeric(loanAmount);
  const monthlyAmortWords = numberToWords(monthlyPayment) + ' PESOS';
  const monthlyAmortNumeric = formatCurrencyNumeric(monthlyPayment);
  const paymentDueDayOrdinal = getDayWithOrdinal(paymentDueDay);
  
  // Calculate first payment date
  const firstPaymentDate = new Date(loanAgreementDate);
  firstPaymentDate.setMonth(firstPaymentDate.getMonth() + 1);
  firstPaymentDate.setDate(paymentDueDay);
  const monthlyAmortPeriodStart = formatDateShort(firstPaymentDate);
  
  const totalAmountDueWords = numberToWords(totalDue) + ' PESOS';
  const totalAmountDueNumeric = formatCurrencyNumeric(totalDue);
  const penaltyInterestPercent = (penaltyRate * 100).toFixed(0);
  const paymentDeadlineDays = daysToComply.toString();

  // Start with borrower information (name, barangay, city)
  const letter = `${debtorName}

${debtorBarangay}

${debtorCity}



Subject: FINAL DEMAND LETTER FOR PAYMENT



Dear ${debtorName},



This letter serves as your FINAL DEMAND to settle your outstanding obligations arising from the Personal Loan Agreement executed on ${loanAgreementDateFormatted} between you ("Borrower") and ${creditorName} ("Lender").



Under the Agreement, you obtained a loan amounting to ${loanAmountWords} (Php ${loanAmountNumeric}), payable in monthly amortizations of ${monthlyAmortWords} (Php ${monthlyAmortNumeric}), beginning on ${monthlyAmortPeriodStart} and every ${paymentDueDayOrdinal} of the month thereafter.



Despite repeated reminders, you have failed to pay your due obligations. As of today, the total amount due and demandable is:



TOTAL AMOUNT DUE: ${totalAmountDueWords} (Php ${totalAmountDueNumeric})



This amount includes:

• Unpaid principal  

• Accrued monthly interest  

• Penalty charges at ${penaltyInterestPercent}% per month  

• Any applicable fees allowed under the Agreement and Philippine law  



You are hereby given a NON-EXTENDIBLE period of **${paymentDeadlineDays} calendar days** from receipt of this letter to fully settle the above amount. Failure to comply within the prescribed period shall compel us to pursue ANY AND ALL legal remedies available under the law, including but not limited to:



1. Filing a civil case for sum of money, damages, and attorney's fees;  

2. Referral of your account to legal collections;  

3. Enforcement of guaranty (if applicable);  

4. Reporting of default behavior to concerned records/credit agencies.



Should legal action become necessary, you shall also be liable for all expenses incurred in enforcing the Agreement, including attorney's fees (not less than 25% of the total amount due), court filing fees, and additional damages.



To avoid further inconvenience, you may settle directly through:



Name of Creditor: ${creditorName}  

Email: ${creditorEmail}  

Phone: ${creditorPhone}  



After payment, kindly send proof of payment to the email indicated above.



This is your FINAL DEMAND. No further notice will be issued.



Sincerely,



${creditorName}

Creditor / Lender`;

  return letter;
}

/**
 * Generate Guaranty Agreement document using new template
 */
export function generateGuarantyAgreement(data: GuarantyAgreementData): string {
  const {
    guarantor,
    creditor,
    principalDebtor,
    loanAmount,
    loanAgreementDate,
    city,
    venueCity,
  } = data;

  // Format values
  const guarantyAgreementCity = city;
  const guarantyAgreementDate = formatDate(loanAgreementDate);
  const guarantorName = guarantor.fullName.toUpperCase();
  const guarantorCivilStatus = (guarantor.civilStatus || 'single').toLowerCase();
  const guarantorAddress = guarantor.address;
  const creditorName = creditor.name.toUpperCase();
  const creditorCivilStatus = (creditor.civilStatus || 'single').toLowerCase();
  const creditorAddress = creditor.address;
  const principalDebtorName = principalDebtor.fullName.toUpperCase();
  const loanAgreementDateFormatted = formatDate(loanAgreementDate);
  const loanAmountWords = numberToWords(loanAmount) + ' PESOS';
  const loanAmountNumeric = formatCurrencyNumeric(loanAmount);
  const venueCityFinal = venueCity || city;
  const ackCity = city;

  const agreement = `Republic of the Philippines   )

City of ${guarantyAgreementCity}   ) S.S.

GUARANTY AGREEMENT

KNOW ALL MEN BY THESE PRESENTS:

This Guaranty Agreement ("Agreement") is executed on this ${guarantyAgreementDate}, in ${guarantyAgreementCity}, Philippines, by and between:



${guarantorName}, of legal age, Filipino, ${guarantorCivilStatus}, and residing at ${guarantorAddress}, hereinafter referred to as the "Guarantor";



- and -



${creditorName}, of legal age, Filipino, ${creditorCivilStatus}, and residing at ${creditorAddress}, hereinafter referred to as the "Creditor".

WHEREAS, ${principalDebtorName} ("Principal Debtor") obtained a loan from the Creditor under a Personal Loan Agreement dated ${loanAgreementDateFormatted}, amounting to ${loanAmountWords} (Php ${loanAmountNumeric});

WHEREAS, the Guarantor freely and voluntarily agrees to guarantee the faithful performance and full payment of the Principal Debtor's obligations;

NOW, THEREFORE, the parties agree as follows:

1. **Guaranty**  

The Guarantor irrevocably and unconditionally guarantees the full, prompt, and complete payment of all obligations of the Principal Debtor under the Personal Loan Agreement referenced above, including principal, interest, penalties, damages, attorney's fees, and any lawful charges.

2. **Nature of Guaranty**  

This guaranty is joint and several ("solidary"), and the Creditor may directly proceed against the Guarantor without first exhausting remedies against the Principal Debtor, pursuant to Article 2059 of the Civil Code of the Philippines.

3. **Continuing Guaranty**  

This guaranty shall remain valid until all obligations of the Principal Debtor are fully paid and settled.

4. **Default**  

Upon the Principal Debtor's failure to pay the amount due, the Guarantor shall be immediately liable, without need of notice or demand.

5. **Costs and Attorney's Fees**  

Should enforcement be necessary, the Guarantor agrees to pay attorney's fees not less than 25% of the total amount due, plus litigation expenses, penalty interest, and damages.

6. **Governing Law and Venue**  

This Agreement shall be governed by Philippine law. Any action arising from this Agreement shall be filed exclusively in the proper courts of ${venueCityFinal}.

IN WITNESS WHEREOF, the parties have signed this Agreement on the date and at the place first above written.





_______________________________      _______________________________

${guarantorName}                   ${creditorName}

Guarantor                            Creditor





ACKNOWLEDGMENT



Republic of the Philippines     )

City/Province of ${ackCity}   ) S.S.



BEFORE ME, personally appeared:



${guarantorName} and ${creditorName}



known to me and identified through competent evidence of identity, who voluntarily signed the foregoing Guaranty Agreement and acknowledged that the same is their free and voluntary act.



IN WITNESS WHEREOF, I have hereunto affixed my signature and notarial seal.



Notary Public

Name: ________________________

PTR No.: ______________________

IBP No.: _______________________

Commission No.: _______________

Until: _________________________`;

  return agreement;
}

/**
 * Get default lender information (can be configured via environment variables)
 */
export function getDefaultLender(): LenderDetails {
  return {
    name: process.env.LENDER_NAME || 'MasterFunds',
    address: process.env.LENDER_ADDRESS || 'Manila City, Philippines',
    email: process.env.LENDER_EMAIL || 'info@masterfunds.com',
    phone: process.env.LENDER_PHONE || '0998-765-4321',
    civilStatus: process.env.LENDER_CIVIL_STATUS || 'single',
  };
}

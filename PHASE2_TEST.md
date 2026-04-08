# Phase 2 Test Plan — PDF Printing (TCPDF)

## 1. Invoice PDF (TCPDF)
**Goal**: Verify existing invoice PDF still works and looks professional.

### Steps:
1. Go to **Accounting > Invoices**.
2. Identify a paid or unpaid invoice.
3. Click the **Print** icon (blue icon) in the Actions column.
4. Verify a PDF is downloaded. Open it.
5. Confirm:
   - Organization logo and details are correct.
   - Brand color (primary color) is applied to headers.
   - All items are listed with correct quantities and prices.
   - Payment history is shown at the bottom.

---

## 2. Receipt PDF (TCPDF)
**Goal**: Verify new official receipt generation for payments.

### Steps:
1. Go to **Accounting > Payments Received**.
2. Find a payment record and click the **Print** icon (info/cyan icon) in the Actions column.
3. Verify a PDF named `Receipt_RCT-XXXX.pdf` is downloaded.
4. Confirm:
   - Title says "OFFICIAL RECEIPT".
   - Tenant name and unit details are correct.
   - Total amount is prominent in a styled box.
   - Signature lines for "Received By" and "Tenant" are present.

---

## 3. Expense PDF (TCPDF)
**Goal**: Verify new expense voucher generation.

### Steps:
1. Go to **Accounting > Expenses List**.
2. Find an expense and click the **Print** icon (info/cyan icon) in the Actions column.
3. Verify a PDF named `Expense_EXP-XXXX.pdf` is downloaded.
4. Confirm:
   - Title says "EXPENSE VOUCHER".
   - Category and amount match the record.
   - "Prepared By" and "Approved By" lines are present.
   - Property name is shown if it was a property-specific expense.

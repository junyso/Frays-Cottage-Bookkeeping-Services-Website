#!/bin/bash
# Cleaning Guru (CG) - Phase 2 Migration
# Continues from Phase 1

SOURCE_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU"
TARGET_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

echo "=== Cleaning Guru Phase 2 Migration ==="
echo ""

# ====== MANAGEMENT ACCOUNTS (01-AC) ======
echo "01-AC - Management Accounts:"
for f in "Attachements to Managment Accounts_Feb 2016.xlsx" \
         "Cleaning-Guru_Financial Statement_2015.xlsx" \
         "KHUMO PROPERTIES FINANCIAL.xlsx" \
         "Cost Centre-Project Analysis-Jan_Feb-2016.xlsx" \
         "Connie BSB Tender Input.xlsx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Management Accounts/" && echo "  ✓ $f"
done

# ====== BANK STATEMENTS (01-AC) ======
echo ""
echo "01-AC - Bank Statements:"
for f in "B642AXC.pdf" "BQA.pdf" "BUSINESS CHEQUE ACCOUNT 25.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Bank Statements/" && echo "  ✓ $f"
done

# ====== INVOICES & RECEIPTS (01-AC) ======
echo ""
echo "01-AC - Invoices & Receipts:"
for f in "CustomerInvoicesReport.xls" \
         "Invoices CG502 For 2016.xlsx" \
         "Invoices from CG360 September (Autosaved).xls" \
         "Invoices-ORANGE_October & November.pdf" \
         "CustomerTransactionsReport-Cleaning Guru.pdf" \
         "CustomerTransactionsReport-FOR RECON cg.pdf" \
         "CustomerBalances_DaysOutstandingReport(1).xls" \
         "CustomerListingReport.txt" \
         "Debtors List for FNB.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Invoices & Receipts/(Issued)/" && echo "  ✓ $f"
done

# ====== INDEX & POLICIES (08-GN) ======
echo ""
echo "08-GN - General Files:"
for f in "Cleaning GURU-Files Index Version 2.pdf" \
         "Cleaning GURU-Files Index.pdf" \
         "Cleaning GURU-Files Index.xlsx" \
         "Index to Policies.docx" \
         "Doc3.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/08-GN/Forms/" && echo "  ✓ $f"
done

# ====== LETTERS (08-GN Correspondence) ======
echo ""
echo "08-GN - Correspondence:"
for f in "Letter to BURS.docx" "Letter to BURS2.docx" \
         "Letter to BURS-May 2018.docx" \
         "Letter to BURS for Manual Allocation of Tax Payments-April 2025.docx" \
         "Letter to BURS for Manual Allocation of Tax Payments-April 2025.pdf" \
         "Letter to BURS_Appliation for TAX Clearance Certificate_June 16th 2020.docx" \
         "Letter to BURS_Appliation for TAX Clearance Certificate_May 7th 2021.docx" \
         "Letter to BURS-May 2018-stats.xlsx" \
         "Letter to FNB-Loan Application Cover Letter-July 2018.docx" \
         "Letter to MITI-Sebele-Tender Clarification.docx" \
         "Letter to Moshupa Tender Clarification-May 2024.docx" \
         "Letter to Moshupa Tender Clarification-May 2024.pdf" \
         "Letter-Landlord Refuse Charges to Tenants.docx" \
         "Letter-Landlord Reimbursement to Improvement to Property.docx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/08-GN/Correspondence/(OUTGOING)/" && echo "  ✓ $f"
done

# ====== JOB DESCRIPTIONS (07-HR) ======
echo ""
echo "07-HR - Human Resources:"
for f in "Job Description-Accounts Department.docx" \
         "JANUARY 2016 SALARIES.xlsx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/07-HR/Employee Records/" && echo "  ✓ $f"
done

echo ""
echo "=== Phase 2 Complete ==="

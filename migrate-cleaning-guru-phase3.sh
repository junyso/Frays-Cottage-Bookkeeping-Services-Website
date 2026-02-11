#!/bin/bash
# Cleaning Guru (CG) - Phase 3 Migration
# Final phase

SOURCE_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU"
TARGET_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

echo "=== Cleaning Guru Phase 3 Migration ==="
echo ""

# ====== MANAGEMENT ACCOUNTS (01-AC) ======
echo "01-AC - Management Accounts:"
for f in "Management Accounts-Jan_Feb 2016.docx" \
         "pdf version Management Account-Jan-Feb2016.pdf" \
         "P & L Jan_Feb 2016.xlsx" \
         "Purchases summary 2016.xlsx" \
         "SALES SUMMARY.xlsx2016.xlsx" \
         "Maikano Balance.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Management Accounts/" && echo "  ✓ $f"
done

# ====== INVOICES & RECEIPTS (01-AC) ======
echo ""
echo "01-AC - Invoices & Receipts:"
for f in "Quotation - QUO0000001.pdf" \
         "Quotation - QUO0000026.pdf" \
         "Quotation - QUO0000097.pdf" \
         "SupplierTransactionsReport-Cleaning Guru.pdf" \
         "suppier details.xlsx" \
         "bank statement for import-1st.csv" \
         "Trial CSV.csv"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Invoices & Receipts/(Received)/" && echo "  ✓ $f"
done

# ====== PAYROLL (07-HR) ======
echo ""
echo "07-HR - Payroll:"
for f in "Maikano Payslip.pdf" \
         "JANUARY 2016 SALARIES.xlsx" \
         "SEPTEMBER SALARIES.xlsx" \
         "PAYROLL_JULY_SHIRLEY_2017-CG.xlsx" \
         "Monthly Timetable_CG.xlsx" \
         "Target-Commission Setting-Maikano March 2016.xlsx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/07-HR/Payroll Records/" && echo "  ✓ $f"
done

# ====== EMPLOYEE RECORDS (07-HR) ======
echo ""
echo "07-HR - Employee Records:"
for f in "Offer Letter-Ame.docx" \
         "Offer Letter-Shareholding.docx" \
         "The Duties and Responsibilities of Accounts Admin Officer.pdf" \
         "Welcome _ Botswana Unified Revenue Service.htm"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/07-HR/Employee Records/" && echo "  ✓ $f"
done

# ====== MARKETING (06-MS) ======
echo ""
echo "06-MS - Marketing & Branding:"
for f in "MARKETING PLAN.docx" \
         "Presentation-Cleaning Guru.pptx" \
         "company logo.doc" \
         "Pricing COVID-19 Stock.xlsx" \
         "Work Plan Cleaning Guru.docx" \
         "Risk Management Plan-Cleaning Guru.xls"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/06-MS/Company Profile & Other Marketing Material/" && echo "  ✓ $f"
done

# ====== CONTRACTS (04-CN) ======
echo ""
echo "04-CN - Contracts:"
for f in "Service Agreement-Cleaning Guru.pdf" \
         "Service Agreement-Revised.pdf" \
         "SHE BIN PROPOSAL-Letter-BUAN-August 2022.docx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/04-CN/General Contracts/" && echo "  ✓ $f"
done

# ====== PROJECTS (06-MS) ======
echo ""
echo "06-MS - Projects:"
for f in "Work_Plan_Template_Excel_2007-2013_0.xlsx" \
         "New Microsoft Office Word  Document.docx" \
         "invoice clarification  ORANGE-revised.docx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/06-MS/Projects/" && echo "  ✓ $f"
done

# ====== BANKING (01-AC) ======
echo ""
echo "01-AC - Bank Statements:"
for f in "Overdraft facility application-FNBB-cg.docx" \
         "Receipts-FIN Investments.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Bank Statements/" && echo "  ✓ $f"
done

echo ""
echo "=== Phase 3 Complete ==="

#!/bin/bash
# Cleaning Guru (CG) - File Migration Script
# Date: 2026-02-08

SOURCE_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU"
TARGET_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"
COMPANY_CODE="CG"

echo "=== Cleaning Guru (CG) Migration ==="
echo ""

# ====== ACCOUNTING (01-AC) ======
echo "01-AC - Accounting Files:"

# Chart of Accounts
for f in "Chart Of Accounts-Cleaning Guru.pdf" "Chart Of Accounts-Cleaning Guru.xlsx" \
         "Chart of Accounts Cleaning Guru-Front Accounting-Oct2022.pdf" \
         "Chart of Accounts Cleaning Guru-Last One!.docx" "Chart of Accounts Cleaning Guru-Last One!.pdf" \
         "ChartOfAccountsAccountListingReport.csv" "ChartOfAccountsAccountListingReport.xlsx" \
         "Chart of Accounts Work in Progress.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Management Accounts/" && echo "  ✓ $f"
done

# Financial Statements
for f in "Balance Sheet Jan_Feb 2016.xlsx" "2016 COOPERATIVES_FINANCIALS.xlsx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Management Accounts/" && echo "  ✓ $f"
done

# Aged Analysis
for f in "Aged Customer Analysis-Jan_Feb-2016.xlsx" "Aged Supplier Analysis-Jan_Feb-2016.xlsx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Management Accounts/" && echo "  ✓ $f"
done

# Opening Balances
for f in "Cleaning GURU Special Opening Balances Project.xlsx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Management Accounts/" && echo "  ✓ $f"
done

# Invoices Master
for f in "Cleaning Guru_Invoices Master.xlsx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/01-AC/Invoices & Receipts/(Issued)/" && echo "  ✓ $f"
done

echo ""

# ====== BOARD OF DIRECTORS (02-BD) ======
echo "02-BD - Board of Directors:"
for f in "Board Resolution No008 of 2016-New Shares.docx" "Board Resolution No008 of 2016-New Shares.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/02-BD/Company Resolutions/" && echo "  ✓ $f"
done

echo ""

# ====== MARKETING & BRANDING (06-MS) ======
echo "06-MS - Marketing & Branding:"

# Logos
for f in "Cleaning Guru Interim Logo.jpg" "Cleaning Guru Interim Logo.png"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/06-MS/Artworks & Logos/" && echo "  ✓ $f"
done

# Presentations/Profiles
for f in "Cleaning Guru-Presentation Appendix 1.pdf" "Cleaning Guru_Business Valuation.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/06-MS/Company Profile & Other Marketing Material/" && echo "  ✓ $f"
done

# Price Lists
for f in "Cleaning Guru_Price Increase Notification Letter-Orange Botswana.docx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/06-MS/Price Lists/" && echo "  ✓ $f"
done

echo ""

# ====== CONTRACTS (04-CN) ======
echo "04-CN - Contracts:"
for f in "Addendum-Service Level Agreement Cleaning Guru.docx" \
         "Addendum-Service Level Agreement Cleaning Guru.docx.pdf" \
         "Cleaning Guru_Generic & Standard Frays Cottage Bookkepping Service Contract.docx"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/04-CN/General Contracts/" && echo "  ✓ $f"
done

echo ""

# ====== GENERAL FILES (08-GN) ======
echo "08-GN - General Files:"
for f in "Accounts Policies, Systems & Procedures.docx" \
         "Cleaning GURU-Procedure Manual.docx" "Cleaning GURU-Procedure Manual.pdf" \
         "Cleaning GURU-Forms Designer.pdf" "Cleaning GURU-Forms Designer.xlsx" \
         "Cleaning GURU-Forms Designer2.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/08-GN/Policies/(Company)/" && echo "  ✓ $f"
done

echo ""

# ====== ARTWORKS & REQUISITION BOOKS (06-MS) ======
echo "06-MS - Artworks:"
for f in "Cleaning GURU- Requisition Book Artwork.docx" "Cleaning GURU- Requisition Book Artwork.pdf"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/06-MS/Artworks & Logos/" && echo "  ✓ $f"
done

echo ""

# ====== COMPANY REGISTRATION (05-CO) ======
echo "05-CO - Company Registration:"
for f in "62485624318 CLEANING GURU.pdf" "62485624318.csv"; do
    [[ -f "$SOURCE_DIR/$f" ]] && mv "$SOURCE_DIR/$f" "$TARGET_DIR/05-CO/Company Registration Documents/" && echo "  ✓ $f"
done

echo ""
echo "=== Batch Migration Complete ==="

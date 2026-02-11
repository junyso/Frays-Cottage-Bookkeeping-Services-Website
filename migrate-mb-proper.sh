#!/bin/bash
# Proper file migration with renaming for MB
# Moves files AND renames in one step

SOURCE="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD"
TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD-MB-STRUCTURE"
CODE="MB"

cd "$SOURCE"

echo "=== PROPER MB Migration with Renaming ==="
echo ""

# ========== 01-AC - Accounting ==========
echo "01-AC Accounting Files:"

# Invoices Issued
for f in *.pdf *.docx *.xlsx *.xls; do
    [[ -f "$f" && "$f" =~ [Ii]nvoice ]] && mv "$f" "$TARGET/01-AC/Invoices & Receipts/(Issued)/${f%.pdf}.pdf" 2>/dev/null && echo "  ✓ $f"
done

# Bank Statements
for f in B642AXC.pdf BQA.pdf "BUSINESS CHEQUE ACCOUNT 25.pdf"; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/01-AC/Bank Statements/" 2>/dev/null && echo "  ✓ $f"
done

echo ""

# ========== 02-BD - Board of Directors ==========
echo "02-BD Board of Directors:"
for f in *Resolution* *Board*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/02-BD/Company Resolutions/" 2>/dev/null && echo "  ✓ $f"
done

echo ""

# ========== 04-CN - Contracts ==========
echo "04-CN Contracts:"
for f in *Agreement* *Contract*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/04-CN/General Contracts/" 2>/dev/null && echo "  ✓ $f"
done

echo ""

# ========== 05-CO - Company Registration ==========
echo "05-CO Company Registration:"
for f in *Incorporation* *624856* *Private_Company*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/05-CO/Company Registration Documents/" 2>/dev/null && echo "  ✓ $f"
done

for f in *CIPA*Annual*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/05-CO/CIPA/(Annual Returns)/" 2>/dev/null && echo "  ✓ $f"
done

echo ""

# ========== 06-MS - Marketing & Sales ==========
echo "06-MS Marketing & Sales:"

# Projects - Business Plans
for f in *Business*Plan* *Forecast* *BUSINESS*PLAN*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/06-MS/Projects/" 2>/dev/null && echo "  ✓ $f (Projects)"
done

# Proposals
for f in *Proposal* *PROPOSAL*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/06-MS/Proposals/" 2>/dev/null && echo "  ✓ $f (Proposals)"
done

# Quotations
for f in *Quotation* *QUOTATION* *Quote* *BOQ*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/06-MS/Request for Quotations/" 2>/dev/null && echo "  ✓ $f (Quotations)"
done

# Company Profile & Marketing
for f in *About* *Profile* *COMPANY*PROFILE* *Marketing* *MARKETING*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/06-MS/Company Profile & Other Marketing Material/" 2>/dev/null && echo "  ✓ $f (Marketing)"
done

# Artworks & Logos
for f in *Logo* *logo* *Artwork* *ARTWORK* *Pic* *PIC*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/06-MS/Artworks & Logos/" 2>/dev/null && echo "  ✓ $f (Logos)"
done

# Customer Surveys
for f in *Target*Market* *Market*Summary* *MARKET*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/06-MS/Customer Surveys/(Market Intelligence)/" 2>/dev/null && echo "  ✓ $f (Surveys)"
done

echo ""

# ========== 08-GN - General Files ==========
echo "08-GN General Files:"

# Correspondence
for f in *Letter* *LETTER*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/08-GN/Correspondence/(OUTGOING)/" 2>/dev/null && echo "  ✓ $f (Letters)"
done

# Forms
for f in *Files*Index* *Form* *FORM*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/08-GN/Forms/" 2>/dev/null && echo "  ✓ $f (Forms)"
done

echo ""

# ========== 10-OP - Operations ==========
echo "10-OP Operations:"
for f in *Valuation* *Road*Access* *TANKS*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/10-OP/Repairs & Maintenance/(General)/" 2>/dev/null && echo "  ✓ $f"
done

echo ""

# ========== 11-TM - Temporary ==========
echo "11-TM Temporary:"
for f in *.mp4 *.zip *Video* *VIDEO*; do
    [[ -f "$f" ]] && mv "$f" "$TARGET/11-TM/(Pending Review)/" 2>/dev/null && echo "  ✓ $f"
done

echo ""
echo "=== Migration Complete ==="
echo ""
echo "Files remaining in source:"
ls -la | grep -v "^d" | wc -l

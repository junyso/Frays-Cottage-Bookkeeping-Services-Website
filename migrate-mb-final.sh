#!/bin/bash
# FINAL PROPER migration with correct naming format: YYYY-MM_MB_Description.ext
# THIS SCRIPT DOES BOTH: moves files AND renames them

SOURCE="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD"
TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD-MB-STRUCTURE"
CODE="MB"

# Ensure folders exist
mkdir -p "$TARGET/01-AC/Invoices & Receipts/(Issued)"
mkdir -p "$TARGET/01-AC/Bank Statements"
mkdir -p "$TARGET/02-BD/Company Resolutions"
mkdir -p "$TARGET/04-CN/General Contracts"
mkdir -p "$TARGET/05-CO/Company Registration Documents"
mkdir -p "$TARGET/05-CO/CIPA/(Annual Returns)"
mkdir -p "$TARGET/06-MS/Projects"
mkdir -p "$TARGET/06-MS/Proposals"
mkdir -p "$TARGET/06-MS/Request for Quotations"
mkdir -p "$TARGET/06-MS/Company Profile & Other Marketing Material"
mkdir -p "$TARGET/06-MS/Artworks & Logos"
mkdir -p "$TARGET/06-MS/Customer Surveys/(Market Intelligence)"
mkdir -p "$TARGET/08-GN/Correspondence/(OUTGOING)"
mkdir -p "$TARGET/08-GN/Forms"
mkdir -p "$TARGET/10-OP/Repairs & Maintenance/(General)"
mkdir -p "$TARGET/11-TM/(Pending Review)"

echo "=== FINAL MB MIGRATION with PROPER RENAMING ==="
echo "Format: YYYY-MM_MB_Description.ext"
echo ""

# Function to extract date and create new name
migrate_file() {
    local filepath="$1"
    local dest_folder="$2"
    local type="$3"
    
    filename=$(basename "$filepath")
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Extract date from filename
    if [[ $filename =~ ([0-9]{4})[/-]([0-9]{2})[/-]([0-9]{2}) ]]; then
        date="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    elif [[ $filename =~ ([0-9]{2})[/]([0-9]{2})[/]([0-9]{4}) ]]; then
        date="${BASH_REMATCH[3]}-${BASH_REMATCH[1]}"
    elif [[ $filename =~ (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[/-]([0-9]{4}) ]]; then
        month="${BASH_REMATCH:0:3}"
        year="${BASH_REMATCH##*-}"
        mnum=$(date -j -f "%b" "$month" "+%m" 2>/dev/null)
        date="$year-$mnum"
    elif [[ $filename =~ ([0-9]{4})(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) ]]; then
        year="${BASH_REMATCH:0:4}"
        month=$(echo "${BASH_REMATCH:4:3}" | tr '[:upper:]' '[:lower:]')
        mnum=$(date -j -f "%b" "$month" "+%m" 2>/dev/null)
        date="$year-$mnum"
    else
        # Use file modification date
        date=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
        [[ -z "$date" ]] && date="2025-01"
    fi
    
    # Create description from filename
    desc=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv)$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr '_' '-' | sed 's/[^a-z0-9-]//g' | sed 's/-+/-/g' | sed 's/^-//' | sed 's/-$//')
    
    # Shorten if too long
    if [[ ${#desc} -gt 40 ]]; then
        desc="${desc:0:40}"
    fi
    
    newname="${date}_${CODE}_${desc}.${ext}"
    
    # Handle duplicates
    if [[ -f "$dest_folder/$newname" ]]; then
        newname="${date}_${CODE}_${desc}_$(date +%s).${ext}"
    fi
    
    # Move and rename
    mv "$filepath" "$dest_folder/$newname"
    echo "  ✓ $newname"
}

cd "$SOURCE"

# ====== Accounting ======
echo "01-AC Accounting:"
for f in *Invoice*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/01-AC/Invoices & Receipts/(Issued)" "AC"
done

for f in B642AXC.pdf BQA.pdf "BUSINESS CHEQUE ACCOUNT 25.pdf"; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/01-AC/Bank Statements" "AC"
done

# ====== Board ======
echo ""
echo "02-BD Board:"
for f in *Resolution* *Board*Resolution*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/02-BD/Company Resolutions" "BD"
done

# ====== Contracts ======
echo ""
echo "04-CN Contracts:"
for f in *Agreement* *Contract*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/04-CN/General Contracts" "CN"
done

# ====== Company Registration ======
echo ""
echo "05-CO Company Registration:"
for f in *Incorporation* *624856* *Private_Company* *Consent*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/05-CO/Company Registration Documents" "CO"
done

for f in *CIPA*Annual*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/05-CO/CIPA/(Annual Returns)" "CO"
done

# ====== Marketing & Sales ======
echo ""
echo "06-MS Marketing & Sales:"
for f in *Business*Plan* *Forecast* *BUSINESS*PLAN* *Filling*Station*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/06-MS/Projects" "MS"
done

for f in *Proposal*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/06-MS/Proposals" "MS"
done

for f in *Quotation* *QUOTATION* *Quote* *BOQ* *Fuelco* *ESTIMATE*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/06-MS/Request for Quotations" "MS"
done

for f in *About* *Profile* *COMPANY*PROFILE* *Marketing* *MARKETING* *Analysis* *Traffic*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/06-MS/Company Profile & Other Marketing Material" "MS"
done

for f in *Logo* *logo* *Artwork* *ARTWORK* *Pic* *PIC* *PETROHYPER*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/06-MS/Artworks & Logos" "MS"
done

for f in *Target*Market* *Market*Summary* *MARKET*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/06-MS/Customer Surveys/(Market Intelligence)" "MS"
done

# ====== General ======
echo ""
echo "08-GN General:"
for f in *Letter* *LETTER*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/08-GN/Correspondence/(OUTGOING)" "GN"
done

for f in *Files*Index* *Form* *FORM*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/08-GN/Forms" "GN"
done

# ====== Operations ======
echo ""
echo "10-OP Operations:"
for f in *Valuation* *Road*Access* *TANKS*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/10-OP/Repairs & Maintenance/(General)" "OP"
done

# ====== Temporary ======
echo ""
echo "11-TM Temporary:"
for f in *.mp4 *.zip *Video* *VIDEO* *ROW*LTKO*; do
    [[ -f "$f" ]] && migrate_file "$f" "$TARGET/11-TM/(Pending Review)" "TM"
done

echo ""
echo "=== MIGRATION COMPLETE ==="
echo ""
echo "Remaining files in source:"
ls -la | grep -v "^d" | wc -l

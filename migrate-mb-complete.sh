#!/bin/bash
# FINAL migration with correct file naming
# YYYY-MM_CompanyCode_Description.ext

SOURCE="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD"
TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD-MB-STRUCTURE"
CODE="MB"

echo "=== FINAL MB MIGRATION ==="
echo "Format: YYYY-MM_MB_Description.ext"
echo ""

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

count=0

# Find and process all files
find "$SOURCE" -type f | while read -r filepath; do
    filename=$(basename "$filepath")
    
    # Skip system files
    [[ "$filename" == ".DS_Store" ]] && continue
    [[ "$filename" =~ ^\..* ]] && continue
    
    # Get extension
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Determine destination folder
    if [[ $filename =~ Invoice ]]; then dest="$TARGET/01-AC/Invoices & Receipts/(Issued)"
    elif [[ $filename =~ Bank|Statement|Account ]]; then dest="$TARGET/01-AC/Bank Statements"
    elif [[ $filename =~ Resolution|Board ]]; then dest="$TARGET/02-BD/Company Resolutions"
    elif [[ $filename =~ Contract|Agreement ]]; then dest="$TARGET/04-CN/General Contracts"
    elif [[ $filename =~ Incorporation|Private.*Compan|Consent ]]; then dest="$TARGET/05-CO/Company Registration Documents"
    elif [[ $filename =~ CIPA|Annual ]]; then dest="$TARGET/05-CO/CIPA/(Annual Returns)"
    elif [[ $filename =~ Business.*Plan|Forecast|Filling.*Station ]]; then dest="$TARGET/06-MS/Projects"
    elif [[ $filename =~ Proposal ]]; then dest="$TARGET/06-MS/Proposals"
    elif [[ $filename =~ Quotation|Quote|BOQ|Estimate|Fuel ]]; then dest="$TARGET/06-MS/Request for Quotations"
    elif [[ $filename =~ Profile|Marketing|Market|Analysis|Traffic ]]; then dest="$TARGET/06-MS/Company Profile & Other Marketing Material"
    elif [[ $filename =~ Logo|Pic|Photo ]]; then dest="$TARGET/06-MS/Artworks & Logos"
    elif [[ $filename =~ Letter ]]; then dest="$TARGET/08-GN/Correspondence/(OUTGOING)"
    elif [[ $filename =~ Valuation ]]; then dest="$TARGET/10-OP/Repairs & Maintenance/(General)"
    elif [[ $filename =~ \.mp4$|\.zip$|Video ]]; then dest="$TARGET/11-TM/(Pending Review)"
    else dest="$TARGET/06-MS/Projects"
    fi
    
    # Extract date from filename
    if [[ $filename =~ ([0-9]{4})[/-]([0-9]{2})[/-]([0-9]{2}) ]]; then
        date="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    elif [[ $filename =~ ([0-9]{2})[/]([0-9]{2})[/]([0-9]{4}) ]]; then
        date="${BASH_REMATCH[3]}-${BASH_REMATCH[1]}"
    elif [[ $filename =~ 20[0-9]{2} ]]; then
        year=$(echo "$filename" | grep -oE "20[0-9]{2}" | head -1)
        date="${year}-01"
    else
        date="2025-01"
    fi
    
    # Create description
    desc=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc)$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr '_' '-' | sed 's/[^a-z0-9-]//g' | sed 's/-+/-/g' | sed 's/^-//' | sed 's/-$//')
    [[ ${#desc} -gt 40 ]] && desc="${desc:0:40}"
    
    newname="${date}_${CODE}_${desc}.${ext}"
    
    # Handle duplicates
    if [[ -f "$dest/$newname" ]]; then
        newname="${date}_${CODE}_${desc}_$(date +%s).${ext}"
    fi
    
    cp "$filepath" "$dest/$newname"
    echo "  $newname"
    ((count++))
done

echo ""
echo "=== COMPLETE: $count files migrated ==="
echo ""
echo "Verify: $TARGET"

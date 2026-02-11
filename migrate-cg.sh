#!/bin/bash
# CG Migration with correct file naming
# YYYY-MM_CG_Description.ext

SOURCE="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU"
TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"
CODE="CG"

echo "=== CLEANING GURU (CG) MIGRATION ==="
echo "Format: YYYY-MM_CG_Description.ext"
echo ""

# Ensure folders exist
mkdir -p "$TARGET/01-AC/Invoices & Receipts/(Issued)"
mkdir -p "$TARGET/01-AC/Bank Statements"
mkdir -p "$TARGET/02-BD/Company Resolutions"
mkdir -p "$TARGET/04-CN/General Contracts"
mkdir -p "$TARGET/05-CO/Company Registration Documents"
mkdir -p "$TARGET/06-MS/Projects"
mkdir -p "$TARGET/06-MS/Artworks & Logos"
mkdir -p "$TARGET/06-MS/Company Profile & Other Marketing Material"
mkdir -p "$TARGET/07-HR/Payroll Records"
mkdir -p "$TARGET/07-HR/Employee Records"
mkdir -p "$TARGET/08-GN/Policies/(Company)"
mkdir -p "$TARGET/08-GN/Forms"

# Find and process all files
find "$SOURCE" -type f | while read -r filepath; do
    filename=$(basename "$filepath")
    
    [[ "$filename" == ".DS_Store" ]] && continue
    [[ "$filename" =~ ^\..* ]] && continue
    
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Determine folder
    if [[ $filename =~ Invoice ]]; then dest="$TARGET/01-AC/Invoices & Receipts/(Issued)"
    elif [[ $filename =~ Bank|Statement ]]; then dest="$TARGET/01-AC/Bank Statements"
    elif [[ $filename =~ Resolution|Board ]]; then dest="$TARGET/02-BD/Company Resolutions"
    elif [[ $filename =~ Contract|Agreement ]]; then dest="$TARGET/04-CN/General Contracts"
    elif [[ $filename =~ Incorporation ]]; then dest="$TARGET/05-CO/Company Registration Documents"
    elif [[ $filename =~ Payroll|Salary|Payslip ]]; then dest="$TARGET/07-HR/Payroll Records"
    elif [[ $filename =~ Offer|Letter|Employee ]]; then dest="$TARGET/07-HR/Employee Records"
    elif [[ $filename =~ Logo|Arwork|Pic ]]; then dest="$TARGET/06-MS/Artworks & Logos"
    elif [[ $filename =~ Marketing|Plan|Profile|Presentation ]]; then dest="$TARGET/06-MS/Company Profile & Other Marketing Material"
    elif [[ $filename =~ Policy|Procedure ]]; then dest="$TARGET/08-GN/Policies/(Company)"
    elif [[ $filename =~ Form ]]; then dest="$TARGET/08-GN/Forms"
    else dest="$TARGET/06-MS/Projects"
    fi
    
    # Extract date
    if [[ $filename =~ ([0-9]{4})[/-]([0-9]{2}) ]]; then
        date="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    elif [[ $filename =~ 20[0-9]{2} ]]; then
        year=$(echo "$filename" | grep -oE "20[0-9]{2}" | head -1)
        date="${year}-01"
    else
        date=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
        [[ -z "$date" ]] && date="2025-01"
    fi
    
    # Description
    desc=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc)$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | sed 's/[^a-z0-9-]//g' | sed 's/-+/-/g' | sed 's/^-//' | sed 's/-$//')
    [[ ${#desc} -gt 40 ]] && desc="${desc:0:40}"
    
    newname="${date}_${CODE}_${desc}.${ext}"
    
    if [[ -f "$dest/$newname" ]]; then
        newname="${date}_${CODE}_${desc}_$(date +%s).${ext}"
    fi
    
    cp "$filepath" "$dest/$newname"
    echo "  $newname"
done

echo ""
echo "=== COMPLETE ==="
echo "Verify: $TARGET"

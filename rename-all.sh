#!/bin/bash
# Comprehensive rename script for all migrated files
# Format: YYYY-MM_CompanyCode_Description.ext

echo "=== COMPREHENSIVE FILE RENAMING ==="
echo ""
echo "This script will rename ALL files in MB and CG structures"
echo "to follow the standard: YYYY-MM_CompanyCode_Description.ext"
echo ""
read -p "Continue? (y/n): " confirm

if [[ $confirm != "y" && $confirm != "Y" ]]; then
    echo "Cancelled."
    exit 0
fi

# ====== FIX CG (Cleaning Guru) ======
echo ""
echo "=== Fixing CG (Cleaning Guru) ==="

CG_TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

# Process all files in CG structure
find "$CG_TARGET" -type f | while read -r filepath; do
    filename=$(basename "$filepath")
    
    # Skip if already properly named
    if [[ $filename =~ ^[0-9]{4}-[0-9]{2}_CG_ ]]; then
        continue
    fi
    
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Determine date from filename or use modification date
    if [[ $filename =~ ([0-9]{4})[/-]([0-9]{2}) ]]; then
        date="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    else
        date=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
        [[ -z "$date" ]] && date="2016-01"
    fi
    
    # Create clean description
    desc=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc|rtf)$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr '_' '-' | tr ',' '' | sed 's/[^a-z0-9-]//g' | sed 's/-+/-/g' | sed 's/^-//' | sed 's/-$//')
    [[ ${#desc} -gt 40 ]] && desc="${desc:0:40}"
    
    newname="${date}_CG_${desc}.${ext}"
    
    # Handle duplicates
    if [[ -f "$filepath" && "$filename" != "$newname" ]]; then
        if [[ -f "$(dirname "$filepath")/$newname" ]]; then
            newname="${date}_CG_${desc}_$(date +%s).${ext}"
        fi
        mv "$filepath" "$(dirname "$filepath")/$newname"
        echo "  ✓ $filename → $newname"
    fi
done

echo ""
echo "=== CG Fix Complete ==="

# ====== FIX MB (Maunatlala Grand Boulevard) ======
echo ""
echo "=== Fixing MB (Maunatlala Grand Boulevard) ==="

MB_TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD-MB-STRUCTURE"

find "$MB_TARGET" -type f | while read -r filepath; do
    filename=$(basename "$filepath")
    
    # Skip if already properly named
    if [[ $filename =~ ^[0-9]{4}-[0-9]{2}_MB_ ]]; then
        continue
    fi
    
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    if [[ $filename =~ ([0-9]{4})[/-]([0-9]{2}) ]]; then
        date="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    else
        date=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
        [[ -z "$date" ]] && date="2025-01"
    fi
    
    desc=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc|rtf)$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr '_' '-' | tr ',' '' | sed 's/[^a-z0-9-]//g' | sed 's/-+/-/g' | sed 's/^-//' | sed 's/-$//')
    [[ ${#desc} -gt 40 ]] && desc="${desc:0:40}"
    
    newname="${date}_MB_${desc}.${ext}"
    
    if [[ -f "$filepath" && "$filename" != "$newname" ]]; then
        if [[ -f "$(dirname "$filepath")/$newname" ]]; then
            newname="${date}_MB_${desc}_$(date +%s).${ext}"
        fi
        mv "$filepath" "$(dirname "$filepath")/$newname"
        echo "  ✓ $filename → $newname"
    fi
done

echo ""
echo "=== MB Fix Complete ==="
echo ""
echo "=== ALL RENAMING COMPLETE ==="

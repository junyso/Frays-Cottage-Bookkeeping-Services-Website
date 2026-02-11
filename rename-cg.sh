#!/bin/bash
# Simple file renamer for CG
# Renames all files to: YYYY-MM_CG_Description.ext

TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

echo "=== Renaming CG Files ==="
echo ""

count=0

find "$TARGET" -type f ! -name "[0-9][0-9][0-9][0-9]-[0-9][0-9]_CG_*" ! -name ".DS_Store" | while read -r filepath; do
    filename=$(basename "$filepath")
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Extract date from filename patterns
    if [[ $filename =~ ([0-9]{4})[-/]([0-9]{2}) ]]; then
        date="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    elif [[ $filename =~ (201[5-9]|202[0-9]) ]]; then
        date=$(echo "$filename" | grep -oE "(201[5-9]|202[0-9])" | head -1)
        date="${date}-01"
    else
        date=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
        [[ -z "$date" ]] && date="2016-01"
    fi
    
    # Create description
    desc=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc|rtf|htm)$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr '_' '-' | tr ',' '' | tr '&' '' | sed 's/[^a-z0-9-]//g' | sed 's/-+/-/g' | sed 's/^-//' | sed 's/-$//')
    [[ ${#desc} -gt 40 ]] && desc="${desc:0:40}"
    
    newname="${date}_CG_${desc}.${ext}"
    
    # Handle duplicates
    if [[ "$filename" != "$newname" ]]; then
        if [[ -f "$(dirname "$filepath")/$newname" ]]; then
            newname="${date}_CG_${desc}_$(date +%s).${ext}"
        fi
        mv "$filepath" "$(dirname "$filepath")/$newname"
        echo "✓ $filename → $newname"
        ((count++))
    fi
done

echo ""
echo "=== Renamed $count files ==="

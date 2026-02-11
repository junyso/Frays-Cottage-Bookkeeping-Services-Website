#!/bin/bash
# Better file renamer for CG
# Keeps meaningful description in filename

TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

echo "=== Better CG Renaming ==="
echo ""

count=0

find "$TARGET" -type f ! -name "[0-9][0-9][0-9][0-9]-[0-9][0-9]_CG_*" ! -name ".DS_Store" | while read -r filepath; do
    filename=$(basename "$filepath")
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Extract date
    if [[ $filename =~ ([0-9]{4})[-/]([0-9]{2}) ]]; then
        date="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    elif [[ $filename =~ (January|February|March|April|May|June|July|August|September|October|November|December) ]]; then
        month="${BASH_REMATCH:0:3}"
        year=$(echo "$filename" | grep -oE "20[0-9]{2}" | head -1)
        mnum=$(date -j -f "%b" "$month" "+%m" 2>/dev/null)
        date="$year-$mnum"
    elif [[ $filename =~ (201[5-9]|202[0-9]) ]]; then
        date=$(echo "$filename" | grep -oE "(201[5-9]|202[0-9])" | head -1)
        date="${date}-01"
    else
        date=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
        [[ -z "$date" ]] && date="2016-01"
    fi
    
    # Create description - keep more meaningful parts
    desc=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc|rtf|htm)$//' | sed 's/CLEANING GURU//g' | sed 's/Cleaning Guru//g' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | tr '_' '-' | tr ',' '' | tr '&' '' | sed 's/[^a-z0-9-]//g' | sed 's/-+/-/g' | sed 's/^-//' | sed 's/-$//')
    
    # If description is too short, use original
    if [[ ${#desc} -lt 3 ]]; then
        short=$(echo "$filename" | sed -E 's/\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc|rtf|htm)$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | head -c 30)
        desc="$short"
    fi
    
    [[ ${#desc} -gt 45 ]] && desc="${desc:0:45}"
    
    newname="${date}_CG_${desc}.${ext}"
    
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

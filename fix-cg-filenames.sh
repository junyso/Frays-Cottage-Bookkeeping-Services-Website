#!/bin/bash
# FIX: Clean up filenames in CG structure
# Format: Title_Period_CompanyCode.ext
# Period = from filename (e.g., "July-2023" or "Jul-2023")
# Date suffix = YYYY-MM from metadata

TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"
CODE="CG"

echo "=== FIXING CG FILENAMES ==="
echo "Target Format: Title_Period_YYYY-MM_Code.ext"
echo ""

count=0

# Find and fix all files
find "$TARGET" -type f -name "*_${CODE}.*" | while read -r filepath; do
    filename=$(basename "$filepath")
    
    # Skip if already correct format
    if [[ $filename =~ ^[0-9]{4}-[0-9]{2}_${CODE}_ ]]; then
        continue
    fi
    
    # Extract extension (properly, no duplication)
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Get metadata date (YYYY-MM)
    mdate=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
    [[ -z "$mdate" ]] && mdate=$(date -r "$(date +%s)" +%Y-%m 2>/dev/null)
    [[ -z "$mdate" ]] && mdate="2024-01"
    
    # Remove existing date and code from filename to get title
    clean=$(echo "$filename" | sed "s/_${CODE}.*//" | sed "s/-${mdate}//" | sed "s/_${mdate}//")
    
    # Clean up title
    title=$(echo "$clean" | sed 's/-2024$//' | sed 's/-2023$//' | sed 's/-2022$//' | sed 's/-2021$//' | sed 's/-2020$//' | sed 's/-2019$//' | sed 's/-2018$//' | sed 's/-2017$//' | sed 's/-2016$//')
    title=$(echo "$title" | sed 's/\.pdf$//' | sed 's/\.docx$//' | sed 's/\.xlsx$//' | sed 's/\.xls$//' | sed 's/\.pptx$//' | sed 's/\.jpg$//' | sed 's/\.png$//' | sed 's/\.csv$//')
    title=$(echo "$title" | tr '-' ' ' | tr '_' ' ' | sed 's/  / /g' | sed 's/^ //' | sed 's/ $//')
    title=$(echo "$title" | tr ' ' '-')
    
    # Truncate if too long
    [[ ${#title} -gt 60 ]] && title="${title:0:60}"
    
    # Build new filename - NO extension duplication
    newname="${title}_${mdate}_${CODE}.${ext}"
    
    # Clean double periods
    newname=$(echo "$newname" | sed 's/\.\./\./g')
    
    # Handle duplicates
    if [[ -f "$filepath" && "$filename" != "$newname" ]]; then
        if [[ -f "$(dirname "$filepath")/$newname" ]]; then
            newname="${title}_${mdate}_${CODE}_$(date +%s).${ext}"
        fi
        mv "$filepath" "$(dirname "$filepath")/$newname"
        echo "✓ $filename → $newname"
        ((count++))
    fi
done

echo ""
echo "=== FIXED $count FILENAMES ==="

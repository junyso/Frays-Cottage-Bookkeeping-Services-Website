#!/bin/bash
# CLEANUP: Fix badly named files in CG structure
# Target format: Title_Period_YYYY-MM_Code.ext

TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

echo "=== CLEANING UP CG FILENAMES ==="
echo ""

count=0

# Find all files with problematic patterns
find "$TARGET" -type f | while read -r filepath; do
    filename=$(basename "$filepath")
    
    # Skip if already clean (YYYY-MM_Code.ext at end)
    if [[ $filename =~ ^[A-Za-z0-9-]+_[0-9]{4}-[0-9]{2}_CG\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc|rtf|htm)$ ]]; then
        continue
    fi
    
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Get metadata date
    mdate=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
    [[ -z "$mdate" ]] && mdate="2024-01"
    
    # Extract title - remove file extension first
    title_no_ext="${filename%.*}"
    
    # Remove patterns like "-YYYY-MM_CG" or "_YYYY-MM_CG" from end
    title=$(echo "$title_no_ext" | sed 's/-[0-9]\{4\}-[0-9]\{2\}_CG$//' | sed 's/_[0-9]\{4\}-[0-9]\{2\}_CG$//')
    
    # Remove extension-like patterns (xlsx.pdf, docx.docx, etc)
    title=$(echo "$title" | sed 's/\.xlsx$//' | sed 's/\.docx$//' | sed 's/\.pdf$//' | sed 's/\.xls$//')
    
    # Remove duplicate periods in name
    title=$(echo "$title" | sed 's/\.\./\./g')
    
    # Clean up title
    title=$(echo "$title" | tr '-' ' ' | tr '_' ' ' | sed 's/  / /g' | sed 's/^ //' | sed 's/ $//')
    title=$(echo "$title" | head -c 60 | sed 's/ $//')
    title=$(echo "$title" | tr ' ' '-')
    
    # Handle empty titles
    if [[ ${#title} -lt 3 ]]; then
        title="Document-Unknown"
    fi
    
    # Build clean name
    newname="${title}_${mdate}_CG.${ext}"
    
    # Remove double extensions
    newname=$(echo "$newname" | sed 's/\.xlsx\.xlsx/\.xlsx/' | sed 's/\.docx\.docx/\.docx/' | sed 's/\.pdf\.pdf/\.pdf/')
    newname=$(echo "$newname" | sed 's/\.xlsx\.pdf/\.pdf/' | sed 's/\.docx\.pdf/\.pdf/')
    
    # Handle duplicates
    if [[ "$filename" != "$newname" ]]; then
        if [[ -f "$(dirname "$filepath")/$newname" ]]; then
            newname="${title}_${mdate}_CG_$(date +%s).${ext}"
        fi
        mv "$filepath" "$(dirname "$filepath")/$newname"
        echo "✓ $filename → $newname"
        ((count++))
    fi
done

echo ""
echo "=== CLEANED $count FILES ==="

#!/bin/bash
# Direct cleanup - removes date duplication from filenames
# Target: Title_Period_YYYY-MM_Code.ext

TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

echo "=== DIRECT CLEANUP ==="
echo ""

# Find files with problematic patterns
find "$TARGET" -type f | while read -r filepath; do
    filename=$(basename "$filepath")
    dirpath=$(dirname "$filepath")
    
    # Skip if already clean
    if [[ $filename =~ ^[A-Za-z0-9\.\-\_]+_[0-9]{4}-[0-9]{2}_CG\.(pdf|docx|xlsx|xls|pptx|jpg|png|csv|doc|rtf|htm)$ ]]; then
        continue
    fi
    
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Get metadata date
    mdate=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
    [[ -z "$mdate" ]] && mdate="2024-01"
    
    # Remove extension
    name="${filename%.*}"
    
    # Fix patterns:
    # 1. "Title-date.pdf-date_CG.pdf" -> "Title-date_YYYY-MM_CG.pdf"
    # 2. "Title.pdf.pdf" -> "Title_YYYY-MM_CG.pdf"
    # 3. "Title-2024-01-CG.pdf" -> "Title_2024-01_CG.pdf"
    
    # Clean name
    newname=$(echo "$name" | sed 's/-CG$//' | sed 's/_CG$//')
    newname=$(echo "$newname" | sed 's/-[0-9]\{4\}-[0-9]\{2\}$//' | sed 's/_[0-9]\{4\}-[0-9]\{2\}$//')
    newname=$(echo "$newname" | sed 's/-Jul-2024$//' | sed 's/-Sep-2024$//' | sed 's/-Oct-2024$//' | sed 's/-Nov-2024$//')
    newname=$(echo "$newname" | sed 's/-Jan-2024$//' | sed 's/-Feb-2024$//' | sed 's/-Mar-2024$//' | sed 's/-Apr-2024$//')
    newname=$(echo "$newname" | sed 's/-May-2024$//' | sed 's/-Jun-2024$//' | sed 's/-Aug-2024$//')
    
    # Clean trailing dots
    newname=$(echo "$newname" | sed 's/\.$//')
    
    # Clean title
    newname=$(echo "$newname" | sed 's/  / /g' | sed 's/^ //' | sed 's/ $//')
    newname=$(echo "$newname" | head -c 80)
    newname=$(echo "$newname" | sed 's/ $//')
    
    # Replace spaces with hyphens
    newname=$(echo "$newname" | tr ' ' '-')
    
    # Build final name
    finalname="${newname}_${mdate}_CG.${ext}"
    
    # Remove double extensions
    finalname=$(echo "$finalname" | sed 's/\.xlsx\.xlsx/\.xlsx/' | sed 's/\.docx\.docx/\.docx/' | sed 's/\.pdf\.pdf/\.pdf/')
    finalname=$(echo "$finalname" | sed 's/\.xlsx\.pdf/\.pdf/' | sed 's/\.docx\.pdf/\.pdf/')
    
    # Handle duplicates
    if [[ "$filename" != "$finalname" ]]; then
        if [[ -f "$dirpath/$finalname" ]]; then
            finalname="${newname}_${mdate}_CG_$(date +%s).${ext}"
        fi
        mv "$filepath" "$dirpath/$finalname"
        echo "✓ $filename → $finalname"
    fi
done

echo ""
echo "=== DONE ==="

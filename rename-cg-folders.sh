#!/bin/bash
# Rename folders with company suffix for CG

CG_TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"

echo "=== Renaming CG Folders with _CG suffix ==="
echo ""

# Process directories
find "$CG_TARGET" -type d | sort -r | while read -r dir; do
    dirname=$(basename "$dir")
    
    # Skip root and already renamed
    [[ "$dir" == "$CG_TARGET" ]] && continue
    [[ "$dirname" =~ _CG$ ]] && continue
    
    # Skip the broken template folder
    [[ "$dirname" =~ ^\{.*\} ]] && continue
    
    # Rename folder
    newname="${dirname}_CG"
    newpath="$(dirname "$dir")/$newname"
    
    if [[ "$dir" != "$newpath" ]]; then
        mv "$dir" "$newpath"
        echo "✓ $dirname → $newname"
    fi
done

echo ""
echo "=== Complete ==="

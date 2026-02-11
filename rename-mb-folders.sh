#!/bin/bash
# Rename folders with company suffix for MB

MB_TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD-MB-STRUCTURE"

echo "=== Renaming MB Folders with _MB suffix ==="
echo ""

# Process directories
find "$MB_TARGET" -type d | sort -r | while read -r dir; do
    dirname=$(basename "$dir")
    
    # Skip root and already renamed
    [[ "$dir" == "$MB_TARGET" ]] && continue
    [[ "$dirname" =~ _MB$ ]] && continue
    
    # Skip the broken template folder
    [[ "$dirname" =~ ^\{.*\} ]] && continue
    
    # Rename folder
    newname="${dirname}_MB"
    newpath="$(dirname "$dir")/$newname"
    
    if [[ "$dir" != "$newpath" ]]; then
        mv "$dir" "$newpath"
        echo "✓ $dirname → $newname"
    fi
done

echo ""
echo "=== Complete ==="

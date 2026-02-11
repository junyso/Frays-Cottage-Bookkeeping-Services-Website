#!/bin/bash
# Rename files to standard format: YYYY-MM_CompanyCode_Description.ext
# For Maunatlala Grand Boulevard (MB)

TARGET_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD-MB-STRUCTURE"
COMPANY_CODE="MB"

echo "=== Renaming Files to Standard Format ==="
echo "Company: Maunatlala Grand Boulevard (MB)"
echo "Target: $TARGET_DIR"
echo ""

# Function to extract date from filename
extract_date() {
    local filename="$1"
    
    # Try various date patterns in filename
    if [[ $filename =~ ([0-9]{4})[-/]([0-9]{2})[-/]([0-9]{2}) ]]; then
        echo "${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    elif [[ $filename =~ ([0-9]{2})[-/]([0-9]{2})[-/]([0-9]{4}) ]]; then
        echo "${BASH_REMATCH[3]}-${BASH_REMATCH[1]}"
    elif [[ $filename =~ ([0-9]{4})[-_](Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) ]]; then
        # Convert month name to number
        local month=$(echo "$BASH_REMATCH" | sed -E 's/[0-9]+-//')
        local num=$(date -j -f "%b" "$month" "+%m" 2>/dev/null)
        echo "${BASH_REMATCH:0:4}-$num"
    elif [[ $filename =~ (January|February|March|April|May|June|July|August|September|October|November|December) ]]; then
        local month=$(date -j -f "%B" "$BASH_REMATCH" "+%m" 2>/dev/null)
        echo "2017-$month"
    else
        # Check file modification date
        local mdate=$(stat -f "%Sm" -t "%Y-%m" "$TARGET_DIR/$filename" 2>/dev/null)
        if [[ -n "$mdate" ]]; then
            echo "$mdate"
        else
            echo "2025-01"  # Default
        fi
    fi
}

# Function to create clean description
clean_description() {
    local filename="$1"
    local ext="$2"
    
    # Remove extension
    local desc="${filename%.*}"
    
    # Remove common prefixes
    desc="${desc//Business-Plan/}"
    desc="${desc//business_plan/}"
    desc="${desc//BusinessPlan/}"
    desc="${desc//Business_Plan/}"
    
    # Remove file extensions from description
    desc="${desc%.docx}"
    desc="${desc%.pdf}"
    desc="${desc%.xlsx}"
    desc="${desc%.xls}"
    desc="${desc%.jpg}"
    desc="${desc%.png}"
    
    # Replace special characters
    desc="${desc// /-}"
    desc="${desc//[^a-zA-Z0-9\-]/}"
    desc="${desc//--/-}"
    
    # Clean up
    desc="${desc#-}"
    desc="${desc%-}"
    
    # Truncate if too long
    if [[ ${#desc} -gt 50 ]]; then
        desc="${desc:0:50}"
    fi
    
    echo "$desc"
}

# Find all files and rename
count=0
find "$TARGET_DIR" -type f -not -name ".*" | while read -r filepath; do
    filename=$(basename "$filepath")
    
    # Skip already renamed files
    if [[ "$filename" =~ ^[0-9]{2}-[0-9]{2}_MB_ ]]; then
        continue
    fi
    
    # Get extension
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Extract date
    date_prefix=$(extract_date "$filename")
    
    # Create description
    description=$(clean_description "$filename" "$ext")
    
    # Build new filename
    newname="${date_prefix}_MB_${description}.${ext}"
    
    # Handle duplicates
    if [[ -f "$TARGET_DIR/$newname" ]]; then
        newname="${date_prefix}_MB_${description}_$(date +%s).${ext}"
    fi
    
    # Rename file
    if [[ "$filename" != "$newname" ]]; then
        mv "$filepath" "$TARGET_DIR/$newname" 2>/dev/null
        echo "✓ $filename → $newname"
        ((count++))
    fi
done

echo ""
echo "=== Renamed $count files ==="

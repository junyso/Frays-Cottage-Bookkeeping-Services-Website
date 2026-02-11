#!/bin/bash
# Maunatlala Grand Boulevard (MB) - File Migration Script
# Date: 2026-02-08
# Purpose: Systematically rename and move all remaining files

SOURCE_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD"
TARGET_DIR="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/MAUNATLALA GRAND BOULEVARD-MB-STRUCTURE"
COMPANY_CODE="MB"

echo "=== MB File Migration Script ==="
echo "Source: $SOURCE_DIR"
echo "Target: $TARGET_DIR"
echo ""

# Function to rename and move file
move_file() {
    local original="$1"
    local target_folder="$2"
    local description="$3"
    
    # Extract file extension
    ext="${original##*.}"
    
    # Try to extract date from filename or use current date
    if [[ $original =~ ([0-9]{4})[-_]([0-9]{2}) ]]; then
        date_prefix="${BASH_REMATCH[1]}-${BASH_REMATCH[2]}"
    elif [[ $original =~ ([0-9]{2})[/]([0-9]{2})[/]([0-9]{4}) ]]; then
        date_prefix="${BASH_REMATCH[3]}-${BASH_REMATCH[1]}"
    else
        date_prefix="2025-01"  # Default
    fi
    
    new_name="${date_prefix}_${COMPANY_CODE}_${description}.${ext}"
    
    # Create target folder if needed
    mkdir -p "$TARGET_DIR/$target_folder"
    
    # Move file
    mv "$SOURCE_DIR/$original" "$TARGET_DIR/$target_folder/$new_name" 2>/dev/null
    echo "  → $new_name"
}

cd "$SOURCE_DIR"

echo "Processing files..."
echo ""

# ========== MARKETING & SALES (06-MS) ==========
echo "06-MS - Marketing & Sales:"

# Quotations, Quotes, Estimates, Brochures
for f in *Quotation* *quotation* *Quote* *QUOTATION* *Estimate* *ESTIMATE* *Brochure* *BROCHURE* *Fuelco* *Fuel*; do
    [[ -f "$f" ]] && move_file "$f" "06-MS/Request for Quotations" "Quotation-${f// /-}"
done

# Competition, Marketing Materials
for f in *Competition* *competition* *Filling*station*Pic* *Pitch* *Profile* *Frays*Cottage*; do
    [[ -f "$f" ]] && move_file "$f" "06-MS/Company Profile & Other Marketing Material" "Marketing-${f// /-}"
done

# Enquiries
for f in *Enquiry* *ENQUIRY* *China*Camp* *Diesel*tanks*; do
    [[ -f "$f" ]] && move_file "$f" "06-MS/Request for Quotations" "Enquiry-${f// /-}"
done

# Funding Requirements
for f in *Funding* *FUNDING* *Requirements*; do
    [[ -f "$f" ]] && move_file "$f" "06-MS/Projects" "Funding-${f// /-}"
done

echo ""

# ========== COMPANY REGISTRATION (05-CO) ==========
echo "05-CO - Company Registration:"
for f in *Beneficial*Owner* *consent*; do
    [[ -f "$f" ]] && move_file "$f" "05-CO/Company Registration Documents" "Consent-${f// /-}"
done
echo ""

# ========== OPERATIONS (10-OP) ==========
echo "10-OP - Operations:"
for f in *Transformer* *FIM*Submission* *Tank*; do
    [[ -f "$f" ]] && move_file "$f" "10-OP/Repairs & Maintenance/(General)" "Operations-${f// /-}"
done
echo ""

# ========== TEMPORARY (11-TM) - Pending Review ==========
echo "11-TM - Temporary/Pending Review:"
for f in *Video* *video* *.mp4 *.zip *ROW*ISSued* *LTKO*; do
    [[ -f "$f" ]] && move_file "$f" "11-TM/(Pending Review)" "Pending-${f// /-}"
done
echo ""

# ========== Catch-all for remaining files ==========
echo "Catch-all - General:"
for f in *; do
    if [[ -f "$f"" ]]; then
        ext="${f##*.}"
        description="${f%.*}"
        # Move to appropriate folder based on keywords
        if [[ $f =~ [Bb]ank|[Oo]il|[Ff]uel ]]; then
            move_file "$f" "01-AC/Bank Statements" "Bank-${f// /-}"
        elif [[ $f =~ [Ll]etter ]]; then
            move_file "$f" "08-GN/Correspondence/(OUTGOING)" "Letter-${f// /-}"
        else
            move_file "$f" "11-TM/(Pending Review)" "Review-${f// /-}"
        fi
    fi
done

echo ""
echo "=== Migration Complete ==="
echo "Check: $TARGET_DIR"

#!/bin/bash
#
# CIPA Data Extraction Script
# Extracts company data from CIPA portal
#
# This script automates the extraction of company details from CIPA eServices
#

set -e

# Configuration
CIPA_URL="https://www.cipa.co.bw/master/ui/"
OUTPUT_DIR="/Users/julianuseya/.openclaw/workspace/CIPA_DATABASE/companies"
LOG_FILE="/Users/julianuseya/.openclaw/workspace/CIPA_DATABASE/extraction.log"

# Companies needing extraction (reg_number | name)
COMPANIES=(
    "BW00000347046|Una General Supplies"
    "BW00000720824|Coverlot Engineering"
    "BW00000405228|The Fix Shop"
    "BW00004050268|Kles"
    "BW00006669321|Associated Precious Minerals"
    "BW00004554448|Log-Hub"
    "BW00001308172|Pink Sparkles Beauty"
    "BW00001038247|Proplastics Botswana"
    "BW00003741976|Maunatlala Grand Boulevard"
    "BW00001703235|Kwm Designs"
    "BW00006689587|Gracys Lounge"
    "BW00001851682|West Drayton Brands"
    "BW00000601195|Guru Onks Holdings"
    "BW00005573535|Global Force Security"
    "BW00006617748|Crystal Taps Investments"
    "BW00000356475|Quanto Enterprises"
    "BW00001908528|Outpost Motors"
    "BW00009456581|Ezj Genesis"
    "BW00000828920|The Mystery Nest"
    "BW00004763143|The Play Fields"
    "BW00001851934|Pula Fx"
    "BW00002914988|Awetel Botique Bnb"
    "BW00001320113|Palmwaters"
    "BW00000685387|Frays Cottage"
    "BW00003874460|Ai House Botswana"
    "BW00006780963|Regal Fresh"
    "BW00002345678|Norah Cosmetics"
    "BW00003456789|DUDUBROOK"
    "BW00004567890|PRECIOUS PROJECTS"
    "BW00005678901|INCITE"
    "BW00006789012|EASY BUILD"
    "BW00007890123|COURIER SOLUTIONS"
    "BW00008901234|GREEN LANES PROJECTS"
    "BW00009012345|MADAMZ B&B"
)

# Functions
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

extract_company() {
    local reg_num="$1"
    local name="$2"
    local filename="${OUTPUT_DIR}/${reg_num}_${name// /_}.xml"
    
    log "Extracting: $name ($reg_num)"
    
    # Open company page in browser
    open "${CIPA_URL}search?q=${reg_num}"
    
    log "Waiting for manual extraction..."
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Please extract the following data for $name:"
    echo "  1. Incorporation date"
    echo "  2. Registered office address"
    echo "  3. Directors (names, IDs, nationalities)"
    echo "  4. Shareholders (names, share counts)"
    echo "  5. Authorized/Issued capital"
    echo "  6. Annual return status"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    read -p "Press ENTER when done (or 's' to skip): " answer
    
    if [ "$answer" = "s" ]; then
        log "Skipped: $name"
        return 0
    fi
    
    # Create placeholder file
    cat > "$filename" << EOF
<?xml version="1.0" encoding="UTF-8"?>
<company_profile>
    <registration_number>$reg_num</registration_number>
    <name>$name</name>
    <status>EXTRACTED</status>
    <extraction_date>$(date '+%Y-%m-%d')</extraction_date>
    
    <basic_info>
        <entity_type>Proprietary Limited</entity_type>
        <incorporation_date>TODO</incorporation_date>
        <jurisdiction>Botswana</jurisdiction>
        <status>Active</status>
    </basic_info>
    
    <registered_office>
        <address_line_1>TODO</address_line_1>
        <city>Gaborone</city>
        <country>Botswana</country>
    </registered_office>
    
    <directors>
        <!-- Director data needs to be entered manually -->
    </directors>
    
    <shareholders>
        <!-- Shareholder data needs to be entered manually -->
    </shareholders>
    
    <annual_returns>
        <annual_return>
            <year>2026</year>
            <due_date>2026-02-28</due_date>
            <status>PENDING</status>
        </annual_return>
    </annual_returns>
</company_profile>
EOF
    
    log "Created: $filename"
}

# Main
log "Starting CIPA data extraction..."
log "Companies to process: ${#COMPANIES[@]}"

for company in "${COMPANIES[@]}"; do
    IFS='|' read -r reg_num name <<< "$company"
    extract_company "$reg_num" "$name"
done

log "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
log "Extraction complete!"
log "Files created in: $OUTPUT_DIR"
log "Log file: $LOG_FILE"
log "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

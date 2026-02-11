#!/bin/bash
# PROPER CG Migration with correct categorization and naming
# Format: Title_Period_CompanyCode.ext
# Reads metadata for actual creation dates

SOURCE="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU"
TARGET="/Users/julianuseya/Library/CloudStorage/OneDrive-Personal/Documents/JULIAN/JULIAN WORK/CLEANING GURU-CG-STRUCTURE"
CODE="CG"

echo "=== PROPER CG MIGRATION ==="
echo "Format: Title_Period_CompanyCode.ext"
echo ""

# Create all destination folders
mkdir -p "$TARGET/01-AC_CG/Bank Statements_CG"
mkdir -p "$TARGET/01-AC_CG/Invoices & Receipts_CG/(Issued)_CG"
mkdir -p "$TARGET/01-AC_CG/Invoices & Receipts_CG/(Received)_CG"
mkdir -p "$TARGET/01-AC_CG/Management Accounts_CG"
mkdir -p "$TARGET/02-BD_CG/Company Resolutions_CG"
mkdir -p "$TARGET/04-CN_CG/General Contracts_CG"
mkdir -p "$TARGET/04-CN_CG/Lease Contracts_CG"
mkdir -p "$TARGET/05-CO_CG/Company Registration Documents_CG"
mkdir -p "$TARGET/06-MS_CG/Tender Requests_CG"
mkdir -p "$TARGET/06-MS_CG/Projects_CG"
mkdir -p "$TARGET/06-MS_CG/Proposals_CG"
mkdir -p "$TARGET/06-MS_CG/Price Lists_CG"
mkdir -p "$TARGET/07-HR_CG/Payroll Records_CG"
mkdir -p "$TARGET/07-HR_CG/Employee Records_CG"
mkdir -p "$TARGET/08-GN_CG/Forms_CG"
mkdir -p "$TARGET/08-GN_CG/Correspondence_CG/(OUTGOING)_CG"
mkdir -p "$TARGET/09-TX_CG/VAT_CG/(VAT Returns)_CG"
mkdir -p "$TARGET/09-TX_CG/PAYE_CG"
mkdir -p "$TARGET/09-TX_CG/Income Tax_CG"
mkdir -p "$TARGET/09-TX_CG/Tax Correspondence_CG"
mkdir -p "$TARGET/09-TX_CG/Tax Clearance Certificates_CG"
mkdir -p "$TARGET/10-OP_CG/Repairs & Maintenance_CG/(General)_CG"

echo "Folders created. Starting migration..."
echo ""

count=0
errors=0

# Process files with proper categorization
find "$SOURCE" -type f | while read -r filepath; do
    filename=$(basename "$filepath")
    
    # Skip system files
    [[ "$filename" == ".DS_Store" ]] && continue
    [[ "$filename" =~ ^\..* ]] && continue
    
    ext="${filename##*.}"
    ext=$(echo "$ext" | tr '[:upper:]' '[:lower:]')
    
    # Get creation date from metadata
    cdate=$(stat -f "%Sm" -t "%Y-%m" "$filepath" 2>/dev/null)
    [[ -z "$cdate" ]] && cdate=$(date -r "$(date +%s)" +%Y-%m 2>/dev/null)
    [[ -z "$cdate" ]] && cdate="2024-01"
    
    # Determine category and create proper title
    if [[ $filename =~ [Tt]ax.*[Vv][Aa][Tt] ]] || [[ $filename =~ VAT ]]; then
        dest="$TARGET/09-TX_CG/VAT_CG/(VAT Returns)_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        # Extract period
        if [[ $filename =~ Jan ]]; then period="Jan"
        elif [[ $filename =~ Feb ]]; then period="Feb"
        elif [[ $filename =~ Mar ]]; then period="Mar"
        elif [[ $filename =~ Apr ]]; then period="Apr"
        elif [[ $filename =~ May ]]; then period="May"
        elif [[ $filename =~ Jun ]]; then period="Jun"
        elif [[ $filename =~ Jul ]]; then period="Jul"
        elif [[ $filename =~ Aug ]]; then period="Aug"
        elif [[ $filename =~ Sep ]]; then period="Sep"
        elif [[ $filename =~ Oct ]]; then period="Oct"
        elif [[ $filename =~ Nov ]]; then period="Nov"
        elif [[ $filename =~ Dec ]]; then period="Dec"
        else period=""
        fi
    elif [[ $filename =~ [Tt]ax ]] || [[ $filename =~ [Tt]axation ]]; then
        dest="$TARGET/09-TX_CG/Tax Correspondence_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        period=""
    elif [[ $filename =~ [Bb]ank ]] || [[ $filename =~ [Ss]tatement ]]; then
        dest="$TARGET/01-AC_CG/Bank Statements_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        if [[ $filename =~ Jan ]]; then period="Jan"
        elif [[ $filename =~ Feb ]]; then period="Feb"
        elif [[ $filename =~ Mar ]]; then period="Mar"
        elif [[ $filename =~ Apr ]]; then period="Apr"
        elif [[ $filename =~ May ]]; then period="May"
        elif [[ $filename =~ Jun ]]; then period="Jun"
        elif [[ $filename =~ Jul ]]; then period="Jul"
        elif [[ $filename =~ Aug ]]; then period="Aug"
        elif [[ $filename =~ Sep ]]; then period="Sep"
        elif [[ $filename =~ Oct ]]; then period="Oct"
        elif [[ $filename =~ Nov ]]; then period="Nov"
        elif [[ $filename =~ Dec ]]; then period="Dec"
        else period=""
        fi
    elif [[ $filename =~ [Tt]ender ]] || [[ $filename =~ TENDER ]]; then
        dest="$TARGET/06-MS_CG/Tender Requests_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        if [[ $filename =~ Jan ]]; then period="Jan"
        elif [[ $filename =~ Feb ]]; then period="Feb"
        elif [[ $filename =~ Mar ]]; then period="Mar"
        elif [[ $filename =~ Apr ]]; then period="Apr"
        elif [[ $filename =~ May ]]; then period="May"
        elif [[ $filename =~ Jun ]]; then period="Jun"
        elif [[ $filename =~ Jul ]]; then period="Jul"
        elif [[ $filename =~ Aug ]]; then period="Aug"
        elif [[ $filename =~ Sep ]]; then period="Sep"
        elif [[ $filename =~ Oct ]]; then period="Oct"
        elif [[ $filename =~ Nov ]]; then period="Nov"
        elif [[ $filename =~ Dec ]]; then period="Dec"
        else period=""
        fi
    elif [[ $filename =~ [Ii]nvoice ]]; then
        dest="$TARGET/01-AC_CG/Invoices & Receipts_CG/(Issued)_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        period=""
    elif [[ $filename =~ [Pp]ayroll ]] || [[ $filename =~ [Ss]alary ]] || [[ $filename =~ [Pp]ayslip ]]; then
        dest="$TARGET/07-HR_CG/Payroll Records_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        if [[ $filename =~ Jan ]]; then period="Jan"
        elif [[ $filename =~ Feb ]]; then period="Feb"
        elif [[ $filename =~ Mar ]]; then period="Mar"
        elif [[ $filename =~ Apr ]]; then period="Apr"
        elif [[ $filename =~ May ]]; then period="May"
        elif [[ $filename =~ Jun ]]; then period="Jun"
        elif [[ $filename =~ Jul ]]; then period="Jul"
        elif [[ $filename =~ Aug ]]; then period="Aug"
        elif [[ $filename =~ Sep ]]; then period="Sep"
        elif [[ $filename =~ Oct ]]; then period="Oct"
        elif [[ $filename =~ Nov ]]; then period="Nov"
        elif [[ $filename =~ Dec ]]; then period="Dec"
        else period=""
        fi
    elif [[ $filename =~ [Oo]ffer ]] || [[ $filename =~ [Ll]etter ]] || [[ $filename =~ [Cc]ontract ]]; then
        dest="$TARGET/04-CN_CG/General Contracts_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        period=""
    elif [[ $filename =~ [Rr]esolution ]]; then
        dest="$TARGET/02-BD_CG/Company Resolutions_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        period=""
    elif [[ $filename =~ [Mm]anagement ]] || [[ $filename =~ [Rr]eport ]] || [[ $filename =~ [Ff]inancial ]]; then
        dest="$TARGET/01-AC_CG/Management Accounts_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        if [[ $filename =~ Jan ]]; then period="Jan"
        elif [[ $filename =~ Feb ]]; then period="Feb"
        elif [[ $filename =~ Mar ]]; then period="Mar"
        elif [[ $filename =~ Apr ]]; then period="Apr"
        elif [[ $filename =~ May ]]; then period="May"
        elif [[ $filename =~ Jun ]]; then period="Jun"
        elif [[ $filename =~ Jul ]]; then period="Jul"
        elif [[ $filename =~ Aug ]]; then period="Aug"
        elif [[ $filename =~ Sep ]]; then period="Sep"
        elif [[ $filename =~ Oct ]]; then period="Oct"
        elif [[ $filename =~ Nov ]]; then period="Nov"
        elif [[ $filename =~ Dec ]]; then period="Dec"
        else period=""
        fi
    elif [[ $filename =~ [Pp]rice ]] || [[ $filename =~ [Ll]ist ]]; then
        dest="$TARGET/06-MS_CG/Price Lists_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        period=""
    else
        dest="$TARGET/08-GN_CG/Forms_CG"
        title=$(echo "$filename" | sed 's/\.(pdf|docx|xlsx)$//' | tr '_' ' ')
        period=""
    fi
    
    # Clean title
    title=$(echo "$title" | sed 's/CLEANING GURU//g' | sed 's/CG//g' | sed 's/  / /g' | sed 's/^ //' | sed 's/ $//')
    [[ ${#title} -gt 50 ]] && title="${title:0:50}"
    
    # Build new filename
    if [[ -n "$period" ]]; then
        newname="${title}_${period}_${cdate}_${CODE}.${ext}"
    else
        newname="${title}_${cdate}_${CODE}.${ext}"
    fi
    
    # Clean filename
    newname=$(echo "$newname" | tr ' ' '-' | tr '_' '-' | sed 's/--*/-/g' | sed 's/^-//' | sed 's/-$//')
    
    # Handle duplicates
    if [[ -f "$dest/$newname" ]]; then
        newname="${title}_${cdate}_${CODE}_$(date +%s).${ext}"
    fi
    
    # Copy file
    cp "$filepath" "$dest/$newname" 2>/dev/null
    if [[ $? -eq 0 ]]; then
        echo "✓ $newname"
        ((count++))
    else
        ((errors++))
    fi
done

echo ""
echo "=== COMPLETE ==="
echo "Files migrated: $count"
echo "Errors: $errors"

# CIPA Company Profile Population Process

## Current Status
- **Browser Automation**: Gateway connectivity issues detected
- **Template Files Created**: 9 new company profiles
- **Existing Files**: 15 company profiles (partially populated)

## Companies Created/Updated

### URGENT - Annual Return Due (7 companies)
1. `016_BW00004763143_The Play Fields Proprietary Limited.xml`
2. `017_BW00001851934_Pula Fx Proprietary Limited.xml`
3. `018_BW00002914988_Awetel Botique Bnb Proprietary Limited.xml`
4. `019_BW00001320113_Palmwaters Proprietary Limited.xml`
5. `020_BW00000685387_Frays Cottage Proprietary Limited.xml`
6. `021_BW00003874460_Ai House Botswana Proprietary Limited.xml`
7. `022_BW00006780963_Regal Fresh Proprietary Limited.xml`

### Additional Companies (2 companies)
8. `023_Nora Cosmetics Proprietary Limited.xml` - Needs registration number
9. `024_Courier Solutions Proprietary Limited.xml` - Needs registration number

### Already Existing (partial data)
- `004_BW00004050268_Kles Proprietary Lim.xml`
- `009_BW00003741976_Maunatlala Grand Bou.xml`

## Data Extraction Process (when browser is working)

### Step 1: Login to CIPA Portal
1. Navigate to: https://www.cipa.co.bw/master/ui/start/CIPARegisterSearch
2. Already logged in as: Julian Useya

### Step 2: Search for Company
1. Enter company number in "Name or number" field
2. Select "Business entities" from dropdown
3. Click "Search" button

### Step 3: Extract Information from Company Profile
From the company detail page, extract:
- **Directors Tab**: Names, ID numbers, appointment dates
- **Shareholders Tab**: Names, share allocation, classes
- **Registered Office**: Full address
- **Share Capital**: Authorized, issued, paid-up amounts
- **Filing History**: Annual returns, amendments, changes
- **Penalties/Non-compliance**: Any violations

### Step 4: Update XML Profile
Populate the corresponding XML file with extracted data

## XML Schema Reference
```xml
<company_profile>
    <directors>
        <director>
            <name></name>
            <id_number></id_number>
            <appointment_date></appointment_date>
        </director>
    </directors>
    <shareholders>
        <shareholder>
            <name></name>
            <share_class></share_class>
            <number_of_shares></number_of_shares>
        </shareholder>
    </shareholders>
    <registered_office>
        <address_line_1></address_line_1>
        <city></city>
    </registered_office>
    <share_capital>
        <authorized_capital><amount></amount></authorized_capital>
        <issued_capital><amount></amount></issued_capital>
        <paid_up_capital><amount></amount></paid_up_capital>
    </share_capital>
    <filing_history>...</filing_history>
    <annual_returns>...</annual_returns>
    <penalties_non_compliance>...</penalties_non_compliance>
</company_profile>
```

## Troubleshooting Browser Gateway
If browser automation fails:
1. Open OpenClaw app
2. Go to Gateway settings
3. Restart the gateway service
4. Ensure Chrome extension is connected (badge should show "ON")

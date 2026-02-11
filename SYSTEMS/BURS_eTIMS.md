# SYSTEMS TOUR - BURS/eTIMS Exploration

**Date:** 2026-02-09
**Status:** NOT ACCESSIBLE (URL not resolvable externally)
**Access:** Requires credentials via internal network or VPN

---

## What is BURS/eTIMS?

**BURS** = Botswana Unified Revenue Service
**eTIMS** = Electronic Tax Information Management System

---

## Known Tax Types & Deadlines (from osTicket patterns)

### Monthly Deadlines
| Tax Type | Deadline | Frequency |
|----------|----------|-----------|
| **VAT Returns** | 25th of each month | Monthly |
| **PAYE Returns** | 15th of each month | Monthly |

### Companies with VAT Obligations (Tracked in osTicket)
1. Royal Construction (BW00001182423)
2. Ernlet Projects (BW00000059598)
3. Lore - Master Building (BW00000113727)
4. Montelview Holdings (BW00000418913)
5. Norah Cosmetics / Pink Sparkles Beauty Studios
6. Kles (BW00004050268)
7. Space Interiors
8. Courier Solutions
9. Frays Cottage
10. Marctizmo
11. Great-Land Construction
12. Lightening Strike
13. Ox Brands

### Companies with PAYE Obligations
1. **Kles** (BW00004050268) - Primary payroll processor

---

## Tax Compliance Workflow (From osTicket)

```
Calendar Event Created → Ticket Generated → Compliance Team → Filing → Confirmation
```

### Pattern:
- "Frays Cottage Calendar - Due [timeframe]: [Tax Type] Return"
- Created ~2 weeks before deadline
- Assigned to appropriate team member

---

## Automation Opportunities

### Current Manual Steps:
1. Login to BURS/eTIMS portal
2. Download tax certificates (VAT, PAYE, withholding)
3. File returns before deadlines
4. Download filing confirmations
5. Save to company folders (09-TX_CompanyCode/)

### Potential Automation:
1. **Browser Automation:** Automa could login and download filings
2. **API Integration:** BURS may have API for large taxpayers
3. **Calendar Sync:** Already working via LuxSoft
4. **Reminder System:** Already working via osTicket

---

## Missing Information

⚠️ **Credentials Required:**
- BURS eTIMS Portal URL
- Login credentials (TIN + password)
- Which companies have eTIMS access
- PIN/TIN numbers for all 82 entities

⚠️ **Knowledge Gaps:**
- Specific BURS portal URL (etims.burs.org.bw not accessible)
- API availability
- Bulk filing capabilities
- Integration options

---

## Next Steps

1. **Get credentials from Julian**
2. **Test BURS portal access**
3. **Document TIN/PIN numbers for all 82 companies**
4. **Map tax obligations to companies**
5. **Explore automation options**

---

## Related Systems

| System | Status | Notes |
|--------|--------|-------|
| CIPA Portal | ✅ Explored | 82 entities mapped |
| FrontAccounting | ✅ Explored | 30 instances, API built |
| osTicket | ✅ Explored | 1,198 tickets, workflows |
| LuxSoft Calendar | ✅ Explored | 200+ recurring events |
| **BURS/eTIMS** | ⏳ **BLOCKED** | Needs credentials |
| Sage One | ⏳ Not explored | Subscription billing |
| Email | ⏳ Not explored | Communication |
| Automa | ⏳ Not explored | Browser automation |

---

## Companies Missing CIPA Numbers (Need TIN Mapping)

From osTicket and FrontAccounting, these companies need CIPA registration numbers:

| Company | FA Instance | Notes |
|---------|-------------|-------|
| Ernlet Projects | ✅ | Multiple entities |
| Madamz BnB | ❓ | Business name? |
| Marctizmo | ✅ | VAT client |
| Royal Construction | ✅ | VAT client |
| Lore - Master Building | ✅ | VAT client |
| Montelview Holdings | ✅ | VAT client |

---

## Tax Filing Calendar (Extracted from osTicket)

| Month | Key Deadlines |
|-------|--------------|
| January | VAT Due 25th |
| February | VAT Due 25th, PAYE Due 15th, **Annual Returns Due 28th** |
| March | VAT Due 25th, PAYE Due 15th |
| April | VAT Due 25th, PAYE Due 15th |
| May | VAT Due 25th, PAYE Due 15th |
| June | VAT Due 25th, PAYE Due 15th |
| July | VAT Due 25th, PAYE Due 15th |
| August | VAT Due 25th, PAYE Due 15th |
| September | VAT Due 25th, PAYE Due 15th |
| October | VAT Due 25th, PAYE Due 15th |
| November | VAT Due 25th, PAYE Due 15th |
| December | VAT Due 25th (annual), PAYE Due 15th |

---

## Notes from osTicket (Tax-Related Tickets)

### VAT Return Workflow:
- Subject: "VAT Return Due: [Company Name]"
- Created: ~10 days before due date
- Assigned to: Compliance team
- Attachments: Sales/purchase logs, bank statements

### PAYE Return Workflow:
- Subject: "PAYE Return Due: Kles"
- Created: ~5 days before 15th
- Assigned to: Payroll team
- Attachments: Payroll reports, P10 forms

### Tax Clearance Workflow:
- Subject: "Tax Clearance Certificate Request"
- Generated when needed for tenders/bids
- Requires: Up-to-date VAT, PAYE, Corporate Tax

---

## Questions for Julian

1. **Do you have BURS eTIMS credentials?**
2. **Are all 82 companies registered for VAT/PAYE?**
3. **Which companies use eTIMS vs manual filing?**
4. **Do you use a tax practitioner portal?**
5. **What's the current pain point with BURS?**

---

**STATUS:** 🚧 BLOCKED - Needs credentials and access

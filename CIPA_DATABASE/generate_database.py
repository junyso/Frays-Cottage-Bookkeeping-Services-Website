#!/usr/bin/env python3
"""
CIPA Company Database Generator
Populates company profiles from CIPA portal data
"""

import json
import xml.etree.ElementTree as ET
from datetime import datetime
from pathlib import Path

class CIPACompanyDatabase:
    def __init__(self, base_path="/Users/julianuseya/.openclaw/workspace/CIPA_DATABASE"):
        self.base_path = Path(base_path)
        self.companies_path = self.base_path / "companies"
        self.business_names_path = self.base_path / "business_names"
        self.reports_path = self.base_path / "reports"
        self.templates_path = self.base_path / "templates"
        
        # Master company data from CIPA portal
        self.companies = [
            {"name": "Una General Supplies Proprietary Limited", "reg_number": "BW00000347046", "industry": "Trading/Retail"},
            {"name": "Coverlot Engineering Proprietary Limited", "reg_number": "BW00000720824", "industry": "Engineering/Construction"},
            {"name": "The Fix Shop Proprietary Limited", "reg_number": "BW00000405228", "industry": "Design/Creative"},
            {"name": "Kles Proprietary Limited", "reg_number": "BW00004050268", "industry": "Professional Services"},
            {"name": "Associated Precious Minerals Company Proprietary Limited", "reg_number": "BW00006669321", "industry": "Mining/Minerals"},
            {"name": "Log-Hub Proprietary Limited", "reg_number": "BW00004554448", "industry": "Logistics"},
            {"name": "Pink Sparkles Beauty Studio Proprietary Limited", "reg_number": "BW00001308172", "industry": "Beauty/Beauty Services", "trading_as": "Nora Cosmetics"},
            {"name": "Proplastics Botswana Proprietary Limited", "reg_number": "BW00001038247", "industry": "Construction/Engineering"},
            {"name": "Maunatlala Grand Boulevard Proprietary Limited", "reg_number": "BW00003741976", "industry": "Hospitality/Retail"},
            {"name": "Kwm Designs Proprietary Limited", "reg_number": "BW00001703235", "industry": "Design/Creative"},
            {"name": "Gracys Lounge Proprietary Limited", "reg_number": "BW00006689587", "industry": "Hospitality/F&B"},
            {"name": "West Drayton Brands Proprietary Limited", "reg_number": "BW00001851682", "industry": "Retail/Trading"},
            {"name": "Guru Onks Holdings Proprietary Limited", "reg_number": "BW00000601195", "industry": "Holding/Investment"},
            {"name": "Global Force Security Proprietary Limited", "reg_number": "BW00005573535", "industry": "Security"},
            {"name": "Crystal Taps Investments Proprietary Limited", "reg_number": "BW00006617748", "industry": "Investment/Holding"},
            {"name": "Collins Travel & Tours", "reg_number": None, "type": "Business Name", "industry": "Travel/Tourism"},
            {"name": "Quanto Enterprises Proprietary Limited", "reg_number": "BW00000356475", "industry": "Professional Services"},
            {"name": "Outpost Motors Proprietary Limited", "reg_number": "BW00001908528", "industry": "Automotive"},
            {"name": "Ezj Genesis Proprietary Limited", "reg_number": "BW00009456581", "industry": "Food/Agri"},
            {"name": "The Mystery Nest Proprietary Limited", "reg_number": "BW00000828920", "industry": "Beauty/Hospitality"},
            {"name": "Afrimond Diamond & Jewellery Institute Proprietary Limited", "reg_number": "BW00001711566", "industry": "Mining/Jewellery"},
            {"name": "Maximus Ambition Proprietary Limited", "reg_number": "BW00005104600", "industry": "Investment/Trading"},
            {"name": "Thaega Investments Proprietary Limited", "reg_number": "BW00001106731", "industry": "Investment"},
            {"name": "Athena Logistics Proprietary Limited", "reg_number": "BW00005230873", "industry": "Logistics"},
            {"name": "Mcmm Enterprises Proprietary Limited", "reg_number": "BW00004314331", "industry": "Trading"},
            {"name": "Keep Calm Proprietary Limited", "reg_number": "BW00002321594", "industry": "Hospitality/Services"},
            {"name": "Courier Solutions Proprietary Limited", "reg_number": "BW00000351656", "industry": "Logistics"},
            {"name": "Majande Proprietary Limited", "reg_number": "BW00000442167", "industry": "Trading"},
            {"name": "Perfect Launch Investments Proprietary Limited", "reg_number": "BW00004221365", "industry": "Investment"},
            {"name": "Glitzcom Enterprises Proprietary Limited", "reg_number": "BW00006234265", "industry": "Technology/Trading"},
            {"name": "Alipam Proprietary Limited", "reg_number": "BW00000664685", "industry": "Trading"},
            {"name": "Dudubrook Quilting Loft Proprietary Limited", "reg_number": "BW00002517614", "industry": "Textile/Trading"},
            {"name": "Honey Berry Investments Proprietary Limited", "reg_number": "BW00004228518", "industry": "Investment"},
            {"name": "Tsiako Empire Investments Proprietary Limited", "reg_number": "BW00006199850", "industry": "Investment"},
            {"name": "Eco-Connect Plus Proprietary Limited", "reg_number": "BW00001242547", "industry": "Environmental"},
            {"name": "Royalport Proprietary Limited", "reg_number": "BW00004141798", "industry": "Trading/Services"},
            {"name": "Notsa Proprietary Limited", "reg_number": "BW00001328168", "industry": "Trading"},
            {"name": "Great-Land Construction Proprietary Limited", "reg_number": "BW00001108793", "industry": "Construction"},
            {"name": "Lightening Strike Proprietary Limited", "reg_number": "BW00001106384", "industry": "Security"},
            {"name": "Hillbloom Proprietary Limited", "reg_number": "BW00001910370", "industry": "Agriculture/Trading"},
            {"name": "Uniglobal Solutions Proprietary Limited", "reg_number": "BW00003162861", "industry": "Technology/Services"},
            {"name": "Tshenolo Waste Management Botswana Proprietary Limited", "reg_number": "BW00004954034", "industry": "Waste Management"},
            {"name": "Impulse Idea Proprietary Limited", "reg_number": "BW00003259157", "industry": "Creative/Trading"},
            {"name": "3d Works Proprietary Limited", "reg_number": "BW00001918423", "industry": "Design/Printing"},
            {"name": "Hoppers Proprietary Limited", "reg_number": "BW00003224948", "industry": "Trading"},
            {"name": "Tiche Maart Stars Proprietary Limited", "reg_number": "BW00001876482", "industry": "Entertainment/Events"},
            {"name": "Darlberry Proprietary Limited", "reg_number": "BW00004119920", "industry": "Fashion/Retail"},
            {"name": "David Empire Investments Proprietary Limited", "reg_number": "BW00000362027", "industry": "Investment"},
            {"name": "Frays Cottage Proprietary Limited", "reg_number": "BW00000685387", "industry": "Professional Services"},
            {"name": "Palmwaters Proprietary Limited", "reg_number": "BW00001320113", "industry": "Hospitality/Tourism"},
            {"name": "Hash-Turn Investment Proprietary Limited", "reg_number": "BW00000594641", "industry": "Investment"},
            {"name": "Senosa Holdings Proprietary Limited", "reg_number": "BW00009080487", "industry": "Investment"},
            {"name": "Space Interiors Proprietary Limited", "reg_number": "BW00000133034", "industry": "Design/Interior"},
            {"name": "Nidarshini Consulting Proprietary Limited", "reg_number": "BW00001182978", "industry": "Consulting"},
            {"name": "Rln Investments Proprietary Limited", "reg_number": "BW00003113878", "industry": "Investment"},
            {"name": "Stelfin Proprietary Limited", "reg_number": "BW00004870778", "industry": "Financial Services"},
            {"name": "Kimblynn Holdings Proprietary Limited", "reg_number": "BW00001460443", "industry": "Investment"},
            {"name": "The Play Fields Proprietary Limited", "reg_number": "BW00004763143", "industry": "Sports/Recreation"},
            {"name": "Pula Fx Proprietary Limited", "reg_number": "BW00001851934", "industry": "Financial Services"},
            {"name": "Awetel Botique Bnb Proprietary Limited", "reg_number": "BW00002914988", "industry": "Hospitality"},
            {"name": "Ai House Botswana Proprietary Limited", "reg_number": "BW00003874460", "industry": "Technology"},
            {"name": "Regal Fresh Proprietary Limited", "reg_number": "BW00006780963", "industry": "Food/Retail"},
            {"name": "Chilli Boys Security Proprietary Limited", "reg_number": "BW00004954050", "industry": "Security"},
            {"name": "Modern Hotel Supplies Proprietary Limited", "reg_number": "BW00001597726", "industry": "Trading/Hospitality"},
            {"name": "Ox Brands Proprietary Limited", "reg_number": "BW00001851178", "industry": "Trading/Retail"},
            {"name": "Stalement Knights Proprietary Limited", "reg_number": "BW00002328011", "industry": "Security"},
            {"name": "Air Splash Proprietary Limited", "reg_number": "BW00002158000", "industry": "Automotive/Trading"},
            {"name": "Bitsa Ice Cream Proprietary Limited", "reg_number": "BW00002160291", "industry": "Food/Manufacturing"},
            {"name": "Golive Solutions Proprietary Limited", "reg_number": "BW00001418234", "industry": "Technology"},
            {"name": "Lazama Investments Proprietary Limited", "reg_number": "BW00000457380", "industry": "Investment"},
        ]
        
        # Companies with urgent annual returns
        self.urgent_annual_returns = [
            {"name": "The Play Fields Proprietary Limited", "reg_number": "BW00004763143", "due_date": "2026-02-28"},
            {"name": "Pula Fx Proprietary Limited", "reg_number": "BW00001851934", "due_date": "2026-02-28"},
            {"name": "Awetel Botique Bnb Proprietary Limited", "reg_number": "BW00002914988", "due_date": "2026-02-28"},
            {"name": "Palmwaters Proprietary Limited", "reg_number": "BW00001320113", "due_date": "2026-02-28"},
            {"name": "Frays Cottage Proprietary Limited", "reg_number": "BW00000685387", "due_date": "2026-02-28"},
            {"name": "Ai House Botswana Proprietary Limited", "reg_number": "BW00003874460", "due_date": "2026-02-28"},
            {"name": "Regal Fresh Proprietary Limited", "reg_number": "BW00006780963", "due_date": "2026-02-28"},
        ]
        
        # Business names
        self.business_names = [
            {"name": "1966", "reg_number": "BN2019/18772"},
            {"name": "Feruka Oils", "reg_number": "BN2021/132726"},
            {"name": "Cotton Affair", "reg_number": "BN2024/240232"},
            {"name": "Palmwaters Travel And Tours", "reg_number": "BN2024/239366"},
            {"name": "Mk Projects & Works", "reg_number": "BN2024/238107"},
            {"name": "Spares Hub", "reg_number": "BN2024/237268"},
            {"name": "Clemercia Farm Produce", "reg_number": "BN2021/116809"},
            {"name": "Unak Clothing", "reg_number": "BN2021/125003"},
            {"name": "Ernlet's Easybuild Innovation", "reg_number": "BN2024/227682"},
            {"name": "Bitsa Fmcg", "reg_number": "BN2023/204884"},
            {"name": "Nkomazana Investments", "reg_number": "BN2023/202177"},
            {"name": "Baby Beans", "reg_number": "BN2019/11643"},
        ]
    
    def generate_company_profile(self, company_data):
        """Generate XML profile for a company"""
        root = ET.Element("company_profile")
        
        # Basic Information
        basic = ET.SubElement(root, "basic_information")
        ET.SubElement(basic, "company_name").text = company_data.get("name", "")
        ET.SubElement(basic, "registration_number").text = company_data.get("reg_number", "")
        ET.SubElement(basic, "entity_type").text = company_data.get("type", "Private Company")
        ET.SubElement(basic, "status").text = "Active"
        ET.SubElement(basic, "industry").text = company_data.get("industry", "Unknown")
        
        if "trading_as" in company_data:
            ET.SubElement(basic, "trading_as").text = company_data["trading_as"]
        
        ET.SubElement(basic, "last_updated").text = datetime.now().isoformat()
        
        # Compliance Status
        compliance = ET.SubElement(root, "compliance")
        annual_return = ET.SubElement(compliance, "annual_return")
        
        # Check if urgent
        is_urgent = any(c["reg_number"] == company_data.get("reg_number") for c in self.urgent_annual_returns)
        if is_urgent:
            ET.SubElement(annual_return, "status").text = "URGENT - DUE 2026-02-28"
            ET.SubElement(annual_return, "due_date").text = "2026-02-28"
            ET.SubElement(annual_return, "action_required").text = "File annual return immediately"
        else:
            ET.SubElement(annual_return, "status").text = "Pending"
        
        # Cross References
        cross_ref = ET.SubElement(root, "cross_references")
        fa = ET.SubElement(cross_ref, "frontaccounting")
        ET.SubElement(fa, "mapped").text = "true" if company_data.get("reg_number") else "false"
        
        return root
    
    def generate_all_profiles(self):
        """Generate profiles for all companies"""
        for i, company in enumerate(self.companies, 1):
            profile = self.generate_company_profile(company)
            
            # Format filename
            reg_num = company.get("reg_number", f"UNKNOWN_{i:03d}").replace("/", "_")
            safe_name = "".join(c for c in company["name"][:20] if c.isalnum() or c in (' ', '-', '_')).strip()
            filename = f"{i:03d}_{reg_num}_{safe_name}.xml"
            
            filepath = self.companies_path / filename
            
            tree = ET.ElementTree(profile)
            tree.write(filepath, encoding="UTF-8", xml_declaration=True)
            
            print(f"✓ Created: {filename}")
    
    def generate_business_name_profiles(self):
        """Generate profiles for business names"""
        for bn in self.business_names:
            root = ET.Element("business_name_profile")
            
            ET.SubElement(root, "name").text = bn["name"]
            ET.SubElement(root, "registration_number").text = bn["reg_number"]
            ET.SubElement(root, "status").text = "Active"
            ET.SubElement(root, "last_updated").text = datetime.now().isoformat()
            
            safe_name = "".join(c for c in bn["name"][:20] if c.isalnum() or c in (' ', '-', '_')).strip()
            filename = f"BN_{bn['reg_number'].replace('/', '_')}_{safe_name}.xml"
            
            filepath = self.business_names_path / filename
            
            tree = ET.ElementTree(root)
            tree.write(filepath, encoding="UTF-8", xml_declaration=True)
            
            print(f"✓ Created BN: {filename}")
    
    def generate_compliance_report(self):
        """Generate compliance summary report"""
        root = ET.Element("compliance_report")
        
        ET.SubElement(root, "generated_date").text = datetime.now().isoformat()
        ET.SubElement(root, "total_companies").text = str(len(self.companies))
        ET.SubElement(root, "total_business_names").text = str(len(self.business_names))
        
        # Urgent items
        urgent = ET.SubElement(root, "urgent_items")
        ET.SubElement(urgent, "count").text = str(len(self.urgent_annual_returns))
        ET.SubElement(urgent, "deadline").text = "2026-02-28"
        
        for company in self.urgent_annual_returns:
            item = ET.SubElement(urgent, "company")
            ET.SubElement(item, "name").text = company["name"]
            ET.SubElement(item, "reg_number").text = company["reg_number"]
            ET.SubElement(item, "due_date").text = company["due_date"]
            ET.SubElement(item, "action").text = "File annual return"
        
        filepath = self.reports_path / "compliance_summary.xml"
        tree = ET.ElementTree(root)
        tree.write(filepath, encoding="UTF-8", xml_declaration=True)
        
        print(f"\n✓ Compliance report: {filepath}")
    
    def run(self):
        """Run the database generator"""
        print("=" * 60)
        print("CIPA Company Database Generator")
        print("=" * 60)
        print(f"\nCompanies: {len(self.companies)}")
        print(f"Business Names: {len(self.business_names)}")
        print(f"Urgent Items: {len(self.urgent_annual_returns)}")
        print("\n" + "-" * 60)
        
        self.generate_all_profiles()
        print("\n" + "-" * 60)
        
        self.generate_business_name_profiles()
        print("\n" + "-" * 60)
        
        self.generate_compliance_report()
        
        print("\n" + "=" * 60)
        print("Database generation complete!")
        print("=" * 60)

if __name__ == "__main__":
    db = CIPACompanyDatabase()
    db.run()

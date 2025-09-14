#!/usr/bin/env python3
"""Analyze Lead411 CSV exports to understand data structure and quality."""

import pandas as pd
import sys
import json
from pathlib import Path

def analyze_csv(file_path):
    """Analyze CSV file structure and data quality."""
    
    print(f"Analyzing Lead411 CSV: {file_path}")
    print("=" * 60)
    
    try:
        # Read CSV file
        df = pd.read_csv(file_path)
        
        # Basic statistics
        print(f"📊 BASIC STATISTICS")
        print(f"   Records: {len(df)}")
        print(f"   Columns: {len(df.columns)}")
        print()
        
        # Column analysis
        print(f"📋 COLUMN STRUCTURE")
        for i, col in enumerate(df.columns, 1):
            non_null = df[col].notna().sum()
            null_pct = ((len(df) - non_null) / len(df)) * 100
            sample_val = df[col].dropna().iloc[0] if non_null > 0 else "NO DATA"
            print(f"   {i:2d}. {col}")
            print(f"       Complete: {non_null}/{len(df)} ({100-null_pct:.1f}%)")
            print(f"       Sample: {str(sample_val)[:50]}...")
        print()
        
        # Data quality checks
        print(f"🔍 DATA QUALITY ANALYSIS")
        
        # Check for key columns
        key_columns = {
            'company': ['company', 'company_name', 'organization', 'account'],
            'website': ['website', 'url', 'domain'],
            'industry': ['industry', 'sector', 'vertical'],
            'intent_score': ['intent_score', 'bombora_score', 'score'],
            'intent_topics': ['intent_topics', 'topics', 'keywords'],
            'email': ['email', 'contact_email', 'primary_email'],
            'phone': ['phone', 'telephone', 'contact_phone'],
            'name': ['name', 'contact_name', 'first_name', 'last_name']
        }
        
        found_columns = {}
        for key, possible_names in key_columns.items():
            matches = [col for col in df.columns if any(name.lower() in col.lower() for name in possible_names)]
            if matches:
                found_columns[key] = matches[0]
                completeness = (df[matches[0]].notna().sum() / len(df)) * 100
                print(f"   ✅ {key.upper()}: Found '{matches[0]}' ({completeness:.1f}% complete)")
            else:
                print(f"   ❌ {key.upper()}: Not found")
        
        print()
        
        # Intent score analysis if available
        if 'intent_score' in found_columns:
            score_col = found_columns['intent_score']
            scores = pd.to_numeric(df[score_col], errors='coerce')
            print(f"📈 INTENT SCORE ANALYSIS")
            print(f"   Min Score: {scores.min()}")
            print(f"   Max Score: {scores.max()}")
            print(f"   Average: {scores.mean():.1f}")
            print(f"   Records 70+: {(scores >= 70).sum()}/{len(scores)} ({(scores >= 70).mean()*100:.1f}%)")
            print(f"   Records 80+: {(scores >= 80).sum()}/{len(scores)} ({(scores >= 80).mean()*100:.1f}%)")
            print(f"   Records 90+: {(scores >= 90).sum()}/{len(scores)} ({(scores >= 90).mean()*100:.1f}%)")
            print()
        
        # Company size analysis if available
        size_columns = [col for col in df.columns if any(term in col.lower() for term in ['employee', 'size', 'headcount'])]
        if size_columns:
            size_col = size_columns[0]
            sizes = pd.to_numeric(df[size_col], errors='coerce')
            print(f"🏢 COMPANY SIZE ANALYSIS")
            print(f"   Column: {size_col}")
            print(f"   Min Size: {sizes.min()}")
            print(f"   Max Size: {sizes.max()}")
            print(f"   Average: {sizes.mean():.0f}")
            print(f"   Companies 50+: {(sizes >= 50).sum()}/{len(sizes)} ({(sizes >= 50).mean()*100:.1f}%)")
            print(f"   Companies 100+: {(sizes >= 100).sum()}/{len(sizes)} ({(sizes >= 100).mean()*100:.1f}%)")
            print()
        
        # Sample records
        print(f"📄 SAMPLE RECORDS (First 3)")
        for i in range(min(3, len(df))):
            print(f"   Record {i+1}:")
            for key, col in found_columns.items():
                value = str(df[col].iloc[i]) if pd.notna(df[col].iloc[i]) else "N/A"
                print(f"     {key}: {value[:50]}...")
            print()
        
        # Recommendations
        print(f"💡 RECOMMENDATIONS FOR MAKE.COM INTEGRATION")
        print(f"   1. Primary company identifier: {found_columns.get('company', 'NEED TO IDENTIFY')}")
        print(f"   2. Intent scoring field: {found_columns.get('intent_score', 'NEED TO IDENTIFY')}")
        print(f"   3. Contact enrichment needed: {'No' if 'email' in found_columns else 'Yes'}")
        print(f"   4. Data quality: {'Good' if len(found_columns) >= 4 else 'Needs improvement'}")
        
        # Export field mapping for Make.com
        field_mapping = {
            "csv_column": "make_variable",
            "description": "usage_notes"
        }
        for key, col in found_columns.items():
            field_mapping[col] = f"lead_{key}"
        
        mapping_file = file_path.replace('.csv', '_field_mapping.json')
        with open(mapping_file, 'w') as f:
            json.dump(field_mapping, f, indent=2)
        print(f"   📁 Field mapping saved to: {mapping_file}")
        
    except Exception as e:
        print(f"Error analyzing CSV: {e}")
        return False
    
    return True

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python csv_analyzer.py <path_to_csv_file>")
        print("Example: python csv_analyzer.py ../imports/leads_20240904_0900.csv")
        sys.exit(1)
    
    csv_file = sys.argv[1]
    if not Path(csv_file).exists():
        print(f"File not found: {csv_file}")
        sys.exit(1)
    
    success = analyze_csv(csv_file)
    if success:
        print("\n✅ Analysis complete! Review the recommendations above.")
    else:
        print("\n❌ Analysis failed. Check the file format and try again.")
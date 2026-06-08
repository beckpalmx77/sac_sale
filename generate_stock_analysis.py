import pandas as pd
import numpy as np
import os

def analyze_stock(file_path, output_path):
    print("Loading Excel file...")
    xl = pd.ExcelFile(file_path)
    
    # Load raw sheets
    df_sales = xl.parse('DATA UPDATE')
    df_stock = xl.parse('UPDATE STOCK')
    df_products = xl.parse('UPDATE รหัสสินค้า')
    
    # Standardize spaces in branch names & month names to avoid matching issues
    df_sales['เดือน'] = df_sales['เดือน'].str.strip()
    df_sales['สาขา'] = df_sales['สาขา'].astype(str).str.strip()
    
    df_stock['สาขา'] = df_stock['สาขา'].astype(str).str.strip()
    df_stock['รหัสสินค้า'] = df_stock['รหัสสินค้า'].astype(str).str.strip()
    
    df_products['รหัสสินค้า'] = df_products['รหัสสินค้า'].astype(str).str.strip()
    
    # Define Months
    months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน']
    avg_months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม'] # Jan to May for MAX/MIN/AVG
    
    # Prepare result lists
    result_rows = []
    
    print("Processing products...")
    for idx, row in df_products.iterrows():
        p_code = row['รหัสสินค้า']
        p_desc = row['รายละเอียดสินค้า']
        
        # 1. Calculate Monthly Sales
        monthly_qty = {}
        for m in months:
            # Sum quantity for this product and month
            qty = df_sales[(df_sales['รหัสสินค้า'] == p_code) & (df_sales['เดือน'] == m)]['จำนวน'].sum()
            monthly_qty[m] = qty
            
        total_sales = sum(monthly_qty.values())
        
        # 2. Get Total Stock
        total_stock = df_stock[df_stock['รหัสสินค้า'] == p_code]['จำนวน'].sum()
        
        # 3. Calculate MAX, MIN, AVG (Jan to May)
        jan_may_sales = [monthly_qty[m] for m in avg_months]
        max_sales = max(jan_may_sales) if jan_may_sales else 0
        min_sales = min(jan_may_sales) if jan_may_sales else 0
        avg_sales = np.mean(jan_may_sales) if jan_may_sales else 0.0
        
        # 4. Stock addition needed
        needed_addition = avg_sales - total_stock
        
        # 5. Sales per Branch (Jan to Jun)
        # Branches: 340, ราชพฤกษ, บางใหญ่, บางบอน
        branches = ['340', 'ราชพฤกษ', 'บางใหญ่', 'บางบอน']
        branch_sales = {}
        for b in branches:
            # Program corrects the Excel bug here by matching against product code (col E) rather than customer code (col D)
            b_qty = df_sales[(df_sales['รหัสสินค้า'] == p_code) & (df_sales['สาขา'] == b)]['จำนวน'].sum()
            branch_sales[b] = b_qty
            
        # 6. Stock per Branch
        branch_stock = {}
        for b in branches:
            b_stk = df_stock[(df_stock['รหัสสินค้า'] == p_code) & (df_stock['สาขา'] == b)]['จำนวน'].sum()
            branch_stock[b] = b_stk
            
        # 7. Additional Stock Needed per Branch (Optional - W to Z columns, empty in original Excel)
        # We can calculate it as: (Branch Average Sales Jan-May) - Branch Stock
        branch_needed = {}
        for b in branches:
            # Calculate branch average sales Jan-May
            b_jan_may_sales = []
            for m in avg_months:
                b_m_qty = df_sales[(df_sales['รหัสสินค้า'] == p_code) & 
                                   (df_sales['เดือน'] == m) & 
                                   (df_sales['สาขา'] == b)]['จำนวน'].sum()
                b_jan_may_sales.append(b_m_qty)
            b_avg_sales = np.mean(b_jan_may_sales) if b_jan_may_sales else 0.0
            
            # Stock addition needed for this branch
            branch_needed[b] = b_avg_sales - branch_stock[b]
            
        # Combine data into a single row dictionary
        res_row = {
            'รหัสสินค้า': p_code,
            'รายละเอียดสินค้า': p_desc,
            'มกราคม': monthly_qty['มกราคม'],
            'กุมภาพันธ์': monthly_qty['กุมภาพันธ์'],
            'มีนาคม': monthly_qty['มีนาคม'],
            'เมษายน': monthly_qty['เมษายน'],
            'พฤษภาคม': monthly_qty['พฤษภาคม'],
            'มิถุนายน': monthly_qty['มิถุนายน'],
            'ผลรวมทั้งหมด': total_sales,
            'STOCK': total_stock,
            'MAX': max_sales,
            'MIN': min_sales,
            'AVG': round(avg_sales, 2),
            'ส่งไปเพิ่ม': round(needed_addition, 2),
            # Sales per branch
            'ยอดขาย_340': branch_sales['340'],
            'ยอดขาย_ราชพฤกษ': branch_sales['ราชพฤกษ'],
            'ยอดขาย_บางใหญ่': branch_sales['บางใหญ่'],
            'ยอดขาย_บางบอน': branch_sales['บางบอน'],
            # Stock per branch
            'STOCK_340': branch_stock['340'],
            'STOCK_ราชพฤกษ': branch_stock['ราชพฤกษ'],
            'STOCK_บางใหญ่': branch_stock['บางใหญ่'],
            'STOCK_บางบอน': branch_stock['บางบอน'],
            # Needed per branch (Option to fill columns W to Z)
            'ต้องการเพิ่ม_340': round(branch_needed['340'], 2),
            'ต้องการเพิ่ม_ราชพฤกษ': round(branch_needed['ราชพฤกษ'], 2),
            'ต้องการเพิ่ม_บางใหญ่': round(branch_needed['บางใหญ่'], 2),
            'ต้องการเพิ่ม_บางบอน': round(branch_needed['บางบอน'], 2),
        }
        result_rows.append(res_row)
        
    df_result = pd.DataFrame(result_rows)
    
    # Save to Excel
    print(f"Saving results to {output_path}...")
    
    # We will write it to a clean Excel file
    with pd.ExcelWriter(output_path, engine='openpyxl') as writer:
        df_result.to_excel(writer, sheet_name='วิเคราะห์ข้อมูล (Program)', index=False)
        
    print("Done!")

if __name__ == "__main__":
    base_dir = os.path.dirname(os.path.abspath(__file__))
    input_xlsx = os.path.join(base_dir, "Part-STOCK.xlsx")
    output_xlsx = os.path.join(base_dir, "Part-STOCK-Analyzed.xlsx")
    
    analyze_stock(input_xlsx, output_xlsx)

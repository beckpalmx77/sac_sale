# รายงานการวิเคราะห์และแนวทางการเขียนโปรแกรมสำหรับไฟล์ Part-STOCK.xlsx

รายงานฉบับนี้สรุปโครงสร้างของไฟล์ Part-STOCK.xlsx และขั้นตอน/สูตรคำนวณที่ใช้ในชีตแรก (**วิเคราะห์ข้อมูล**) พร้อมทั้งแนวทางการเขียนโปรแกรม (ด้วย **Python/Pandas** และ **PHP/PhpSpreadsheet**) เพื่อให้ได้ผลลัพธ์การวิเคราะห์ที่ถูกต้องและเป็นระบบ

---

## 1. โครงสร้างชีตข้อมูลในไฟล์ Excel

ไฟล์ `Part-STOCK.xlsx` ประกอบด้วย 5 ชีตหลัก ได้แก่:
1. **วิเคราะห์ข้อมูล** (ชีตแรก): ชีตผลลัพธ์ที่เป็นเป้าหมาย ประกอบด้วยการสรุปยอดขายรายเดือน, สต็อกทั้งหมด, สถิติ (MAX, MIN, AVG), คำนวณความต้องการสินค้าเพิ่มเติม, และข้อมูลแยกรายสาขา
2. **รายการขายออกของแต่ละช่วง**: ตาราง Pivot Table สรุปยอดขายแยกรายสินค้าและสาขาของแต่ละเดือน (เช่น เดือนมิถุนายน)
3. **DATA UPDATE**: ข้อมูลการทำธุรกรรมขาย (Sales Transactions) แบบดิบ มีข้อมูลรหัสสินค้า, วันที่, เดือน, ปี, จำนวนที่ขาย, และสาขาที่ขาย
4. **UPDATE STOCK**: ข้อมูลยอดคงเหลือสต็อกสินค้า (Stock Balance) แบบดิบ แยกตามคลังสินค้าและสาขา
5. **UPDATE รหัสสินค้า**: รายการรหัสสินค้าและรายละเอียดสินค้าหลัก (Product Master) จำนวน 1,289 รายการ

> **หมายเหตุ**: จากการตรวจสอบพบว่าจำนวนสินค้าในชีตหลัก **UPDATE รหัสสินค้า** (1,289 รายการ) มีจำนวนเท่ากับจำนวนสินค้าที่มีการขายในชีต **DATA UPDATE** (1,289 รายการ) และตรงกับจำนวนแถวสินค้าในชีตผลลัพธ์ **วิเคราะห์ข้อมูล** พอดี ดังนั้น โปรแกรมจะใช้รายการสินค้าจากชีต **UPDATE รหัสสินค้า** เป็นฐานข้อมูลหลักในการทำงาน

---

## 2. การวิเคราะห์สูตรคำนวณในชีตเป้าหมาย ("วิเคราะห์ข้อมูล")

การคำนวณในชีตแรกมีสูตรแยกตามคอลัมน์ ดังนี้ (อ้างอิงข้อมูลแถวที่ 6 ซึ่งเป็นสินค้าชิ้นแรก `019-I5-1`):

| คอลัมน์ | หัวข้อหลัก | หัวข้อย่อย / ค่าอ้างอิง | สูตรใน Excel | รายละเอียดการคำนวณ |
| :--- | :--- | :--- | :--- | :--- |
| **A** | - | รหัสสินค้า | *ข้อมูลดิบ* | ดึงมาจากชีต `UPDATE รหัสสินค้า` |
| **B** | - | รายละเอียดสินค้า | *ข้อมูลดิบ* | ดึงมาจากชีต `UPDATE รหัสสินค้า` |
| **C - H** | เดือน | มกราคม - มิถุนายน | `=SUMIFS('DATA UPDATE'!$L:$L, 'DATA UPDATE'!$E:$E, A6, 'DATA UPDATE'!$B:$B, $C$2)` | หาผลรวมจำนวนขาย (คอลัมน์ L ใน `DATA UPDATE`) โดยจับคู่รหัสสินค้า และเดือนที่ขายตามแถวที่ 2 |
| **I** | ผลรวมทั้งหมด | - | `=SUM(C6:H6)` | ผลรวมยอดขายรวม 6 เดือน (ม.ค. - มิ.ย.) |
| **J** | STOCK | - | `=SUMIFS('UPDATE STOCK'!F:F, 'UPDATE STOCK'!A:A, A6)` | ผลรวมสต็อกคงเหลือปัจจุบันของสินค้านั้นจากทุกคลัง/สาขาในชีต `UPDATE STOCK` |
| **K** | MAX | - | `=MAX(C6:G6)` | **ยอดขายสูงสุดรายเดือน** (คำนวณเฉพาะช่วง ม.ค. - พ.ค.) *ไม่รวมเดือน มิ.ย.* |
| **L** | MIN | - | `=MIN(C6:G6)` | **ยอดขายต่ำสุดรายเดือน** (คำนวณเฉพาะช่วง ม.ค. - พ.ค.) *ไม่รวมเดือน มิ.ย.* |
| **M** | AVG | - | `=AVERAGE(C6:G6)` | **ยอดขายเฉลี่ยรายเดือน** (คำนวณเฉพาะช่วง ม.ค. - พ.ค.) *ไม่รวมเดือน มิ.ย.* |
| **N** | สินค้าที่ต้อง | ส่งไปเพิ่ม | `=M6-J6` | คำนวณจาก `ค่าเฉลี่ยยอดขาย (AVG) - สต็อกปัจจุบัน (STOCK)`<br>• ค่าเป็น**บวก**: ยอดขายเฉลี่ยสูงกว่าสต็อก (ต้องส่งเพิ่ม)<br>• ค่าเป็น**ลบ**: สต็อกมีเพียงพอแล้ว |
| **O - R** | ยอดขายรายสาขา | 340, ราชพฤกษ, บางใหญ่, บางบอน | `=SUMIFS('DATA UPDATE'!$L:$L, 'DATA UPDATE'!$E:$E, A6, 'DATA UPDATE'!$T:$T, $O$2)` | ยอดขายรวมของสินค้านั้นแยกตามสาขา (ม.ค. - มิ.ย.) |
| **S - V** | STOCK รายสาขา | 340, ราชพฤกษ, บางใหญ่, บางบอน | `=SUMIFS('UPDATE STOCK'!$F:$F, 'UPDATE STOCK'!$A:$A, A6, 'UPDATE STOCK'!$G:$G, $S$2)` | จำนวนสต็อกคงเหลือแยกรายสาขาในชีต `UPDATE STOCK` |
| **W - Z** | ความต้องการเพิ่ม | 340, ราชพฤกษ, บางใหญ่, บางบอน | *ไม่มีสูตร (ว่างเปล่า)* | ในตาราง Excel เดิมไม่ได้ใส่สูตรไว้ แต่สามารถเขียนโปรแกรมคำนวณเพิ่มเติมให้ได้ |

---

## 3. ข้อผิดพลาดสำคัญที่พบในสูตร Excel เดิม (Critical Bug!)

จากการแกะสูตรจริงในไฟล์ Excel พบข้อผิดพลาดร้ายแรงในคอลัมน์ **R** (ยอดขายสาขา **บางบอน**):
* **สูตรคอลัมน์ R เดิม**: `=SUMIFS('DATA UPDATE'!$L:$L, 'DATA UPDATE'!$D:$D, A6, 'DATA UPDATE'!$T:$T, $R$2)`
* **วิเคราะห์ข้อผิดพลาด**: ตารางสูตรไปจับคู่รหัสสินค้ากับคอลัมน์ **`$D:$D`** ของชีต `DATA UPDATE` ซึ่งคอลัมน์ D คือ **รหัสลูกค้า (Customer Code)** ไม่ใช่รหัสสินค้า (Product Code) ซึ่งอยู่ในคอลัมน์ **`$E:$E`** เหมือนคอลัมน์สาขาอื่น ๆ
* **ผลกระทบ**: ยอดขายของสาขา **บางบอน** ในหน้าสรุปผลลัพธ์จึงเป็น **`0` เสมอสำหรับทุกรายการสินค้า** ทำให้สรุปยอดขายผิดพลาด
* **ตัวอย่าง**: สินค้า `155-1-12` มียอดขายจริงที่สาขาบางบอนจำนวน **102 ชิ้น** แต่ในตารางวิเคราะห์กลับแสดงผลเป็น **0**

> **วิธีแก้ไข**: โปรแกรมที่เราเขียนขึ้นมาใหม่จะปรับแก้ไขบั๊กนี้โดยอัตโนมัติ โดยเปลี่ยนไปจับคู่กับคอลัมน์ **`$E:$E`** (รหัสสินค้า) สำหรับสาขาบางบอน เพื่อให้ได้ค่าตามความจริง

---

## 4. แนวทางการเขียนโปรแกรม (Programming Solutions)

### วิธีที่ 1: การใช้ Python และ Pandas (แนะนำสำหรับการประมวลผลข้อมูลที่รวดเร็ว)
เราได้สร้างไฟล์สคริปต์ [generate_stock_analysis.py](file:///D:/website/sac_sale/generate_stock_analysis.py) ไว้ในโฟลเดอร์โครงการเรียบร้อยแล้ว โดยสคริปต์นี้จะ:
1. โหลดชีตข้อมูลดิบทั้งหมดเข้ามาในรูปของ DataFrame
2. ลบช่องว่างส่วนเกิน (Whitespace Strip) เพื่อความแม่นยำในการจอยข้อมูล
3. คำนวณยอดขายรายเดือน, ยอดขายรายสาขา (แก้ไขบั๊กบางบอน), สต็อกรวม และสต็อกรายสาขา
4. คำนวณค่าสถิติ MAX, MIN, AVG ยอดขาย (ม.ค. - พ.ค.)
5. คำนวณความต้องการเพิ่มเติมรายสาขา (Optional - คอลัมน์ W-Z)
6. บันทึกผลลัพธ์ลงไฟล์ Excel ตัวใหม่ชื่อ `Part-STOCK-Analyzed.xlsx`

**โค้ดตัวอย่างใน Python/Pandas:**
```python
import pandas as pd
import numpy as np

# โหลดไฟล์ Excel
xl = pd.ExcelFile('Part-STOCK.xlsx')
df_sales = xl.parse('DATA UPDATE')
df_stock = xl.parse('UPDATE STOCK')
df_products = xl.parse('UPDATE รหัสสินค้า')

# คลีนข้อมูลตัวอักษร
df_sales['เดือน'] = df_sales['เดือน'].str.strip()
df_sales['สาขา'] = df_sales['สาขา'].astype(str).str.strip()
df_stock['สาขา'] = df_stock['สาขา'].astype(str).str.strip()

months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน']
avg_months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม']
branches = ['340', 'ราชพฤกษ', 'บางใหญ่', 'บางบอน']

result_rows = []
for idx, row in df_products.iterrows():
    p_code = row['รหัสสินค้า']
    p_desc = row['รายละเอียดสินค้า']
    
    # 1. ยอดขายแต่ละเดือน
    monthly_sales = {m: df_sales[(df_sales['รหัสสินค้า'] == p_code) & (df_sales['เดือน'] == m)]['จำนวน'].sum() for m in months}
    total_sales = sum(monthly_sales.values())
    
    # 2. สต็อกรวม
    total_stock = df_stock[df_stock['รหัสสินค้า'] == p_code]['จำนวน'].sum()
    
    # 3. MAX, MIN, AVG (ม.ค. - พ.ค.)
    jan_may_sales = [monthly_sales[m] for m in avg_months]
    max_val = max(jan_may_sales) if jan_may_sales else 0
    min_val = min(jan_may_sales) if jan_may_sales else 0
    avg_val = np.mean(jan_may_sales) if jan_may_sales else 0
    needed_total = avg_val - total_stock
    
    # 4. ยอดขายรายสาขา (แก้ไขบั๊กคอลัมน์ R บางบอน โดยใช้รหัสสินค้าเป็นตัวกรอง)
    branch_s = {b: df_sales[(df_sales['รหัสสินค้า'] == p_code) & (df_sales['สาขา'] == b)]['จำนวน'].sum() for b in branches}
    
    # 5. สต็อกรายสาขา
    branch_stk = {b: df_stock[(df_stock['รหัสสินค้า'] == p_code) & (df_stock['สาขา'] == b)]['จำนวน'].sum() for b in branches}
    
    # 6. คำนวณความต้องการเพิ่มเติมรายสาขา (คอลัมน์ W - Z)
    # สูตร: เฉลี่ยขายของสาขานั้น (ม.ค. - พ.ค.) - สต็อกของสาขานั้น
    branch_need = {}
    for b in branches:
        b_avg = np.mean([df_sales[(df_sales['รหัสสินค้า'] == p_code) & (df_sales['เดือน'] == m) & (df_sales['สาขา'] == b)]['จำนวน'].sum() for m in avg_months])
        branch_need[b] = b_avg - branch_stk[b]
        
    result_rows.append({
        'รหัสสินค้า': p_code, 'รายละเอียดสินค้า': p_desc,
        **monthly_sales, 'ผลรวมทั้งหมด': total_sales, 'STOCK': total_stock,
        'MAX': max_val, 'MIN': min_val, 'AVG': round(avg_val, 2), 'ส่งไปเพิ่ม': round(needed_total, 2),
        **{f'ยอดขาย_{b}': branch_s[b] for b in branches},
        **{f'STOCK_{b}': branch_stk[b] for b in branches},
        **{f'ต้องการเพิ่ม_{b}': round(branch_need[b], 2) for b in branches}
    })

df_res = pd.DataFrame(result_rows)
df_res.to_excel('Part-STOCK-Analyzed.xlsx', index=False)
```

---

### วิธีที่ 2: การใช้ PHP และ PhpSpreadsheet (สำหรับระบบเว็บ sac_sale ของคุณ)
เนื่องจากระบบของคุณมีการใช้ PHP และลงไลบรารี `phpoffice/phpspreadsheet` อยู่ในไฟล์ `composer.json` คุณสามารถเขียนสคริปต์ PHP เพื่อรันผลสรุปข้อมูลนี้ได้โดยตรง:

```php
<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$inputPath = 'Part-STOCK.xlsx';
$outputPath = 'Part-STOCK-PHP-Analyzed.xlsx';

echo "Loading workbook...\n";
$spreadsheet = IOFactory::load($inputPath);

// อ่านแผ่นงานต่าง ๆ
$sheetSales = $spreadsheet->getSheetByName('DATA UPDATE')->toArray(null, true, true, true);
$sheetStock = $spreadsheet->getSheetByName('UPDATE STOCK')->toArray(null, true, true, true);
$sheetProducts = $spreadsheet->getSheetByName('UPDATE รหัสสินค้า')->toArray(null, true, true, true);

// 1. จัดเรียงข้อมูลขาย (Sales Grouping)
$salesData = [];
$months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน'];
$avgMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม'];
$branches = ['340', 'ราชพฤกษ', 'บางใหญ่', 'บางบอน'];

for ($i = 2; $i <= count($sheetSales); $i++) {
    $pCode = trim($sheetSales[$i]['E']); // รหัสสินค้า
    $month = trim($sheetSales[$i]['B']); // เดือน
    $qty = floatval($sheetSales[$i]['L']); // จำนวน
    $branch = trim($sheetSales[$i]['T']); // สาขา
    
    if (!isset($salesData[$pCode])) {
        $salesData[$pCode] = [
            'months' => array_fill_keys($months, 0),
            'branches' => array_fill_keys($branches, 0),
            'branch_months' => []
        ];
        foreach ($branches as $b) {
            $salesData[$pCode]['branch_months'][$b] = array_fill_keys($months, 0);
        }
    }
    
    if (in_array($month, $months)) {
        $salesData[$pCode]['months'][$month] += $qty;
    }
    if (in_array($branch, $branches)) {
        $salesData[$pCode]['branches'][$branch] += $qty;
        if (in_array($month, $months)) {
            $salesData[$pCode]['branch_months'][$branch][$month] += $qty;
        }
    }
}

// 2. จัดเรียงข้อมูลสต็อก (Stock Grouping)
$stockData = [];
for ($i = 2; $i <= count($sheetStock); $i++) {
    $pCode = trim($sheetStock[$i]['A']);
    $qty = floatval($sheetStock[$i]['F']);
    $branch = trim($sheetStock[$i]['G']);
    
    if (!isset($stockData[$pCode])) {
        $stockData[$pCode] = [
            'total' => 0,
            'branches' => array_fill_keys($branches, 0)
        ];
    }
    $stockData[$pCode]['total'] += $qty;
    if (in_array($branch, $branches)) {
        $stockData[$pCode]['branches'][$branch] += $qty;
    }
}

// 3. สร้างตารางผลลัพธ์สรุปข้อมูล
$outSpreadsheet = new Spreadsheet();
$outSheet = $outSpreadsheet->getActiveSheet();
$outSheet->setTitle('วิเคราะห์ข้อมูล');

// เขียนหัวตารางหลักและย่อย
$outSheet->setCellValue('A1', 'ผลรวม ของ จำนวน');
$outSheet->setCellValue('C1', 'เดือน');
$outSheet->setCellValue('J1', 'STOCK');
$outSheet->setCellValue('K1', 'MAX');
$outSheet->setCellValue('L1', 'MIN');
$outSheet->setCellValue('M1', 'AVG');
$outSheet->setCellValue('N1', 'สินค้าที่ต้องส่งเพิ่ม');
$outSheet->setCellValue('O1', 'ยอดขายรายสาขา');
$outSheet->setCellValue('S1', 'STOCK รายสาขา');
$outSheet->setCellValue('W1', 'ความต้องการเพิ่มรายสาขา');

$outSheet->setCellValue('A2', 'รหัสสินค้า');
$outSheet->setCellValue('B2', 'รายละเอียดสินค้า');
$outSheet->setCellValue('C2', 'มกราคม');
$outSheet->setCellValue('D2', 'กุมภาพันธ์');
$outSheet->setCellValue('E2', 'มีนาคม');
$outSheet->setCellValue('F2', 'เมษายน');
$outSheet->setCellValue('G2', 'พฤษภาคม');
$outSheet->setCellValue('H2', 'มิถุนายน');
$outSheet->setCellValue('I2', 'ผลรวมทั้งหมด');

$branchHeaders = ['O' => '340', 'P' => 'ราชพฤกษ', 'Q' => 'บางใหญ่', 'R' => 'บางบอน',
                  'S' => '340', 'T' => 'ราชพฤกษ', 'U' => 'บางใหญ่', 'V' => 'บางบอน',
                  'W' => '340', 'X' => 'ราชพฤกษ', 'Y' => 'บางใหญ่', 'Z' => 'บางบอน'];
foreach ($branchHeaders as $col => $bName) {
    $outSheet->setCellValue($col . '2', $bName);
}

// 4. วนลูปรายสินค้าเขียนข้อมูล
$rowNum = 3; 
for ($i = 2; $i <= count($sheetProducts); $i++) {
    $pCode = trim($sheetProducts[$i]['A']);
    $pDesc = trim($sheetProducts[$i]['B']);
    if (empty($pCode)) continue;
    
    $outSheet->setCellValue('A' . $rowNum, $pCode);
    $outSheet->setCellValue('B' . $rowNum, $pDesc);
    
    // ยอดขายรายเดือน
    $mSales = isset($salesData[$pCode]) ? $salesData[$pCode]['months'] : array_fill_keys($months, 0);
    $outSheet->setCellValue('C' . $rowNum, $mSales['มกราคม']);
    $outSheet->setCellValue('D' . $rowNum, $mSales['กุมภาพันธ์']);
    $outSheet->setCellValue('E' . $rowNum, $mSales['มีนาคม']);
    $outSheet->setCellValue('F' . $rowNum, $mSales['เมษายน']);
    $outSheet->setCellValue('G' . $rowNum, $mSales['พฤษภาคม']);
    $outSheet->setCellValue('H' . $rowNum, $mSales['มิถุนายน']);
    
    $totalSales = array_sum($mSales);
    $outSheet->setCellValue('I' . $rowNum, $totalSales);
    
    // สต็อก
    $stockTotal = isset($stockData[$pCode]) ? $stockData[$pCode]['total'] : 0;
    $outSheet->setCellValue('J' . $rowNum, $stockTotal);
    
    // สถิติ (ม.ค. - พ.ค.)
    $janMaySales = array_intersect_key($mSales, array_flip($avgMonths));
    $maxVal = count($janMaySales) ? max($janMaySales) : 0;
    $minVal = count($janMaySales) ? min($janMaySales) : 0;
    $avgVal = count($janMaySales) ? (array_sum($janMaySales) / count($janMaySales)) : 0;
    
    $outSheet->setCellValue('K' . $rowNum, $maxVal);
    $outSheet->setCellValue('L' . $rowNum, $minVal);
    $outSheet->setCellValue('M' . $rowNum, round($avgVal, 2));
    
    // ส่งไปเพิ่ม
    $outSheet->setCellValue('N' . $rowNum, round($avgVal - $stockTotal, 2));
    
    // ยอดขายสาขา และ สต็อกสาขา
    $bSales = isset($salesData[$pCode]) ? $salesData[$pCode]['branches'] : array_fill_keys($branches, 0);
    $bStock = isset($stockData[$pCode]) ? $stockData[$pCode]['branches'] : array_fill_keys($branches, 0);
    
    $outSheet->setCellValue('O' . $rowNum, $bSales['340']);
    $outSheet->setCellValue('P' . $rowNum, $bSales['ราชพฤกษ']);
    $outSheet->setCellValue('Q' . $rowNum, $bSales['บางใหญ่']);
    $outSheet->setCellValue('R' . $rowNum, $bSales['บางบอน']); // แก้บั๊กที่นี่
    
    $outSheet->setCellValue('S' . $rowNum, $bStock['340']);
    $outSheet->setCellValue('T' . $rowNum, $bStock['ราชพฤกษ']);
    $outSheet->setCellValue('U' . $rowNum, $bStock['บางใหญ่']);
    $outSheet->setCellValue('V' . $rowNum, $bStock['บางบอน']);
    
    // ความต้องการเพิ่มรายสาขา
    foreach ($branches as $idx => $b) {
        $colName = chr(ord('W') + $idx);
        $bJanMaySales = [];
        foreach ($avgMonths as $m) {
            $bJanMaySales[] = isset($salesData[$pCode]['branch_months'][$b][$m]) ? $salesData[$pCode]['branch_months'][$b][$m] : 0;
        }
        $bAvgSales = array_sum($bJanMaySales) / count($bJanMaySales);
        $bNeeded = $bAvgSales - $bStock[$b];
        $outSheet->setCellValue($colName . $rowNum, round($bNeeded, 2));
    }
    
    $rowNum++;
}

echo "Saving spreadsheet...\n";
$writer = IOFactory::createWriter($outSpreadsheet, 'Xlsx');
$writer->save($outputPath);
echo "Completed!\n";
```

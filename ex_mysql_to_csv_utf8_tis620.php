<?php
// ตัวอย่างการ SELECT ข้อมูลจาก TABLE ที่ใช้ "UTF-8" แล้วส่งออกเป็น CSV ที่ใช้ "TIS-620"
// TIS-620 มักเข้ากันได้กับภาษาไทย ที่ใช้ในซอฟต์แวร์งานเอกสารเช่น MSOFFICE 
// หรือซอฟต์แวร์ธุรกิจทั่วไปที่พัฒนาด้วย VB, VFP 

// การเชื่อมต่อฐานข้อมูล (UTF-8)
$conn = new mysqli("localhost", "user", "pass", "dbname");
$conn->set_charset("utf8");

// query
$sql = "SELECT id, name, address FROM customers";
$result = $conn->query($sql);

// กำหนด header สำหรับดาวน์โหลดไฟล์ CSV
header("Content-Type: text/csv; charset=TIS-620");
header("Content-Disposition: attachment; filename=\"customers.csv\"");

// สร้าง output stream
$output = fopen("php://output", "w");

// เขียน header (optional)
$header = ['ID', 'ชื่อ', 'ที่อยู่'];
$header_tis620 = array_map(function($val) {
	return iconv("UTF-8", "TIS-620//IGNORE", $val);
}, $header);
fputcsv($output, $header_tis620);

// เขียนข้อมูลทีละแถวโดยใช้ iconv ช่วยแปลง และใช้ //IGNORE หรือ //TRANSLIT ตามความเหมาะสม
//IGNORE → ตัวที่แปลงไม่ได้จะถูกตัดทิ้ง (หายไป)
//TRANSLIT → ตัวที่แปลงไม่ได้จะพยายามแทนด้วยอะไรบางอย่าง (? เป็นส่วนใหญ่)
while($row = $result->fetch_assoc()) {
	$row_tis620 = array_map(function($val) {
		return iconv("UTF-8", "TIS-620//IGNORE", $val);
    }, $row);
    fputcsv($output, $row_tis620);
}

// ปิดไฟล์ และการเชื่อมต่อฐานข้อมูล
fclose($output);
$conn->close();
?>

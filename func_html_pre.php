<?php
/**
 * แปลง array เป็น string โดยใช้เครื่องหมายเชื่อม key-value และตัวคั่นระหว่างแต่ละคู่
 * 
 * @param array $ar_param อาร์เรย์ที่ต้องการแปลงเป็น string
 * @param string $str_link เครื่องหมายเชื่อมระหว่าง key และ value เช่น ":" สำหรับ style css หรือ "=" สำหรับ querystring
 * @param string $str_separate ตัวคั่นระหว่างแต่ละ key-value คู่ เช่น ";" สำหรับ style css หรือ "," สำหรับ CSV
 * 
 * @return string คืนค่าข้อความ string ของ array ที่แปลงจากอาร์เรย์ตามรูปแบบที่กำหนด
 * 
 * @example
 * <pre>
 * $style = [
 *   "padding" => "10px",
 *   "border" => "1px solid black",
 *   "margin" => "5px"
 * ];
 * 
 * $cssText = array_to_string_builder($style, ":", ";");
 * echo $cssText; // ผลลัพธ์: padding:10px;border:1px solid black;margin:5px;
 * 
 * $csvText = array_to_string_builder($style, "=", ",");
 * echo $csvText; // ผลลัพธ์: padding=10px,border=1px solid black,margin=5px,
 * </pre>
 */
function array_to_string_builder($ar_param, $str_link = ':', $str_separate = ';') {
    $string_value = '';
    foreach ($ar_param as $key => $value) {
        $string_value .= $key . $str_link . $value . $str_separate;
    }
    return $string_value;
}

/**
 * ครอบข้อความด้วย tag <pre> และเพิ่ม inline style CSS เพื่อให้แสดงผลในรูปแบบบล็อก
 * เหมาะสำหรับแสดงข้อมูล debug, array, หรือข้อความที่ต้องการให้อ่านง่าย
 * 
 * @param string $string_value ข้อความที่จะถูกครอบด้วย <pre> และถูกตกแต่งด้วย style
 * @return string คืนค่า HTML string ของข้อความที่ห่อด้วย <pre> และ style ที่กำหนด
 * 
 * @example
 * <pre>
 * echo html_pre("Hello World");
 * // ผลลัพธ์ HTML แสดงข้อความ "Hello World" ในบล็อกที่มีกรอบและ background สีเทาอ่อน
 * </pre>
 */
function html_pre($string_value = '') {
    $this_style = array(
        "padding" => "10px",
        "border-width" => "1px 1px 1px 5px",
        "border-style" => "solid",
        "border-color" => "rgb(128, 128, 128)",
        "margin" => "5px",
        "border-radius" => "7px",
        "background-color" => "rgba(128, 128, 128, 0.2)"
    );
    // แปลง array style เป็น string สำหรับ inline style CSS
    $styleString = array_to_string_builder($this_style, ":", ";");
    // สร้าง HTML string พร้อมครอบข้อความด้วย <pre>
    $html = "<pre style=\"" . $styleString . "\" >";
    $html .= $string_value;
    $html .= "</pre>";
    return $html;
}

// ตัวอย่างการใช้งาน
// แสดง array $_SERVER ในรูปแบบอ่านง่าย พร้อมตกแต่งด้วย style
#echo html_pre(print_r($_SERVER, true));

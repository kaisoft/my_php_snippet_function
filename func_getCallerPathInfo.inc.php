<?php
/**
 * ฟังก์ชัน getCallerPathInfo
 *
 * ประโยชน์: ใช้สำหรับดึงข้อมูลตำแหน่งไฟล์ที่เรียกฟังก์ชันนี้ (หรือระดับ stack trace ที่ระบุ)
 *  เพื่อช่วยในการ debug หรือเก็บข้อมูล context ของการเรียกใช้งาน เช่น Path, File, ชื่อโฟลเดอร์, และข้อมูล server ที่เกี่ยวข้อง
 *
 * วิธีใช้:
 *   - ส่ง parameter $level เป็นจำนวนเต็ม เพื่อเลือกระดับของ backtrace stack ที่ต้องการดู (0 = ฟังก์ชันนี้เอง, 1 = ฟังก์ชันที่เรียกมา, ...)
 *   - คืนค่าเป็น array ที่ประกอบด้วยข้อมูลเกี่ยวกับพาธไฟล์และ server
 *
 * @param int $level (optional) ระบุระดับของ backtrace stack (default = 0)
 * @return array ข้อมูลตำแหน่งไฟล์และ server ที่เกี่ยวข้อง
 */
function getCallerPathInfo($level = 0) {
    // ตรวจสอบให้แน่ใจว่า $level เป็น int และไม่ติดลบ
    if (!is_int($level) || $level < 0) {
        $level = 0;  // กรณีผิดพลาด ตั้งกลับเป็น 0
    }

    // ดึง debug backtrace โดยไม่คืนค่าพารามิเตอร์ argument
    // ทำให้ได้ข้อมูลแค่การเรียกฟังก์ชัน, ไฟล์, และบรรทัดที่เรียก
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

    // ตรวจสอบว่า $level มีอยู่ใน backtrace จริงหรือไม่
    if (!isset($trace[$level])) {
        $level = 0; // ถ้าไม่มีใช้ค่า 0 แทน
    }

    $traceInfo = $trace[$level];

    // อ่านไฟล์ที่ stack trace ชั้นนี้ชี้ไป ถ้าไม่มีให้เป็นค่าว่าง
    $filePath = isset($traceInfo['file']) ? $traceInfo['file'] : '';

    // ถ้าไม่มีไฟล์เลย (เช่น function internal) ให้ return ว่าง
    if (empty($filePath)) {
        return [];
    }

    // หาตำแหน่งโฟลเดอร์ของไฟล์นี้
    $dirPath = dirname($filePath);

    // แยกโฟลเดอร์เป็นแต่ละชั้นใน array แบนราบ
    // เช่น "/home/site/domains" => ['home','site','domains']
    $folders = explode(DIRECTORY_SEPARATOR, trim($dirPath, DIRECTORY_SEPARATOR));

    // เก็บโฟลเดอร์ชื่อแยกชั้นเป็น array แบนราบ ไม่ซ้อน array
    $folder_name = $folders;

    // รวมผลลัพธ์ทั้งหมดเป็น array เพื่อใช้ต่อ
    $result = [
        'dirname' => $dirPath,                                         // path ของโฟลเดอร์ที่ไฟล์อยู่
        'basename' => basename($filePath),                             // ชื่อไฟล์พร้อมนามสกุล
        'extension' => pathinfo($filePath, PATHINFO_EXTENSION),        // นามสกุลไฟล์
        'filename' => pathinfo($filePath, PATHINFO_FILENAME),          // ชื่อไฟล์ไม่รวมสกุล
        'document_root' => isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '',  // document root ของเว็บเซิร์ฟเวอร์
        'server_name' => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '',        // ชื่อเซิร์ฟเวอร์
        'server_addr' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',        // IP เซิร์ฟเวอร์
        'remote_addr' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',        // IP ไคลเอนต์
        'folder_name' => $folder_name,                                  // รายชื่อโฟลเดอร์ตามลำดับชั้น
    ];

    return $result;
}

<?php
/**
 * ระบบจัดการบันทึกข้อผิดพลาด (Error Logging) แบบแยกไฟล์ log ตามชื่อไฟล์ที่เกิด error หรือรวมไฟล์เดียว
 *
 * วิธีใช้งาน:
 * 1. ให้ include หรือ require ไฟล์นี้ในไฟล์ PHP ที่ต้องการจับ error เช่น
 *    include 'error_to_log.inc.php';
 * 2. ตั้งค่าพารามิเตอร์ต่าง ๆ เพื่อกำหนดรูปแบบการบันทึก log ตามต้องการ
 *
 * พารามิเตอร์ที่สำคัญ:
 * - $logToSingleFile (boolean) 
 *     กำหนดว่าจะบันทึก error log ลงในไฟล์เดียวคือ errors.log (true) 
 *     หรือจะแยกไฟล์แต่ละไฟล์ตามชื่อไฟล์ที่เกิด error เช่น index_error.log (false)
 *
 * - $singleLogFile (string)
 *     ชื่อไฟล์ log กรณีใช้บันทึกไฟล์เดียว (เมื่อ $logToSingleFile = true)
 *
 * - $maxLogAgeDays (int)
 *     จำนวนวันที่จะเก็บไฟล์ log ไว้ (หน่วยเป็นวัน)
 *     หากตั้งเป็น 0 จะไม่ลบไฟล์ log เก่าอัตโนมัติ
 *
 * ประโยชน์:
 * - แยกดูข้อผิดพลาดแต่ละไฟล์ได้ง่ายขึ้น ช่วยลดความยุ่งเหยิงของไฟล์ log รวม
 * - ระบบลบ log เก่าที่เกินระยะเวลาที่กำหนดช่วยลดปัญหาพื้นที่จัดเก็บเต็ม
 * - ปรับเปลี่ยนรูปแบบการบันทึกได้ตามต้องการ เพิ่มความยืดหยุ่นและเหมาะสมกับสภาพแวดล้อม
 *
 * หมายเหตุ:
 * - แนะนำปิดการแสดง error บนหน้าจอในสภาพแวดล้อม production เพื่อความปลอดภัยและความเป็นมืออาชีพ
 *
 * ตัวอย่างการใช้งาน:
 * ```
 * // แยกบันทึกไฟล์ log ตามชื่อไฟล์ต้นทาง
 * $logToSingleFile = false; 
 * $maxLogAgeDays = 7;
 * include 'error_to_log.inc.php';
 * 
 * // หรือบันทึกรวมไฟล์เดียว
 * $logToSingleFile = true;
 * $singleLogFile = __DIR__ . '/errors.log';
 * $maxLogAgeDays = 30;
 * include 'error_to_log.inc.php';
 * ```
 */

// กำหนดค่าเริ่มต้นและ parameter ต่าง ๆ
$logToSingleFile = false; 
$singleLogFile = dirname(__FILE__) . '/errors.log';
$maxLogAgeDays = 7;

// ปิด error log อัตโนมัติ (ใช้ custom handler แทน)
ini_set('log_errors', 'off');

// ตั้งค่า error reporting เต็มที่
error_reporting(E_ALL);

// กำหนดแสดง error บนหน้าจอหรือไม่ (แนะนำปิดใน production)
ini_set('display_errors', 'off');

/**
 * ฟังก์ชันลบไฟล์ log เก่าที่เกินกำหนด
 * @param string $dir โฟลเดอร์เก็บไฟล์ log
 * @param int $maxAgeDays จำนวนวันที่จะเก็บ log ถ้าเก่ากว่านี้จะลบไฟล์
 * @param string|null $singleLogFileName ชื่อไฟล์ log กรณีใช้เก็บในไฟล์เดียว
 * @param bool $logToSingleFile กำหนดว่าบันทึกแบบรวมไฟล์เดียวหรือแยกไฟล์
 */
function cleanOldLogFiles($dir, $maxAgeDays, $singleLogFileName = null, $logToSingleFile = false) {
    if ($maxAgeDays === 0) return; // ไม่ลบ log เก่าเมื่อกำหนดเป็น 0
    if (!is_dir($dir)) return;

    $expireTime = time() - ($maxAgeDays * 24 * 60 * 60);

    if ($logToSingleFile && $singleLogFileName && file_exists($singleLogFileName)) {
        if (filemtime($singleLogFileName) < $expireTime) {
            @unlink($singleLogFileName);
        }
    } elseif (!$logToSingleFile) {
        $files = glob($dir . '/*_error.log');
        foreach ($files as $file) {
            if (filemtime($file) < $expireTime) {
                @unlink($file);
            }
        }
    }
}

/**
 * ฟังก์ชันดักจับ error และบันทึกลงไฟล์ log ตามรูปแบบที่เลือกไว้
 * @param int $errno หมายเลขรหัสประเภทของ error
 * @param string $errstr ข้อความ error
 * @param string $errfile ไฟล์ที่เกิด error
 * @param int $errline บรรทัดที่เกิด error
 * @return bool คืน true เพื่อบอกว่า error ถูกจับและจัดการแล้ว
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    global $logToSingleFile, $singleLogFile;

    // ตรวจสอบข้อมูลสำคัญมีครบก่อนบันทึก
    if (empty($errno) || empty($errstr) || empty($errfile) || empty($errline)) {
        return false; // ข้อมูลไม่ครบ ไม่บันทึก
    }

    $dateTime = date('Y-m-d H:i:s');
    $fileName = basename($errfile);

    $errorMessage = "[$dateTime] ";
    $errorMessage .= "Error type: $errno | ";
    $errorMessage .= "File: $fileName | Line: $errline | ";
    $errorMessage .= "Message: $errstr" . PHP_EOL;

    if ($logToSingleFile) {
        error_log($errorMessage, 3, $singleLogFile);
    } else {
        $logFile = dirname($errfile) . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '_error.log';
        error_log($errorMessage, 3, $logFile);
    }

    return true;
}

// ตั้ง custom error handler
set_error_handler('customErrorHandler');

/**
 * ฟังก์ชันสำหรับดักจับ fatal errors ก่อน script สิ้นสุด
 */
register_shutdown_function(function() {
    global $logToSingleFile, $singleLogFile;

    $lastError = error_get_last();
    if ($lastError !== null) {
        $errno = $lastError['type'];
        $errfile = $lastError['file'];
        $errline = $lastError['line'];
        $errstr = $lastError['message'];

        // ตรวจสอบข้อมูลครบก่อนบันทึก
        if (empty($errno) || empty($errfile) || empty($errline)) {
            return;
        }

        $dateTime = date('Y-m-d H:i:s');
        $fileName = basename($errfile);
        $errorMessage = "[$dateTime] ";
        $errorMessage .= "Shutdown error type: $errno | ";
        $errorMessage .= "File: $fileName | Line: $errline | ";
        $errorMessage .= "Message: $errstr" . PHP_EOL;

        if ($logToSingleFile) {
            error_log($errorMessage, 3, $singleLogFile);
        } else {
            $logFile = dirname($errfile) . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '_error.log';
            error_log($errorMessage, 3, $logFile);
        }
    }
});

// ลบไฟล์ log เก่า
$logDir = dirname(__FILE__);
cleanOldLogFiles($logDir, $maxLogAgeDays, $singleLogFile, $logToSingleFile);

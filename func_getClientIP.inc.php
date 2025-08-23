<?php
/**
 * ดึงที่อยู่ IP ของลูกค้าจากตัวแปรเซิร์ฟเวอร์
 *
 * ฟังก์ชันนี้จะตรวจสอบค่าใน header ของเซิร์ฟเวอร์หลายตัวที่อาจมี IP จริงของลูกค้า
 * รวมถึงกรณีที่มีการผ่านพร็อกซีหรือโหลดบาลานเซอร์ด้วย โดยจะตรวจสอบและกรองค่า IP
 * เพื่อให้แน่ใจว่าเป็น IP สาธารณะที่ถูกต้อง ไม่ใช่ IP ส่วนตัวหรือสำรอง
 *
 * @return string|null คืนค่า IP ของลูกค้าในรูปแบบสตริง ถ้าไม่พบหรือไม่ถูกต้องจะคืนค่า null
 *
 * @example
 * ```
 * $clientIP = getClientIP();
 * if ($clientIP === null) {
 *     echo "ไม่สามารถระบุที่อยู่ IP ของลูกค้าได้";
 * } else {
 *     echo "ที่อยู่ IP ของลูกค้าคือ: " . $clientIP;
 * }
 * ```
 */
function getClientIP() {
    $ipAddress = null;

    $ipKeys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            // หาก header มีหลาย IP เช่นใน X-Forwarded-For จะแยกออกเป็น array
            $ipList = explode(',', $_SERVER[$key]);
            foreach ($ipList as $ip) {
                $ip = trim($ip);
                // ตรวจสอบว่า IP ที่ได้เป็น IP สาธารณะและถูกต้อง
                if (filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                )) {
                    return $ip;
                }
            }
        }
    }

    return $ipAddress; // คืนค่า null ถ้าไม่พบ IP ที่ถูกต้อง
}

# another ways to get the client IP address
# getenv() is used to get the value of an environment variable in PHP.
# - - - - https://stackoverflow.com/questions/15699101/get-the-client-ip-address-using-php
# $_SERVER is an array that contains server variables created by the web server.
# - - - - https://stackoverflow.com/questions/15699101/get-the-client-ip-address-using-php

?>

<?php
/**
 * Retrieves the client's IP address from the server variables.
 *
 * This function checks various server headers that may contain the client's real IP,
 * including those passed through proxies or load balancers. It validates the IP address
 * to ensure it is a valid public IP (not private or reserved).
 *
 * @return string|null Returns the client's IP address as a string if found and valid, otherwise null.
 *
 * @example
 * ```
 * $clientIP = getClientIP();
 * if ($clientIP === null) {
 *     echo "Cannot determine client IP address.";
 * } else {
 *     echo "Client IP is: " . $clientIP;
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
            // อาจมีหลาย IP ในกรณีของ header บางตัว เช่น X-Forwarded-For
            $ipList = explode(',', $_SERVER[$key]);
            foreach ($ipList as $ip) {
                $ip = trim($ip);
                // ตรวจสอบว่าเป็น IP ที่ valid และไม่ใช่ private หรือ reserved IP
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

    return $ipAddress; // คืนค่า null ถ้าไม่พบ IP ที่ valid
}
?>

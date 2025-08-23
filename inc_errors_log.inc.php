<?php
# ใน process ที่ include ไฟล์นี้เข้าไปหากมี error จะถูกบังคับให้ไปบันทึกใน errors.log ใน folder ปัจจุบันของ process นั้นๆ
# ข้อควรระวัง : errors.log อาจถูกเรียกดูจากผู้ไม่ประสงค์ดี หรือ bot ต่างๆได้
# reference : http://nyphp.org/PHundamentals/7_PHP-Error-Handling
ini_set ('error_reporting', E_ALL);
//ini_set ('display_errors','on'); 
//ini_set ('display_startup_errors','on');
ini_set ('log_errors','on'); 
ini_set ('error_log', dirname(__FILE__).'/errors.log');

# ป้องกันไม่ให้เข้าถึงไฟล์ .log ทุกไฟล์ในโฟลเดอร์นี้และ subfolders
/*
<FilesMatch "\.log$">
    Require all denied
    # สำหรับ Apache เวอร์ชันเก่า (< 2.4) ใช้แทนได้ดังนี้:
    # Order allow,deny
    # Deny from all
</FilesMatch>
*/

# บล็อกการเข้าถึงไฟล์ errors.log โดยเฉพาะ (ถ้าต้องการเจาะจง)
/*
<Files "errors.log">
    Require all denied
</Files>
*/

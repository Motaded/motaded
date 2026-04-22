<?php
$path = __FILE__;
$img = 'logo1.jpg';
if (file_exists($img)) {
    echo "<h2>✅ الملف موجود!</h2>";
    echo "<img src='$img' width='200' />";
} else {
    echo "<h2>❌ الملف غير موجود في هذا المجلد!</h2>";
}
?>

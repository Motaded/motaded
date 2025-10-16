<?php
/**
 * سكربت لنسخ مجلد sites من motaded.com.sa إلى الجذر.
 * تأكد إن عندك صلاحيات كتابة في المجلد الهدف.
 */

function copy_folder($source, $destination) {
    if (!is_dir($source)) {
        die("❌ مجلد المصدر غير موجود: $source");
    }

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
        echo "✅ تم إنشاء مجلد الوجهة: $destination<br>";
    }

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;

        $srcPath = $source . '/' . $file;
        $destPath = $destination . '/' . $file;

        if (is_dir($srcPath)) {
            copy_folder($srcPath, $destPath);
        } else {
            copy($srcPath, $destPath);
            echo "📁 تم نسخ: $srcPath → $destPath<br>";
        }
    }
    closedir($dir);
}

$source = __DIR__ . '/motaded.com.sa/sites';
$destination = __DIR__ . '/sites';

echo "<h2>🚀 بدء النسخ من: $source إلى: $destination</h2>";
copy_folder($source, $destination);
echo "<h3>✅ تم الانتهاء.</h3>";
?>

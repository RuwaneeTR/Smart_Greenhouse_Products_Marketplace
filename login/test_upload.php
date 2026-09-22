<?php
$uploadDir = '../uploads/gap_certificates/';

echo "<h2>Upload Test</h2>";
echo "Target folder: <strong>" . realpath($uploadDir) . "</strong><br><br>";

if (!is_dir($uploadDir)) {
    echo "❌ Folder does NOT exist<br>";
    if (mkdir($uploadDir, 0777, true)) {
        echo "✅ Folder created now<br>";
    } else {
        echo "❌ Could NOT create folder — permission issue<br>";
    }
} else {
    echo "✅ Folder exists<br>";
}

if (is_writable($uploadDir)) {
    echo "✅ Folder is writable<br>";
} else {
    echo "❌ Folder is NOT writable<br>";
}

$testFile = $uploadDir . 'test.txt';
if (file_put_contents($testFile, 'hello')) {
    echo "✅ Test file written successfully at: " . realpath($testFile);
    unlink($testFile);
} else {
    echo "❌ Could NOT write test file";
}
?>
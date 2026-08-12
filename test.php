<?php
/**
 * MEBELCOM — быстрая проверка, что PHP на сайте работает.
 * После проверки этот файл удалить.
 */

header('Content-Type: text/plain; charset=utf-8');

echo "PHP работает\n";
echo "Версия: " . PHP_VERSION . "\n";
echo "cURL: " . (extension_loaded('curl') ? 'есть' : 'НЕТ — заявки отправляться не будут') . "\n";
echo "mbstring: " . (extension_loaded('mbstring') ? 'есть' : 'нет') . "\n";
echo "Папка сайта: " . __DIR__ . "\n";
echo "Загрузка файлов: " . ini_get('upload_max_filesize') . " / POST " . ini_get('post_max_size') . "\n";

echo "\n--- Файлы обработчика ---\n";
foreach (['api/lead.php', 'api/config.php', 'api/.htaccess', 'uploads/.htaccess'] as $f) {
    echo str_pad($f, 22) . (is_file(__DIR__ . '/' . $f) ? 'на месте' : 'НЕ НАЙДЕН') . "\n";
}

echo "\n--- Запись в папку uploads ---\n";
$dir = __DIR__ . '/uploads';
if (!is_dir($dir)) {
    echo (@mkdir($dir, 0755, true) ? 'папка создана' : 'НЕ УДАЛОСЬ создать папку') . "\n";
}
$probe = $dir . '/_probe.txt';
echo (@file_put_contents($probe, 'ok') !== false ? 'запись работает' : 'ЗАПИСЬ ЗАПРЕЩЕНА — нужны права 775') . "\n";
@unlink($probe);

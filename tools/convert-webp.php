<?php
/* ============================================================================
   tools/convert-webp.php — конвертация изображений в WebP (оптимизация веса).
   ----------------------------------------------------------------------------
   Зачем: WebP весит на 25–35% меньше JPEG/PNG при том же качестве. Рядом с
   каждым .jpg/.png создаётся .webp, а media_picture() (helpers.php) сама
   подставит его через <picture> с фолбэком на оригинал.

   КАК ЗАПУСТИТЬ (после того, как положил настоящие фото из задания в assets/img):
     • из браузера:  http://localhost/exam/tools/convert-webp.php
     • или из консоли: php tools/convert-webp.php
   Скрипт пройдёт по assets/img/ и создаст недостающие .webp.
   Требуется расширение GD с поддержкой WebP (в XAMPP включено по умолчанию).
   ============================================================================ */

$dir = __DIR__ . '/../assets/img';     // 🔧 папка с картинками
$quality = 80;                          // качество WebP (0–100)

if (!function_exists('imagewebp')) {
    exit("GD без поддержки WebP. Включи extension=gd в php.ini (в XAMPP обычно включено).\n");
}

$cli = php_sapi_name() === 'cli';
$nl  = $cli ? "\n" : "<br>";
if (!$cli) header('Content-Type: text/html; charset=utf-8');

$made = 0; $skip = 0;
foreach (glob($dir . '/*.{jpg,jpeg,png}', GLOB_BRACE) as $file) {
    $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);
    if (is_file($webp)) { echo "= уже есть: " . basename($webp) . $nl; $skip++; continue; }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $img = $ext === 'png' ? imagecreatefrompng($file) : imagecreatefromjpeg($file);
    if (!$img) { echo "! не открылось: " . basename($file) . $nl; continue; }

    if ($ext === 'png') {                 // сохраняем прозрачность PNG
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
    }
    imagewebp($img, $webp, $quality);
    imagedestroy($img);
    echo "+ создан: " . basename($webp) . $nl;
    $made++;
}
echo $nl . "Готово. Создано WebP: $made, пропущено: $skip." . $nl;
echo "Картинки на сайте автоматически переключатся на WebP (через <picture>)." . $nl;

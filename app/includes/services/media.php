<?php
function imageResizeWebp(string $srcPath, int $newW, int $newH, string $destPath): bool
{
    $mime = mime_content_type($srcPath);

    switch ($mime) {
        case 'image/jpeg': $src = imagecreatefromjpeg($srcPath); break;
        case 'image/png':  $src = imagecreatefrompng($srcPath);  break;
        case 'image/webp': $src = imagecreatefromwebp($srcPath); break;
        default: return false;
    }

    $origW = imagesx($src);
    $origH = imagesy($src);

    $ratio = max($newW / $origW, $newH / $origH);
    $resizeW = (int)($origW * $ratio);
    $resizeH = (int)($origH * $ratio);

    $tmp = imagecreatetruecolor($resizeW, $resizeH);
    imagealphablending($tmp, false);
    imagesavealpha($tmp, true);

    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $resizeW, $resizeH, $origW, $origH);

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    $cropX = (int)(($resizeW - $newW) / 2);
    $cropY = (int)(($resizeH - $newH) / 2);

    imagecopy($dst, $tmp, 0, 0, $cropX, $cropY, $newW, $newH);

    imagewebp($dst, $destPath, 85);

    imagedestroy($src);
    imagedestroy($tmp);
    imagedestroy($dst);

    return true;
}
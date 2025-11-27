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

    $ratio = min($newW / $origW, $newH / $origH);
    $targetW = (int)($origW * $ratio);
    $targetH = (int)($origH * $ratio);

    $dst = imagecreatetruecolor($targetW, $targetH);

    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

    imagewebp($dst, $destPath, 80);

    imagedestroy($src);
    imagedestroy($dst);

    return true;
}

function makeFolderSlug(string $text): string
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '_', $text);
    return trim($text, '_');
}
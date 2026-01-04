<?php

/**
 * Helper functions for views, escaping, and image processing
 * 
 * @package App\Includes\Services\Helpers
 */

/**
 * Render a view file with data
 * 
 * @param string $view View name (without .php extension, relative to app/views/)
 * @param array $data Data to pass to view
 * @return string Rendered HTML content
 */
function renderView(string $view, array $data = []): string {
    extract($data);
    ob_start();
    require BASE_DIR . '/app/views/' . $view . '.php';
    return ob_get_clean();
}

/**
 * Get human-readable rating text based on numeric rating
 * 
 * @param float $rating Average rating (0-10)
 * @param int $reviewsCount Number of reviews
 * @return string Rating text (e.g., "Velmi pozitivní", "Pozitivní")
 */
function getRatingText(float $rating, int $reviewsCount): string {
    if ($reviewsCount === 0) {
        return 'Žádné hodnocení';
    }

    $texts = [
        8.5 => 'Velmi pozitivní',
        7.0 => 'Pozitivní',
        5.0 => 'Smíšené',
        2.5 => 'Negativní',
        0.0 => 'Velmi negativní'
    ];

    foreach ($texts as $threshold => $text) {
        if ($rating >= $threshold) {
            return $text;
        }
    }

    return 'Velmi negativní';
}

/**
 * Resize and convert image to WebP format
 * 
 * @param string $srcPath Source image path
 * @param int $newW Target width
 * @param int $newH Target height
 * @param string $destPath Destination path for WebP file
 * @return bool True on success, false on failure
 */
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
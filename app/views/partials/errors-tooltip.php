<?php
/**
 * Error messages partial
 * 
 * @file App\Views\Partials\ErrorsTooltip
 * @param array|null $error Array of error messages (can be single string or array of strings)
 */
if (!empty($error)):
    // Handle both single string and array
    $errorArray = is_array($error) ? $error : [$error];
    
    foreach ($errorArray as $err):
?>
        <small class="error"><?= htmlspecialchars($err) ?></small>
<?php
    endforeach;
endif;
?>
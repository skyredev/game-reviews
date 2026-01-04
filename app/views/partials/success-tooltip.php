<?php
/**
 * Success messages partial
 * 
 * @file App\Views\Partials\SuccessTooltip
 * @param array|null $message Array of success messages (can be single string or array of strings)
 */
if (!empty($message)):
    // Handle both single string and array
    $messageArray = is_array($message) ? $message : [$message];
    
    foreach ($messageArray as $msg):
?>
        <small class="success"><?= htmlspecialchars($msg) ?></small>
<?php
    endforeach;
endif;
?>
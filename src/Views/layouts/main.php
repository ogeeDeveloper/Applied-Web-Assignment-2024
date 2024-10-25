<?php
// Include header (which already includes navigation)
require_once APP_ROOT . '/src/Views/shared/header.php';

// Render the main content
echo $content ?? '';

// Include footer
require_once APP_ROOT . '/src/Views/shared/footer.php';
?>
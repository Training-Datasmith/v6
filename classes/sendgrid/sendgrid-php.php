<?php

declare (strict_types=1);
/**
 * This file is used to load the Composer autoloader if required.
 */
use Send_Grid\Mail\Mail;
// Define path/existence of Composer autoloader
$composer_autoload_file = __DIR__ . '/vendor/autoload.php';
$composer_autoload_file_exists = is_file($composer_autoload_file);
// Can't locate SendGrid\Mail\Mail class?
if (!class_exists(Mail::class)) {
    // Suggest to load Composer autoloader of project
    if (!$composer_autoload_file_exists) {
        //  Can't load the Composer autoloader in this project folder
        error_log("Composer autoloader not found. Execute 'composer install' in the project folder first.");
    } else {
        // Load Composer autoloader
        require_once $composer_autoload_file;
        // If desired class still not existing
        // Suggest to review the Composer autoloader settings
        error_log('Error finding SendGrid classes. Please review your autoloading configuration.');
    }
}
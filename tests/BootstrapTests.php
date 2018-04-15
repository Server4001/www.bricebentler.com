<?php declare(strict_types=1);
/**
 * @category     Tests
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

define('PROJECT_ROOT', dirname(__DIR__));

require_once PROJECT_ROOT . '/vendor/autoload.php';

set_error_handler(function($errorNumber, $errorString, $errorFile, $errorLine) {
    // error suppressed with @
    if (error_reporting() === 0) {
        return;
    }

    throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
});

error_reporting(E_ALL);

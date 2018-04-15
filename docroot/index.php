<?php declare(strict_types=1);
/**
 * @category     Main
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

use BentlerDesign\Http\Router;
use BentlerDesign\Models\Config;
use BentlerDesign\Services\Logger;
use Dotenv\Dotenv;

define('PROJECT_ROOT', dirname(__DIR__));

set_error_handler(function($errorNumber, $errorString, $errorFile, $errorLine) {
    if (error_reporting() === 0) {
        // The error was suppressed using @.
        return;
    }

    throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
});

require_once PROJECT_ROOT . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

(new Dotenv(PROJECT_ROOT . DIRECTORY_SEPARATOR . 'etc'))->load();

$config = new Config();

try {
    $logFilePath = $config->get('log_path');
    $logger = new Logger($logFilePath);
} catch (Throwable $e) {
    http_response_code(500);
    echo "Missing log file path, or directory not writable.";
    exit();
}

try {
    (new Router($config))->route();
} catch (Throwable $e) {
    http_response_code(500);
    $logger->logException($e);
    echo "An error occurred. Sorry.";
}

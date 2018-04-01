<?php declare(strict_types=1);
/**
 * @category     Main
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

use BentlerDesign\Http\Router;
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

$config = [
    'google_analytics' => (getenv('USE_GOOGLE_ANALYTICS') === 'true'),
    'sendgrid_user' => getenv('SENDGRID_USER'),
    'sendgrid_pass' => getenv('SENDGRID_PASS'),
    'log_path' => (getenv('LOG_PATH') ?? '/var/log/nginx/www.log'),
];

$logger = new Logger($config['log_path']);

try {
    (new Router($logger, $config))->route();
} catch (Throwable $e) {
    $logger->logException($e);
    echo "An error occurred. Sorry.";
}

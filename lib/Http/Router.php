<?php declare(strict_types=1);
/**
 * @category     Http
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Http;

use BentlerDesign\Services\Logger;
use Closure;

class Router
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $config;

    public function __construct(Logger $logger, array $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function route(): Router
    {
        $routes = $this->getRoutes();

        if (array_key_exists($_SERVER['REQUEST_URI'], $routes)) {
            $routes[$_SERVER['REQUEST_URI']]();
        } else {
            $routes['DEFAULT']();
        }

        return $this;
    }

    private function getRoutes(): array
    {
        return [
            '/mail' => Closure::fromCallable([$this, 'mailRoute']),
            'DEFAULT' => Closure::fromCallable([$this, 'defaultRoute']),
        ];
    }

    private function mailRoute()
    {
        if (!isset($this->config['sendgrid_user'], $this->config['sendgrid_pass']) ||
            $this->config['sendgrid_user'] === false || $this->config['sendgrid_pass'] === false) {

            $this->logger->log('Unable to send email due to missing credentials.');

            return;
        }

        // TODO : THIS.
    }

    private function defaultRoute()
    {
        $config = $this->config;

        ob_start();
        require(PROJECT_ROOT . '/lib/Views/main.php');
        $output = ob_get_contents();
        ob_end_clean();

        echo $output;
    }
}

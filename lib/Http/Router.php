<?php declare(strict_types=1);
/**
 * @category     Http
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Http;

use BentlerDesign\Models\Config;
use BentlerDesign\Services\Email\SendGrid;
use BentlerDesign\Services\Logger;
use BentlerDesign\Helpers\Template;
use Closure;
use Exception;
use GuzzleHttp\Client;

class Router
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Logger $logger, Config $config)
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

    /**
     * @throws \Exception
     */
    private function mailRoute()
    {
        $sendGrid = new SendGrid(new Client(), $this->config, $this->logger);

        if(empty($_POST['name']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['message']) ||
            !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {

            return false;
        }

        $name = strip_tags(htmlspecialchars($_POST['name']));
        $email = strip_tags(htmlspecialchars($_POST['email']));
        $phone = strip_tags(htmlspecialchars($_POST['phone']));
        $message = strip_tags(htmlspecialchars($_POST['message']));

        $toEmail = $this->config->get('sendgrid_to_email');
        $toName = $this->config->get('sendgrid_to_name');
        $subject = 'Contact from bricebentler.com';

        $html = Template::render(PROJECT_ROOT . '/lib/Views/email-to-me.php', [
            'email_address' => $email,
            'name' => $name,
            'phone' => $phone,
            'message' => $message,
            'date' => date('Y-m-d H:i:s e'),
        ]);

        $sendGrid->sendEmail($toEmail, $toName, $subject, null, $html);

        return true;
    }

    /**
     * @throws Exception
     */
    private function defaultRoute()
    {
        echo Template::render(PROJECT_ROOT . '/lib/Views/main.php', [
            'google_analytics' => $this->config->get('google_analytics'),
        ]);
    }
}

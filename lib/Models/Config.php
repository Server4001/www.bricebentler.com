<?php declare(strict_types=1);
/**
 * @category     Models
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Models;

use Exception;

class Config
{
    /**
     * @var array
     */
    private $config;

    public function __construct()
    {
        $this->config = [
            'google_analytics' => (getenv('USE_GOOGLE_ANALYTICS') === 'true'),
            'sendgrid_user' => getenv('SENDGRID_USER'),
            'sendgrid_pass' => getenv('SENDGRID_PASS'),
            'sendgrid_from_email' => (getenv('SENDGRID_FROM_EMAIL') ?? 'foo@bar.com'),
            'sendgrid_from_name' => (getenv('SENDGRID_FROM_NAME') ?? 'Some Person'),
            'log_path' => (getenv('LOG_PATH') ?? '/var/log/nginx/www.log'),
        ];
    }

    /**
     * @return mixed
     *
     * @throws Exception
     */
    public function get(string $key)
    {
        if (!array_key_exists($key, $this->config)) {
            throw new Exception("Key does not exist in config: '{$key}'.");
        }

        return $this->config[$key];
    }
}

<?php declare(strict_types=1);
/**
 * @category     Services
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Services\Email;

use BentlerDesign\Models\Config;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class SendGrid
{
    const MAIL_ENDPOINT = 'https://api.sendgrid.com/v3/mail/send';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $fromEmail;

    /**
     * @var string
     */
    private $fromName;

    /**
     * @throws Exception
     */
    public function __construct(Client $client, Config $config)
    {
        $this->client = $client;
        $this->apiKey = $config->get('sendgrid_api_key');
        $this->fromEmail = $config->get('sendgrid_from_email');
        $this->fromName = $config->get('sendgrid_from_name');

        if ($this->apiKey === false) {
            throw new Exception('Missing sendgrid api key in config.');
        }
    }

    /**
     * @throws Exception
     */
    public function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $text = null,
        string $html = null): SendGrid
    {
        $payload = $this->createRequestPayload($toEmail, $toName, $subject, $text, $html);

        $response = $this->client->post(self::MAIL_ENDPOINT, [
            RequestOptions::JSON => $payload,
            RequestOptions::HEADERS => [
                'authorization' => "Bearer {$this->apiKey}",
            ],
        ]);

        return $this->handleSyncResponse($response);
    }

    private function createRequestPayload(
        string $toEmail,
        string $toName,
        string $subject,
        string $text = null,
        string $html = null): array
    {
        if (is_null($text) && is_null($html)) {
            throw new InvalidArgumentException('Must set text and/or html.');
        }

        $payload = [
            'personalizations' => [
                [
                    'to' => [
                        [
                            'email' => $toEmail,
                            'name' => $toName,
                        ],
                    ],
                ],
            ],
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName,
            ],
            'subject' => $subject,
        ];

        if (!is_null($text)) {
            $payload['content'][] = [
                'type' => 'text/plain',
                'value' => $text,
            ];
        }
        if (!is_null($html)) {
            $payload['content'][] = [
                'type' => 'text/html',
                'value' => $html,
            ];
        }

        return $payload;
    }

    /**
     * @throws Exception
     */
    private function handleSyncResponse(ResponseInterface $response): SendGrid
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 202) {
            throw new Exception("Invalid status code returned from SendGrid: '{$statusCode}'.");
        }

        return $this;
    }
}

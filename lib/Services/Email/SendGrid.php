<?php declare(strict_types=1);
/**
 * @category     Services
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Services\Email;

use BentlerDesign\Models\Config;
use BentlerDesign\Services\Logger;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class SendGrid
{
    const MAIL_ENDPOINT = 'https://api.sendgrid.com/api/mail.send.json';
    const RESPONSE_SUCCESS = 'success';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $pass;

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
    public function __construct(Client $client, Config $config, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->user = $config->get('sendgrid_user');
        $this->pass = $config->get('sendgrid_pass');
        $this->fromEmail = $config->get('sendgrid_from_email');
        $this->fromName = $config->get('sendgrid_from_name');

        if ($config->get('sendgrid_user') === false || $config->get('sendgrid_pass') === false) {
            throw new Exception('Missing sendgrid credentials in config.');
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
        string $html = null,
        array $files = []): SendGrid
    {
        $payload = $this->createRequestPayload($toEmail, $toName, $subject, $text, $html, $files);

        $response = $this->client->post(self::MAIL_ENDPOINT, array(
            RequestOptions::FORM_PARAMS => $payload,
        ));

        return $this->handleSyncResponse($response);
    }

    private function createRequestPayload(
        string $toEmail,
        string $toName,
        string $subject,
        string $text = null,
        string $html = null,
        array $files = []): array
    {
        if (is_null($text) && is_null($html)) {
            throw new InvalidArgumentException('Must set text and/or html.');
        }

        $payload = array(
            'api_user' => $this->user,
            'api_key' => $this->pass,
            'to' => $toEmail,
            'toname' => $toName,
            'subject' => $subject,
            'from' => $this->fromEmail,
            'fromname' => $this->fromName,
        );

        if (!is_null($text)) {
            $payload['text'] = $text;
        }
        if (!is_null($html)) {
            $payload['html'] = $html;
        }

        foreach ($files as $fileName => $fileContents) {
            $payload['files'][$fileName] = $fileContents;
        }

        return $payload;
    }

    /**
     * @throws Exception
     */
    private function handleSyncResponse(ResponseInterface $response): SendGrid
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new Exception("Invalid status code returned from SendGrid: '{$statusCode}'.");
        }

        $responseBodyContents = $response->getBody()->getContents();
        $responseBody = \GuzzleHttp\json_decode($responseBodyContents, true);

        if (!is_array($responseBody)) {
            throw new Exception("Invalid response body returned from SendGrid: '{$responseBodyContents}'.");
        }
        if (!isset($responseBody['message']) || $responseBody['message'] !== self::RESPONSE_SUCCESS) {
            throw new Exception("Invalid message in SendGrid response body: '{$responseBody['message']}'.");
        }

        return $this;
    }
}

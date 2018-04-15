<?php declare(strict_types=1);
/**
 * @category     UnitTests
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace Unit\Services\Email;

use BentlerDesign\Services\Email\SendGrid;
use PHPUnit_Framework_TestCase;

class SendGridTest extends PHPUnit_Framework_TestCase
{
    /** @var \GuzzleHttp\Client|\PHPUnit_Framework_MockObject_MockObject */
    private $guzzleMock;

    /** @var \BentlerDesign\Models\Config|\PHPUnit_Framework_MockObject_MockObject */
    private $configMock;

    /** @var SendGrid */
    private $sendGrid;

    public function setUp()
    {
        $this->guzzleMock = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder('BentlerDesign\Models\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock->expects($this->any())
            ->method('get')
            ->with($this->isType('string'))
            ->willReturn('foo');

        $this->sendGrid = new SendGrid($this->guzzleMock, $this->configMock);
    }

    public function testSendEmail()
    {
        $email = 'a@a.com';
        $name = 'Some Person';
        $subject = 'Some Subject';
        $html = 'This is the content of the email.';
        $response = new class() {
            public function getStatusCode() {
                return 202;
            }
        };

        $this->guzzleMock->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo(SendGrid::MAIL_ENDPOINT),
                $this->equalTo([
                    'headers' => [
                        'authorization' => 'Bearer foo',
                    ],
                    'json' => [
                        'personalizations' => [
                            [
                                'to' => [
                                    [
                                        'email' => $email,
                                        'name' => $name,
                                    ],
                                ],
                            ],
                        ],
                        'from' => [
                            'email' => 'foo',
                            'name' => 'foo',
                        ],
                        'subject' => $subject,
                        'content' => [
                            [
                                'type' => 'text/html',
                                'value' => $html,
                            ],
                        ],
                    ],
                ])
            )->willReturn($response);

        $this->sendGrid->sendEmail($email, $name, $subject, null, $html);
    }
}

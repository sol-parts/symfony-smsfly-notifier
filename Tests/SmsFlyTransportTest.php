<?php

/*
 * This file is part of the Sol.parts package.
 *
 * (c) Andrii Didenko <mail@sol.parts>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SolParts\SymfonySmsFlyNotifier\Tests;

use SolParts\SymfonySmsFlyNotifier\SmsFlyOptions;
use SolParts\SymfonySmsFlyNotifier\SmsFlyTransport;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Oleksandr Nechyporuk <oleksandr@nechyporuk.name>
 */
final class SmsFlyTransportTest extends TransportTestCase
{
    public static function createTransport(?HttpClientInterface $client = null): SmsFlyTransport
    {
        return new SmsFlyTransport('authKey', 'sender', $client ?? new MockHttpClient());
    }

    public static function supportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('+380771234567', 'Hi!')];
    }

    public static function unsupportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hi!')];
    }

    public static function toStringProvider(): iterable
    {
        yield ['smsfly://sms-fly.ua?from=sender', self::createTransport()];
    }

    public function testSuccessfulSend()
    {
        $body = [
            'success' => 1,
            'date' => '2025-09-17 23:57:45 +0300',
            'data' => [
                'messageID' => 'FAPI00133FA7A9000001',
                'sms' => [
                    'status' => 'ACCEPT',
                    'date' => '2025-09-17 23:57:45 +0300',
                    'cost' => '0.979',
                ],
            ],
        ];

        $response = new JsonMockResponse(body: $body, info: ['http_code' => 200]);

        $client = new MockHttpClient(static function (string $method, string $url, array $options) use ($response): ResponseInterface {
            $body = \json_decode($options['body'], true);
            self::assertSame([
                'auth' => [
                    'key' => 'authKey',
                ],
                'action' => 'SENDMESSAGE',
                'data' => [
                    'recipient' => '380771234567',
                    'channels' => [
                        'sms',
                    ],
                    'sms' => [
                        'source' => 'sender',
                        'ttl' => 5,
                        'text' => 'Текст sms. Перевірка!',
                    ],
                ],
            ], $body);

            return $response;
        });

        $options = (new SmsFlyOptions())->ttl(5);
        $message = new SmsMessage(
            phone: '+380771234567',
            subject: 'Текст sms. Перевірка!',
            options: $options,
        );

        $transport = self::createTransport($client);
        $sentMessage = $transport->send($message);

        self::assertInstanceOf(SentMessage::class, $sentMessage);
        self::assertSame('FAPI00133FA7A9000001', $sentMessage->getMessageId());
        self::assertSame($body, $sentMessage->getInfo());
    }
}

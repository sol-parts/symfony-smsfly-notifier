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

namespace SolParts\SymfonySmsFlyNotifier;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Oleksandr Nechyporuk <oleksandr@nechyporuk.name>
 * @author Andrii Didenko <andrii@didenko.dev>
 *
 * @see https://sms-fly.ua/integration/api/
 */
final class SmsFlyTransport extends AbstractTransport
{
    protected const HOST = 'sms-fly.ua';

    public function __construct(
        #[\SensitiveParameter]
        private string $authKey,
        private string $from,
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null,
    ) {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return \sprintf('smsfly://%s?from=%s', $this->getEndpoint(), \urlencode($this->from));
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    /**
     * Перевірка балансу.
     */
    public function balance(): string
    {
        $endpoint = \sprintf('https://%s/api/v2/api.php', $this->getEndpoint());

        $response = $this->client->request('POST', $endpoint, [
            'json' => [
                'auth' => [
                    'key' => $this->authKey,
                ],
                'action' => 'GETBALANCE',
                'data' => [],
            ],
        ]);

        try {
            $info = $response->toArray(false);
        } catch (\Exception|\Error $e) {
            throw new TransportException('SmsFly API request execution error.', $response, 0, $e);
        }

        return $info['data']['balance'] ?? 'n/a';
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$this->supports($message)) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        $from = $message->getFrom() ?: $this->from;

        $endpoint = \sprintf('https://%s/api/v2/api.php', $this->getEndpoint());

        $data = ['recipient' => \ltrim($message->getPhone(), '+')];
        $data['channels'] = $message->getOptions()?->toArray()['channels'] ?? ['sms'];

        if (\in_array('viber', $data['channels'], true)) {
            $data['viber'] = [
                'source' => $message->getOptions()?->toArray()['viber_source'] ?? $from,
                'ttl' => $message->getOptions()?->toArray()['ttl'] ?? 60,
                'text' => $message->getOptions()?->toArray()['viber_text'] ?? $message->getSubject(),
            ];
            if (!empty($message->getOptions()?->toArray()['viber_button_url'])) {
                $data['viber']['button'] = [
                    'caption' => $message->getOptions()?->toArray()['viber_button_caption'] ?? 'Button',
                    'url' => $message->getOptions()?->toArray()['viber_button_url'],
                ];
            }
            if (!empty($message->getOptions()?->toArray()['viber_image'])) {
                $data['viber']['image'] = $message->getOptions()?->toArray()['viber_image'];
            }
        }

        if (\in_array('sms', $data['channels'], true)) {
            $data['sms'] = [
                'source' => $from,
                'ttl' => $message->getOptions()?->toArray()['ttl'] ?? 60,
                'text' => $message->getSubject(),
            ];
        }

        $response = $this->client->request('POST', $endpoint, [
            'json' => [
                'auth' => ['key' => $this->authKey],
                'action' => 'SENDMESSAGE',
                'data' => $data,
            ],
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote SmsFly server.', $response, 0, $e);
        }

        try {
            $info = $response->toArray(false);
        } catch (DecodingExceptionInterface $e) {
            throw new TransportException('Could not decode body to an array.', $response, 0, $e);
        }

        if (empty($info['success']) || 200 !== $statusCode) {
            try {
                $textError = \json_encode($info, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                $textError = 'unknown error';
            }
            throw new TransportException(\sprintf('Unable to send the SMS with SmsFly: "%s".', $textError), $response);
        }

        $messageId = $info['data']['messageID'] ?? null;

        $sentMessage = new SentMessage($message, (string) $this, $info);
        $sentMessage->setMessageId((string) $messageId);

        return $sentMessage;
    }
}

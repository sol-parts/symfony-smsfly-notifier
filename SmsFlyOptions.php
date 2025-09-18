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

use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

/**
 * @author Oleksandr Nechyporuk <oleksandr@nechyporuk.name>
 * @author Andrii Didenko <andrii@didenko.dev>
 */
final class SmsFlyOptions implements MessageOptionsInterface
{
    private array $options = [];

    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * Термін життя повідомлення у хвилинах, приймає значення від 1 до  1440 (24 години).
     */
    public function ttl(int $ttl): static
    {
        if ($ttl < 0 || $ttl > 1440) {
            throw new InvalidArgumentException(\sprintf('Message lifetime in minutes, takes values from 1 to 1440. Value "%s" is not available.', $ttl));
        }

        $this->options['ttl'] = $ttl;

        return $this;
    }

    /**
     * Канали відправки повідомлення. Доступні значення viber, sms.
     * При вказанні декількох каналів, повідомлення відправляється в канали в порядку черговості.
     * Повідомлення відправляється в наступний канал, якщо доставка в попередній не була успішна.
     */
    public function channels(array $channels): static
    {
        $allowed = ['viber', 'sms'];
        $unknown = \array_diff($channels, $allowed);
        if ($unknown) {
            throw new InvalidArgumentException(\sprintf('Available channels values "viber" or "sms". Unavailable channel: "%s".', \implode(', ', $unknown)));
        }

        $this->options['channels'] = $channels;

        return $this;
    }

    /**
     * Ім'я відправника Viber, якщо не вказано, то буде використовуватися таке саме, як для SMS.
     */
    public function viber_source(string $viber_source): static
    {
        $this->options['viber_source'] = $viber_source;

        return $this;
    }

    /**
     * Опціональне поле, текст повідомлення Viber довжиною до 1000 символів.
     * Якщо не вказано, буде використовуватися текст SMS повідомлення.
     */
    public function viber_text(string $viber_text): static
    {
        if (\mb_strlen($viber_text) > 1000) {
            throw new InvalidArgumentException(\sprintf('Viber message text must be up to 1000 characters long. Length "%s".', \mb_strlen($viber_text)));
        }
        $this->options['viber_text'] = $viber_text;

        return $this;
    }

    /**
     * Опціональне поле, текст для кнопки в Viber повідомленні.
     */
    public function viber_button_caption(string $viber_button_caption): static
    {
        $this->options['viber_button_caption'] = $viber_button_caption;

        return $this;
    }

    /**
     * Опціональне поле, посилання для кнопки в Viber повідомленні.
     */
    public function viber_button_url(string $viber_button_url): static
    {
        $this->options['viber_button_url'] = $viber_button_url;

        return $this;
    }

    /**
     * Опціональне поле, https url адреса на картинку, що відображається в Viber повідомленні.
     */
    public function viber_image(string $viber_image): static
    {
        $this->options['viber_image'] = $viber_image;

        return $this;
    }

    public function getRecipientId(): ?string
    {
        return null;
    }
}

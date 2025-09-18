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

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;

/**
 * @author Oleksandr Nechyporuk <oleksandr@nechyporuk.name>
 * @author Andrii Didenko <andrii@didenko.dev>
 */
final class SmsFlyTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): SmsFlyTransport
    {
        $scheme = $dsn->getScheme();

        if ('smsfly' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'smsfly', $this->getSupportedSchemes());
        }

        $authKey = $this->getUser($dsn);
        $from = $dsn->getRequiredOption('from');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new SmsFlyTransport($authKey, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['smsfly'];
    }
}

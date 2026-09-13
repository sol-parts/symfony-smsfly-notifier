<?php

/*
 * This file is part of SOLPARTS
 *
 * (c) SOLPARTS LLC (EDRPOU 46143031) <mail@sol.parts>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SolParts\SymfonySmsFlyNotifier\Tests;

use SolParts\SymfonySmsFlyNotifier\SmsFlyTransportFactory;
use Symfony\Component\Notifier\Test\AbstractTransportFactoryTestCase;
use Symfony\Component\Notifier\Test\IncompleteDsnTestTrait;
use Symfony\Component\Notifier\Test\MissingRequiredOptionTestTrait;

/**
 * @author Oleksandr Nechyporuk <oleksandr@nechyporuk.name>
 * @author Andrii Didenko <andrii@didenko.dev>
 */
final class SmsFlyTransportFactoryTest extends AbstractTransportFactoryTestCase
{
    use IncompleteDsnTestTrait;
    use MissingRequiredOptionTestTrait;

    public function createFactory(): SmsFlyTransportFactory
    {
        return new SmsFlyTransportFactory();
    }

    public static function createProvider(): iterable
    {
        yield [
            'smsfly://sms-fly.ua?from=sol',
            'smsfly://authKey@default?from=sol',
        ];

        yield [
            'smsfly://sms-fly.ua?from=Example+Sender',
            'smsfly://authKey@default?from=Example Sender',
        ];
    }

    public static function supportsProvider(): iterable
    {
        yield [true, 'smsfly://authKey@default?from=sol'];
        yield [false, 'somethingElse://authKey@default?from=sol'];
    }

    public static function missingRequiredOptionProvider(): iterable
    {
        yield 'missing option: from' => ['smsfly://authKey@default'];
    }

    public static function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://authKey@default?from=sol'];
        yield ['somethingElse://authKey@default'];
    }

    public static function incompleteDsnProvider(): iterable
    {
        yield ['smsfly://default?from=sol'];
    }
}

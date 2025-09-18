Symfony SMS-fly Notifier
=================

Provides [SMS-fly](https://sms-fly.ua/) integration for Symfony Notifier. SymfonySmsFlyNotifier allows you to send SMS and/or Viber messages

Example use without Symfony Full-Stack Framework
---------------------------
```php
use SolParts\SymfonySmsFlyNotifier\SmsFlyTransport;
use Psr\Log\LoggerInterface;

/** @var LoggerInterface $logger */
$logger = /*...*/;

try {
    $transport = new SmsFlyTransport('authKey', 'InfoCenter');
    
    $sms = new SmsMessage('+380771234567', 'My sms message');
    $sentMessage = $transport->send($sms);
catch (\Throwable $e) {
    $logger->critical($e->getMessage());
}

dump($sentMessage->getMessageId());
// "FAPI00134035C3000004"

dump($sentMessage->getInfo());
// [
//    "success" => 1
//    "date" => "2025-09-18 14:14:01 +0300"
//    "data" => array:2 [
//      "messageID" => "FAPI00134035C3000004"
//      "sms" => array:3 [
//        "status" => "ACCEPTD"
//        "date" => "2025-09-18 14:14:01 +0300"
//        "cost" => "0.979"
//      ]
//    ]
//  ]
```

Example if you are using Symfony Full-Stack Framework
-----------

```
# config/packages/notifier.yaml
SMSFLY_DSN=smsfly://AUTHKEY@default?from=InfoCenter
```

where:
- `AUTHKEY` is your SMS-fly auth key
- `FROM` is your sender name, should be alpha-numeral

```php
use Symfony\Component\Notifier\Message\SmsMessage;
use SolParts\SymfonySmsFlyNotifier\SmsFlyOptions;
use Symfony\Component\Notifier\TexterInterface;

/** @var TexterInterface $texter */
$texter = /*...*/;

$sms = new SmsMessage('+380771234567', 'My sms message');
$texter->send($sms);
```

Adding custom Options to a Message
---------------------------

With a SMS-Fly Message, you can use the `SmsFlyOptions` class to add
[message options](https://github.com/sol-parts/symfony-smsfly-notifier/blob/7.3/SmsFlyOptions.php).

```php
use SolParts\SymfonySmsFlyNotifier\SmsFlyOptions;
use Symfony\Component\Notifier\Message\SmsMessage;

$options = (new SmsFlyOptions())
    // Message sending channels.
    // Available values are viber, sms. If you specify multiple channels, the message is sent to the channels in order of priority.
    // The message is sent to the next channel if delivery to the previous one was not successful.
    // If not specified, ['sms'] will be used.
    ->channels(['viber', 'sms']) 
    ->viber_source('Promo') // The Viber sender name, if not specified, will be the same as for SMS.
    ->viber_text('🎁 A special gift from Example Corp is here') // Optional field, Viber message text up to 1000 characters long. If not specified, the SMS message text will be used.
    ->viber_button_caption('Go to site') // Optional field
    ->viber_button_url('https://example.com/') // Optional field
    ->viber_image('https://example.com/image.jpg') // Optional field
    ->ttl(1440)
    ;

/** @var SmsMessage $sms */
$sms = /*...*/;
$sms->options($options);
```

Resources
---------

*  [Main Symfony SMS Channel Notifications](https://symfony.com/doc/current/notifier.html#sms-channel)

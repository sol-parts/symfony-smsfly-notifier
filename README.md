Symfony SMS-fly Notifier
=================

Provides [SMS-fly](https://sms-fly.ua/) integration for Symfony Notifier.

DSN example
-----------

```
SMSFLY_DSN=smsfly://AUTHKEY@default?from=InfoCenter
```

where:
- `AUTHKEY` is your SMS-fly auth key
- `FROM` is your sender name, should be alpha-numeral

Adding Options to a Message
---------------------------

With a SMS-Fly Message, you can use the `SmsFlyOptions` class to add
[message options](https://sms-fly.ua/public/api.v2.4_uk.pdf).

```php
use Symfony\Component\Notifier\Message\SmsMessage;
use SolParts\SymfonySmsFlyNotifier\SmsFlyOptions;

$sms = new SmsMessage('+380771234567', 'My sms message');

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

// Add the custom options to the sms message and send the message
$sms->options($options);

$texter->send($sms);
```

Resources
---------

*  [Main Symfony SMS Channel Notifications](https://symfony.com/doc/current/notifier.html#sms-channel)

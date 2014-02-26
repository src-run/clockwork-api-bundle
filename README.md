# ScribeClockworkBundle

Symfony bundle to handle interaction with clockworksms.com for SMS messaging

## Requirements

- You must register for an account and recieve an API key from http://www.clockworksms.com/
- PHP5
- Curl PHP module

## Installation

Add the following to your composer.json file in the `require` block:

```
"scribe/clockwork-bundle": "dev-master"
```

Issue a `composer.phar update` to download your new package (this command will also update any outdated packages).

To register the bundle within your application, you must add the bundle to the `AppKernel.php` file within the `$bundles` array:

```
new Scribe\ClockworkBundle\ScribeClockworkBundle()
```

## Configuration

Edit your symfony config.yml file and add, at a minimum, the following lines:

```
scribe_clockwork:
  api_key: your-api-key-goes-here
```

You may optionally configure the following items as well (show with their default values):

```
scribe_clockwork:
  api_key: your-api-key-goes-here
  allow_long_messages: false
  truncate_long_messages: true
  from_address: 'ScribeClock'
  enable_ssl: true
  invalid_character_action: replace_character
  log_activity: false
```

## Usage

Assuming you have completed the installation and configuration, you can send a text message by requesting the `scribe.clockwork` service and using the `send` method.

```
$cw = $container->get('scribe.clockwork');
$message_id = $cw->send('12223334444', 'Your text message goes here');
```

More complex usage, including sending multiple messages, checking your balance, credit, and API key validity are also available.

## License

Please see the LICENSE file distributed with this software.
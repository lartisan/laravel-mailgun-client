# Laravel Mailgun Client

A clean Laravel HTTP client for the Mailgun API with mailing list management and newsletter sending support.

## Installation

```bash
composer require lartisan/laravel-mailgun-client
```

The package auto-discovers its service provider and the `Mailgun` facade.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=mailgun-config
```

Add the following to your `.env` file:

```env
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=key-xxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAILGUN_API_KEY=key-xxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAILGUN_SENDING_API_KEY=key-xxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAILGUN_ENDPOINT=https://api.mailgun.net
MAILGUN_SUBSCRIBERS_LIST=newsletter@your-domain.com
```

> Use `https://api.eu.mailgun.net` as the endpoint if your domain is registered in the EU region.

## Usage

### Via Facade

```php
use Lartisan\MailgunClient\Facades\Mailgun;

// Send a custom email
Mailgun::send([
    'to'      => 'recipient@example.com',
    'subject' => 'Hello!',
    'html'    => '<p>Hello, world!</p>',
]);

// Send a newsletter (uses the configured subscribers_list as the default "to")
Mailgun::sendNewsletter('recipient@example.com', 'My Newsletter', '<p>Content</p>');

// Fetch all mailing lists
$lists = Mailgun::fetchMailingLists();

// Add a member to a mailing list (unsubscribed)
Mailgun::addMemberToMailingList('user@example.com', 'list@your-domain.com');

// Subscribe a member
Mailgun::subscribeMemberToMailingList('user@example.com', 'list@your-domain.com');

// Unsubscribe a member
Mailgun::unsubscribeMemberFromMailingList('user@example.com', 'list@your-domain.com');

// Get all registered webhooks
$webhooks = Mailgun::getAllWebhooks();
```

### Via Dependency Injection

```php
use Lartisan\MailgunClient\Mailgun;

class NewsletterService
{
    public function __construct(
        protected Mailgun $mailgun,
    ) {}

    public function send(string $email, string $subject, string $html): void
    {
        $this->mailgun->sendNewsletter($email, $subject, $html);
    }
}
```

## Value Objects

Mailing list data is returned as `Lartisan\MailgunClient\ValueObjects\MailingList` instances:

```php
$lists = Mailgun::fetchMailingLists();

foreach ($lists as $list) {
    echo $list->name;           // The list display name
    echo $list->address;        // The list email address
    echo $list->members_count;  // Total member count
    echo $list->access_level;   // readonly, members, everyone
}
```

## Testing

```php
use Lartisan\MailgunClient\Facades\Mailgun;

it('sends a newsletter', function () {
    Mailgun::shouldReceive('sendNewsletter')
        ->once()
        ->with('user@example.com', 'Subject', '<p>Content</p>');

    // call your code...
});
```

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.


<?php

namespace Lartisan\MailgunClient\Facades;

use Illuminate\Support\Facades\Facade;
use Lartisan\MailgunClient\Mailgun as MailgunManager;

/**
 * @method static \Illuminate\Support\Collection|null fetchMailingLists()
 * @method static mixed addMemberToMailingList(string $email, string $mailingList)
 * @method static mixed subscribeMemberToMailingList(string $email, string $mailingList)
 * @method static int unsubscribeMemberFromMailingList(string $email, string $mailingList)
 * @method static array|null getAllWebhooks()
 * @method static array|null send(array $data)
 * @method static void sendNewsletter(string $to, string $subject, string $html)
 *
 * @see MailgunManager
 */
class Mailgun extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mailgun';
    }
}

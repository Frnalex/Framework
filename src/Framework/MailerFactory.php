<?php

namespace Framework;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class MailerFactory
{
    public function __invoke(): Mailer
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        return new Mailer($transport);
    }
}

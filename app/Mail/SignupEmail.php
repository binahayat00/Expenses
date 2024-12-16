<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class SignupEmail
{
    public function __construct(
        private readonly Config $config
    )
    {

    }
    public function send(string $to): void
    {
        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($to)
            ->subject('Welcome to Expenses!')
            ->htmlTemplate('emails/signup.html.twig')
            ->context([]);
    }
}

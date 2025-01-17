<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config;
use App\SignedUrl;
use App\Entity\User;
use Slim\Interfaces\RouteParserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

class TwoFactorAuthEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $renderer,
        private readonly RouteParserInterface $routeParser,
        private readonly SignedUrl $signedUrl
    ) {

    }
    public function send(User $user): void
    {
        $email = $user->getEmail();
        $expirationDate = new \DateTime('+30 minutes');
        $activationLink = $this->signedUrl->fromRoute(
            'verify',
            ['id' => $user->getId(), 'hash' => sha1($email)],
            $expirationDate
        );

        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($email)
            ->subject('Your Expenses Verification Code!')
            ->htmlTemplate('emails/two_factor.html.twig')
            ->context(
                [
                    'activationLink' => $activationLink,
                    'expirationDate' => $expirationDate,
                ]
            );

        $this->renderer->render($message);

        $this->mailer->send($message);
    }
}

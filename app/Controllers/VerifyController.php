<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\UserProviderServiceInterface;
use App\Entity\User;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VerifyController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly UserProviderServiceInterface $userProviderService
        )
    {

    }
    public function index(ResponseInterface $response): ResponseInterface
    {
        return $this->twig->render($response, 'auth/verify.twig');
    }

    public function verify(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        if (! hash_equals((string) $user->getId(), $args['id']) || hash_equals(sha1($user->getEmail()), $args['hash']))
        {
            throw new \RuntimeException('Verification faild');
        }

        if(! $user->getVerifiedAt()) 
        {
            $this->userProviderService->verifyUser($user);
        }
        
        return $response->withHeader('Location','/')->withStatus(302);
    }
}

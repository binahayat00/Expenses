<?php

declare(strict_types=1);

namespace App\Controllers;

use Slim\Views\Twig;
use App\ResponseFormatter;
use App\Enum\AuthAttemptStatus;
use App\Contracts\AuthInterface;
use App\DataObjects\RegisterUserData;
use App\Exception\ValidationException;
use App\Contracts\RequestValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use App\RequestValidators\UserLoginRequestValidator;
use App\RequestValidators\RegisterUserRequestValidator;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\RequestValidators\TwoFactorLoginRequestValidator;

class AuthController
{
    public function __construct(
        private readonly Twig $twig, 
        private readonly AuthInterface $auth,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter $responseFormatter
        )
    {

    }

    public function loginView(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/login.twig');
    }

    public function registerView(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(RegisterUserRequestValidator::class)->validate($request->getParsedBody());

        $this->auth->register(
            new RegisterUserData($data['name'],$data['email'],$data['password'])
        );

        return $response->withHeader('Location','/')->withStatus(302);
    }

    public function logIn(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserLoginRequestValidator::class)->validate($request->getParsedBody());
        $status = $this->auth->attemptLogin($data);

        if($status === AuthAttemptStatus::FAILED)
        {
            throw new ValidationException(['password' => ['You have entered an invalid username or password']]);
        }

        if($status === AuthAttemptStatus::TWO_FACTOR_AUTH)
        {
            return $this->responseFormatter->asJson(
                $response, 
                ['two_factor' => true]
            );
        }
        
        return $this->responseFormatter->asJson($response, []);
    }

    public function logOut(Request $request, Response $response): Response
    {
        $this->auth->logOut();

        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function twoFactorLogin(Request $request, Response $response):Response     
    {
        $data = $this->requestValidatorFactory->make(TwoFactorLoginRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        if (! $this->auth->attemptTwoFactorLogin($data))
        {
            throw new ValidationException(['code' => ['Invalid Code!']]);
        }

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App;
use App\Contracts\SessionInterface;
use App\DataObjects\SessionConfig;
use App\Exception\SessionException;

class Session implements SessionInterface
{
    public function __construct(private readonly SessionConfig $options)
    {

    }

    public function start(): void
    {
        if($this->isActive())
        {
            throw new SessionException('Session has already been started!');
        }

        if(headers_sent($filename, $line))
        {
            throw new SessionException("Headers have already sent by $filename : $line");
        }

        session_set_cookie_params([
            'secure' => $this->options->secure ?? true, 
            'httponly' => $this->options->httponly ?? true, 
            'samesite' => $this->options->samesite->value ?? 'lax'
        ]);

        if(! empty($this->options->name))
        {
            session_name($this->options->name);
        }

        if(! session_start())
        {
            throw new SessionException('Unable to start the Session !');
        }
    }

    public function save(): void
    {
        session_write_close();
    }

    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function forget(string $name): void
    {
        unset($_SESSION[$name]);
    }

    public function regenerate(): bool
    {
        return session_regenerate_id();
    }

    public function put(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->has($name) ? $_SESSION[$name] : $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $_SESSION);
    }

}

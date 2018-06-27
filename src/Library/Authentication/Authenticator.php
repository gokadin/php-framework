<?php

namespace Library\Authentication;

use Firebase\JWT\JWT;
use Exception;

class Authenticator
{
    private const JWT_SECRET_ENV_KEY = 'JWT_SECRET_KEY';
    private const JWT_ENCODE_ALGORITHM = 'SHA256';

    /**
     * @var array
     */
    private $models;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * Authenticator constructor.
     *
     * @param array $config
     * @throws AuthenticationException
     */
    public function __construct(array $config)
    {
        $this->readConfig($config);
        $this->readSecretKey();
    }

    /**
     * @param array $config
     */
    private function readConfig(array $config)
    {
        $this->models = $config['models'];
    }

    private function readSecretKey()
    {
        $this->secretKey = getenv(self::JWT_SECRET_ENV_KEY);
        if (!$this->secretKey)
        {
            throw new AuthenticationException('JWT secret key not found. Set it in you .env.* file');
        }
    }

    public function shouldAuthenticate(string $uri)
    {
        return true;
    }

    public function authenticate(string $encodedJwt, string $uri)
    {
        try
        {
            $token = JWT::decode($encodedJwt, $this->secretKey, [self::JWT_ENCODE_ALGORITHM]);
        }
        catch (Exception $e)
        {
            return false;
        }
    }
}
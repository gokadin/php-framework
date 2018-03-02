<?php

namespace Library\Authentication;

use Library\Http\Middleware;
use Library\Http\Response;

class Authenticate extends Middleware
{
    private const AUTHORIZATION_HEADER_KEY = 'Authorization';

    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * Authenticate constructor.
     *
     * @param Authenticator $authenticator
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @return bool|Response
     */
    public function handle()
    {
        if (!$this->authenticator->shouldAuthenticate($this->request->uri()))
        {
            return true;
        }

        $encodedJwt = $this->extractEncodedJwt();
        if (!$encodedJwt)
        {
            return new Response(Response::STATUS_UNAUTHORIZED);
        }

        $this->authenticator->authenticate($encodedJwt, $this->request->uri());

        return true;
    }

    /**
     * @return mixed
     */
    private function extractEncodedJwt()
    {
        if (!$this->hasAuthentication())
        {
            return false;
        }

        $header = $this->request->header(self::AUTHORIZATION_HEADER_KEY);
        list($jwt) = sscanf($header, 'Authorization: Bearer %s');
        return $jwt;
    }

    /**
     * @return bool
     */
    private function hasAuthentication(): bool
    {
        return $this->request->headerExists(self::AUTHORIZATION_HEADER_KEY);
    }
}
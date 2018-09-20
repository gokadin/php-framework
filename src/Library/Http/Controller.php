<?php

namespace Library\Http;

use Library\IscClient\IscClient;
use Library\Validation\ValidationResult;
use Library\Validation\Validator;

abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var IscClient
     */
    protected $isc;

    /**
     * @param array $rules
     * @return ValidationResult
     */
    protected function validate(array $rules): ValidationResult
    {
        return $this->validator->validate($this->request->all(), $rules);
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function respondOk($data = []): Response
    {
        return new Response(Response::STATUS_OK, $data);
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function respondBadRequest($data = []): Response
    {
        return new Response(Response::STATUS_BAD_REQUEST, $data);
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function respondNotFound($data = []): Response
    {
        return new Response(Response::STATUS_NOT_FOUND, $data);
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function respondInternalServerError($data = []): Response
    {
        return new Response(Response::STATUS_INTERNAL_SERVER_ERROR, $data);
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function respondUnauthorized($data = []): Response
    {
        return new Response(Response::STATUS_UNAUTHORIZED, $data);
    }

    /**
     * @param string $topic
     * @param string $action
     * @param array $payload
     */
    protected function dispatchEvent(string $topic, string $action, array $payload = [])
    {
        $this->isc->dispatchEvent($topic, $action, $payload);
    }

    /**
     * @param string $topic
     * @param string $action
     * @param array $payload
     */
    protected function dispatchCommand(string $topic, string $action, array $payload = [])
    {
        $result = $this->isc->dispatchCommand($topic, $action, $payload);
        return new Response($result['statusCode'], $result['payload']);
    }

    /**
     * @param string $topic
     * @param string $action
     * @param array $payload
     */
    protected function dispatchQuery(string $topic, string $action, array $payload = [])
    {
        $result = $this->isc->dispatchQuery($topic, $action, $payload);
        return new Response($result['statusCode'], $result['payload']);
    }
}

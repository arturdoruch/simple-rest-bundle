<?php

namespace ArturDoruch\SimpleRestBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class RequestErrorEvent extends AbstractEvent
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    public function __construct(\Exception $exception, Request $request)
    {
        $this->exception = $exception;
        $this->request = $request;
    }

    /**
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}

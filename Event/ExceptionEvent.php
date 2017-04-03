<?php

namespace ArturDoruch\SimpleRestBundle\Event;

use ArturDoruch\SimpleRestBundle\Error\Error;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ExceptionEvent extends Event
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var Error
     */
    private $error;

    /**
     * @var Request
     */
    private $request;

    public function __construct(\Exception $exception, Error $error, Request $request)
    {
        $this->exception = $exception;
        $this->error = $error;
        $this->request = $request;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
 
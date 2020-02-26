<?php

namespace ArturDoruch\SimpleRestBundle\EventListener;

use ArturDoruch\SimpleRestBundle\Error\Error;
use ArturDoruch\SimpleRestBundle\Error\ErrorException;
use ArturDoruch\SimpleRestBundle\Error\ErrorResponse;
use ArturDoruch\SimpleRestBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ExceptionListener
{
    /**
     * @var array
     */
    private $restPaths;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param KernelInterface $kernel
     * @param array $restPaths
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $restPaths, KernelInterface $kernel, EventDispatcherInterface $dispatcher)
    {
        $this->restPaths = $restPaths;
        $this->kernel = $kernel;
        $this->dispatcher = $dispatcher;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->isRestPath($event->getRequest())) {
            return;
        }

        // Call listeners to modify current event.
        $this->dispatcher->dispatch(Events::KERNEL_EXCEPTION, $event);

        $exception = $event->getException();
        $statusCode = $this->getStatusCode($exception);
        $debugEnvironment = in_array($this->kernel->getEnvironment(), ['dev', 'test']);

        // Throw errors with code 500 in debug mode
        if ($statusCode === 500 && $debugEnvironment) {
            /*if ($exception instanceof FatalThrowableError) {
                $exception = FlattenException::create($exception, $statusCode);
            }*/

            return;
        }

        if ($exception instanceof ErrorException) {
            $error = $exception->getError();
        } else {
            $message = '';
            $type = null;
            // If it's an HttpException message (e.g. for 404, 403), we'll say as a rule
            // that the exception message is safe for the client. Otherwise, it could be
            // some sensitive low-level exception, which should NOT be exposed.
            if ($exception instanceof HttpException) {
                if ($debugEnvironment) {
                    $message = $exception->getMessage();
                }
                $type = Error::TYPE_REQUEST;
            }

            $error = new Error($statusCode, $type, $message);
        }

        $response = new ErrorResponse($error);
        $event->setResponse($response);
    }

    private function getStatusCode(\Exception $exception)
    {
        return method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * @todo Maybe move to RequestHelper class.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isRestPath(Request $request)
    {
        $requestPath = $request->getPathInfo();

        foreach ($this->restPaths as $restPath) {
            if (preg_match('~'.$restPath.'~', $requestPath)) {
                return true;
            }
        }

        return false;
    }
}
 
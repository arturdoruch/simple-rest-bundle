<?php

namespace ArturDoruch\SimpleRestBundle\EventListener;

use ArturDoruch\SimpleRestBundle\Api\ApiProblem;
use ArturDoruch\SimpleRestBundle\Event\RequestErrorEvent;
use ArturDoruch\SimpleRestBundle\Http\RequestErrorEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ExceptionListener
{
    /**
     * @var array
     */
    private $apiPaths;

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
     * @param array $apiPaths
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $apiPaths, KernelInterface $kernel, EventDispatcherInterface $dispatcher)
    {
        $this->apiPaths = $apiPaths;
        $this->kernel = $kernel;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Prepares HTTP response based on exception properties.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->isApiPath($event->getRequest())) {
            return;
        }

        $exception = $event->getException();

        $requestErrorEvent = new RequestErrorEvent($exception, $event->getRequest());
        $this->dispatcher->dispatch(RequestErrorEvents::PRE_CREATE_RESPONSE, $requestErrorEvent);

        $statusCode = (int) $this->getStatusCode($exception);
        $debugEnvironment = in_array($this->kernel->getEnvironment(), ['dev', 'test']);

        // Throw errors with code 500 in debug mode
        if ($statusCode === 500 && $debugEnvironment) {
            /*if ($exception instanceof FatalThrowableError) {
                $exception = FlattenException::create($exception, $statusCode);
            }*/

            return;
        }

        $type = null;
        $message = '';
        $details = [];
        $headers = [];

        if ($exception instanceof \ArturDoruch\SimpleRestBundle\Http\Exception\HttpExceptionInterface) {
            $type = $exception->getType() ?? ApiProblem::TYPE_REQUEST;
            $message = $exception->getMessage();
            $details = $exception->getDetails();
        } elseif ($exception instanceof HttpExceptionInterface) {
            // If it's an HttpException message (e.g. for 404, 403), we'll say as a rule
            // that the exception message is safe for the client. Otherwise, it could be
            // some sensitive low-level exception, which should NOT be exposed.
            if ($debugEnvironment) {
                $message = $exception->getMessage();
            }
            $type = ApiProblem::TYPE_REQUEST;
            $headers = $exception->getHeaders();
        }

        $apiProblem = new ApiProblem($statusCode, $type, $message, $details);

        $headers['Content-Type'] = 'application/json'; // 'application/problem+json'
        $response = new JsonResponse($apiProblem->toArray(), $statusCode, $headers);

        $event->setResponse($response);

        $requestErrorEvent->setResponse(clone $response);
        $this->dispatcher->dispatch(RequestErrorEvents::POST_CREATE_RESPONSE, $requestErrorEvent);
    }


    private function getStatusCode(\Exception $exception)
    {
        return method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
    }


    private function isApiPath(Request $request): bool
    {
        $requestPath = $request->getPathInfo();

        foreach ($this->apiPaths as $restPath) {
            if (preg_match('~'.$restPath.'~', $requestPath)) {
                return true;
            }
        }

        return false;
    }
}
 
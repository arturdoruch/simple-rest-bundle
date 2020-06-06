<?php

namespace ArturDoruch\SimpleRestBundle\EventListener;

use ArturDoruch\SimpleRestBundle\Api\ApiProblem;
use ArturDoruch\SimpleRestBundle\Api\ApiProblemTypes;
use ArturDoruch\SimpleRestBundle\Event\RequestErrorEvent;
use ArturDoruch\SimpleRestBundle\Http\RequestErrorEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $kernelDebug;


    public function __construct(array $apiPaths, EventDispatcherInterface $dispatcher, bool $kernelDebug)
    {
        $this->apiPaths = $apiPaths;
        $this->dispatcher = $dispatcher;
        $this->kernelDebug = $kernelDebug;
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

        // Throw errors with code 500 in debug mode
        if ($statusCode === 500 && $this->kernelDebug) {
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
            $type = $exception->getType() ?? ApiProblemTypes::REQUEST;
            $message = $exception->getMessage();
            $details = $exception->getDetails();
        } elseif ($exception instanceof HttpExceptionInterface) {
            if ($statusCode < 500) {
                $message = $exception->getMessage();
            }

            if ($exception instanceof AccessDeniedHttpException) {
                $type = ApiProblemTypes::AUTHORIZATION;
            } elseif ($exception instanceof UnauthorizedHttpException) {
                $type = ApiProblemTypes::AUTHENTICATION;
            } else {
                $type = ApiProblemTypes::REQUEST;
            }

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

        foreach ($this->apiPaths as $apiPath) {
            if (preg_match('~'.$apiPath.'~', $requestPath)) {
                return true;
            }
        }

        return false;
    }
}
 
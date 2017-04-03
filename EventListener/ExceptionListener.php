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
 * The class created based on information from course https://knpuniversity.com/screencast/symfony-rest2.
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

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Act only on api paths.
        if (!$this->isRequestedApiPath($event->getRequest())) {
            return;
        }

        // Call listeners to modify current event.
        $this->dispatcher->dispatch(Events::KERNEL_EXCEPTION, $event);

        $exception = $event->getException();
        $statusCode = $this->getStatusCode($exception);

        // Allow to throw errors with code 500 in debug mode
        if ($statusCode == 500 && in_array($this->kernel->getEnvironment(), array('dev', 'test'))) {
            return;
        }

        if ($exception instanceof ErrorException) {
            $error = $exception->getError();
        } else {
            $error = new Error($statusCode);

            // If it's an HttpException message (e.g. for 404, 403), we'll say as a rule
            // that the exception message is safe for the client. Otherwise, it could be
            // some sensitive low-level exception, which should NOT be exposed.
            if ($exception instanceof HttpException) {
                $error->addData('detail', $exception->getMessage());
            }
        }

        $response = new ErrorResponse($error);
        // Send the modified response object to the event
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
    private function isRequestedApiPath(Request $request)
    {
        $requestPath = $request->getPathInfo();

        foreach ($this->apiPaths as $apiPath) {
            if (strpos($requestPath, $apiPath) === 0) {
                return true;
            }
        }

        return false;
    }
}
 
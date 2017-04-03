<?php

namespace ArturDoruch\SimpleRestBundle;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Events
{
    /**
     * Events occurs when api endpoint has been requested and exception was thrown.
     * Listeners will receive Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent
     * event object.
     */
    const KERNEL_EXCEPTION = 'ad.simple_rest.kernel_exception';
}
 
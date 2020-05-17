<?php

namespace ArturDoruch\SimpleRestBundle\Http;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class RequestErrorEvents
{
    /**
     * The event is dispatched before creating the HTTP response, while API endpoint
     * has been requested and an exception occurred.
     *
     * Allows to modify exception.
     *
     * @Event("ArturDoruch\SimpleRestBundle\Event\RequestErrorEvent").
     */
    const PRE_CREATE_RESPONSE = 'artur_doruch_simple_rest.request_error.pre_create_response';

    /**
     * The event is dispatched after creating the HTTP response, while API endpoint
     * has been requested and an exception occurred.
     *
     * Provides access to the HTTP response.
     *
     * @Event("ArturDoruch\SimpleRestBundle\Event\RequestErrorEvent").
     */
    const POST_CREATE_RESPONSE = 'artur_doruch_simple_rest.request_error.post_create_response';
}
 
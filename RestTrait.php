<?php

namespace ArturDoruch\SimpleRestBundle;

use ArturDoruch\SimpleRestBundle\Http\Exception\HttpException;
use ArturDoruch\SimpleRestBundle\Http\RequestHandler;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST common functions.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
trait RestTrait
{
    /**
     * Processes request with the form.
     *
     * @param Request $request
     * @param FormInterface $form
     * @param bool $expectedJsonRequest Whether the request expected content type is type of "application/json".
     * param bool $validateParameters Whether to validate the request (query, form or JSON body) parameters.
     *
     * @return mixed The form data.
     * @throws HttpException when submitted form is not valid, request contains invalid parameters.
     */
    protected function handleRequest(Request $request, FormInterface $form, $expectedJsonRequest = false)
    {
        return RequestHandler::handle($request, $form, $expectedJsonRequest);
    }

    /**
     * @param Request $request
     *
     * @return array|null Decoded request JSON content.
     * @throws \InvalidArgumentException when request JSON body is invalid.
     */
    protected function getRequestJsonData(Request $request)
    {
        return HttpUtils::getRequestJsonData($request);
    }

    /**
     * Creates HTTP response.
     * As default response has header "Content-Type: application/json".
     *
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     *
     * @return Response
     */
    protected function createResponse($data = '', $statusCode = 200, array $headers = [])
    {
        return HttpUtils::createResponse($data, $statusCode, $headers);
    }

    /**
     * Gets formatted form error messages.
     *
     * @param FormInterface $form
     * @param bool $flatten Whether to flatten error messages multidimensional array into simple array
     *                      with key (form names path) value (messages concatenated with ";") pairs.
     *                      E.g. "name.formLevel2Name" => "message1; message2"
     *
     * @return array
     */
    protected function getFormErrorMessages(FormInterface $form, bool $flatten = false): array
    {
        return FormErrorHelper::getMessages($form, $flatten);
    }

    /**
     * Serializes data into JSON format.
     *
     * @param mixed $data
     * @param SerializationContext $context
     *
     * @return string
     */
    protected function serialize($data, SerializationContext $context = null)
    {
        return SerializerAdapter::serialize($data, $context);
    }

    /**
     * Converts object into array.
     *
     * @param object $object
     * @param SerializationContext|null $context
     *
     * @return array
     */
    protected function normalize($object, SerializationContext $context = null): array
    {
        return SerializerAdapter::normalize($object, $context);
    }
}

<?php

namespace ArturDoruch\SimpleRestBundle\Http;

use ArturDoruch\SimpleRestBundle\Http\Exception\HttpException;
use ArturDoruch\SimpleRestBundle\FormErrorHelper;
use ArturDoruch\SimpleRestBundle\HttpUtils;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class RequestHandler
{
    private static $flattenFormErrorMessages = false;

    public static function flattenFormErrorMessages()
    {
        self::$flattenFormErrorMessages = true;
    }

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
    public static function handle(Request $request, FormInterface $form, $expectedJsonRequest = false/*, $validateParameters = true*/)
    {
        static $isPostMethod = ['POST', 'PUT', 'PATCH'];
        $requestMethod = $request->getMethod();

        if ($form->getConfig()->getMethod() === 'GET' || !in_array($requestMethod, $isPostMethod)) {
            $data = $request->query->all();
            $dataSource = 'query ';
        } elseif ($expectedJsonRequest === true) {
            $data = self::getJsonData($request);
            $dataSource = 'body JSON ';
        } else {
            $data = $request->request->all();
            $dataSource = 'form ';
        }

        $form->submit($data, $requestMethod !== 'PATCH');

        if (/*$validateParameters === true && */!$form->isValid()) {
            throw new HttpException(
                sprintf('Invalid request %sparameters.', $dataSource),
                FormErrorHelper::getMessages($form, self::$flattenFormErrorMessages)
            );
        }

        return $form->getData();
    }


    private static function getJsonData(Request $request): ?array
    {
        try {
            self::validateContentType($request, 'json');

            return HttpUtils::getRequestJsonData($request);
        } catch (\InvalidArgumentException $e) {
            throw new HttpException($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param string $contentType The expected content type.
     */
    private static function validateContentType(Request $request, string $contentType)
    {
        $type = $request->headers->get('Content-Type');

        if (!preg_match('/^application\/.*'.$contentType.'/', $type)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid request content type. Expected "application/%s", but got "%s".', $contentType, $type
            ));
        }
    }
}

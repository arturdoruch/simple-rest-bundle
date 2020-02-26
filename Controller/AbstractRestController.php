<?php

namespace ArturDoruch\SimpleRestBundle\Controller;

use ArturDoruch\SimpleRestBundle\Error\Error;
use ArturDoruch\SimpleRestBundle\Error\ErrorException;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class with useful REST functions.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
abstract class AbstractRestController extends Controller
{
    /**
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     *
     * @return Response
     */
    protected function createResponse($data = '', $statusCode = 200, array $headers = [])
    {
        if (!is_string($data)) {
            $data = $this->serialize($data);
        }

        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        return new Response($data, $statusCode, $headers);
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
        $context = $context ?: new SerializationContext();
        $context->setSerializeNull(true);
        $context->enableMaxDepthChecks();

        return $this->get('jms_serializer')->serialize($data, 'json', $context);
    }

    /**
     * @param int    $statusCode
     * @param string $type
     * @param string $message
     * @param array  $data
     *
     * @return ErrorException
     */
    protected function createErrorException($statusCode = 400, $type = null, $message = null, array $data = [])
    {
        return ErrorException::create($statusCode, $type, $message, $data);
    }

    /**
     * @param FormInterface $form
     *
     * @return ErrorException
     */
    protected function createFormValidationErrorException(FormInterface $form)
    {
        return ErrorException::create(400, Error::TYPE_REQUEST, 'Invalid request parameters.', [
            'details' => $this->getFormErrors($form)
        ]);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     */
    protected function processForm(Request $request, FormInterface $form)
    {
        $data = $this->getRequestData($request);
        $clearMissing = $request->getMethod() != 'PATCH';

        $form->submit($data, $clearMissing);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestData(Request $request)
    {
        $contentType = $request->headers->get('Content-Type');

        if (preg_match('/^application\/.*json/', $contentType)) {
            return $this->getRequestJsonData($request);
        }

        return $this->getRequestRequestData($request);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function getRequestJsonData(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            $message = (!$request->getContent() ? 'Missing' : 'Invalid') . ' request JSON body.';

            throw $this->createErrorException(400, Error::TYPE_REQUEST, $message);
        }

        return $data;
    }

    /**
     * Gets request POST, PUT, PATCH body parameters.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestRequestData(Request $request)
    {
        return $request->request->all();
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getFormErrors(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $message = $this->getErrorMessage($error);
            $extraData = $form->getExtraData();

            if (!empty($extraData)) {
                $message .= sprintf(' Submitted extra fields: "%s".', join('", "', array_keys($extraData)));
            }

            $errors[] = $message;
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getFormErrors($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * @param FormError $error
     * @return string
     */
    private function getErrorMessage(FormError $error)
    {
        if (null !== $error->getMessagePluralization()) {
            return $this->get('translator')->transChoice($error->getMessageTemplate(), $error->getMessagePluralization(), $error->getMessageParameters(), 'validators');
        }

        return $this->get('translator')->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }
}

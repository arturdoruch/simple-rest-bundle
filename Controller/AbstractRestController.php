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
     * Serializes data into json format.
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
     * @param mixed $data
     * @param int   $statusCode
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

        $response = new Response($data, $statusCode, $headers);
        $response->setMaxAge(600);
        $response->setPublic();

        return $response;
    }

    /**
     * @param int    $statusCode
     * @param string $type The error type. One of ArturDoruch\SimpleRestBundle\Error\Error::Type_* constant.
     * @param string $message The error custom message.
     *
     * @return ErrorException
     */
    protected function createErrorException($statusCode = 400, $type = null, $message = null)
    {
        return ErrorException::create($statusCode, $type, $message);
    }

    /**
     * @param FormInterface $form
     *
     * @return ErrorException
     */
    protected function createFormValidationErrorException(FormInterface $form)
    {
        $error = new Error(400, Error::TYPE_VALIDATION, null, [
                'errors' => $this->getFormErrors($form)
            ]);

        return new ErrorException($error);
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
     * @todo Gets request data from request or content based on request content type: application/json or application/x-www-form-urlencoded.
     *
     * @param Request $request
     * @return array
     */
    protected function getRequestData(Request $request)
    {
        $data = $request->request->all();

        if (empty($data) && $request->getContent()) {
            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                throw $this->createErrorException(400, Error::TYPE_INVALID_REQUEST_BODY_FORMAT);
            }
        }

        return $data;
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getFormErrors(FormInterface $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            //$message = $error->getMessage();
            $message = $this->getErrorMessage($error);
            $extraData = $form->getExtraData();

            if (!empty($extraData)) {
                $message .= ' Submitted extra fields: \'' . join('\', \'', array_keys($extraData)) . '\'.';
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

    /*
     * This code has been taken from JMSSerializer.
     */
    /*protected function _getFormErrors(FormInterface $data)
    {
        $form = $errors = array();

        foreach ($data->getErrors() as $error) {
            $errors[] = $this->getErrorMessage($error);
        }

        if ($errors) {
            $form['errors'] = $errors;
        }

        $children = array();

        foreach ($data->all() as $child) {
            if ($child instanceof FormInterface) {
                $children[$child->getName()] = $this->getFormErrors($child);
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        return $form;
    }*/
}

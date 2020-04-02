<?php

namespace ArturDoruch\SimpleRestBundle;

use ArturDoruch\SimpleRestBundle\Error\Error;
use ArturDoruch\SimpleRestBundle\Error\ErrorException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * REST helper methods.
 *
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
trait RestTrait
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param Serializer $serializer
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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

        return $this->serializer->serialize($data, 'json', $context);
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
     * @param string $message
     *
     * @return ErrorException
     */
    protected function createFormValidationErrorException(FormInterface $form, $message = null)
    {
        return ErrorException::create(400, Error::TYPE_REQUEST, $message ?: 'Invalid request parameters.', [
            'details' => $this->getFormErrors($form)
        ]);
    }

    /**
     * @deprecated Use handleRequest() method instead.
     *
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
     * @param FormInterface $form
     * @param bool $expectedRequestJsonBody
     * @param bool $validateParameters Whether to validate the request parameters.
     *
     * @return mixed The form data.
     */
    protected function handleRequest(Request $request, FormInterface $form, $expectedRequestJsonBody = false, $validateParameters = true)
    {
        static $isPostMethod = ['POST', 'PUT', 'PATCH'];
        $requestMethod = $request->getMethod();

        if ($form->getConfig()->getMethod() === 'GET' || !in_array($requestMethod, $isPostMethod)) {
            $data = $request->query->all();
            $dataSource = 'query ';
        } elseif ($expectedRequestJsonBody === true) {
            $this->validateRequestContentType($request, 'json');
            $data = $this->getRequestJsonData($request);
            $dataSource = 'body JSON ';
        } else {
            $data = $request->request->all();
            $dataSource = 'form ';
        }

        $form->submit($data, $requestMethod !== 'PATCH');

        if ($validateParameters === true && !$form->isValid()) {
            throw $this->createFormValidationErrorException($form, sprintf('Invalid request %sparameters.', $dataSource));
        }

        return $form->getData();
    }

    /**
     * @deprecated
     *
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

        return $request->request->all();
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function getRequestJsonData(Request $request)
    {
        if (null !== $data = json_decode($request->getContent(), true)) {
            return $data;
        }

        throw $this->createErrorException(400, Error::TYPE_REQUEST, 'Invalid request JSON body.');
    }

    /**
     * @param Request $request
     * @param string $contentType
     */
    private function validateRequestContentType(Request $request, $contentType)
    {
        $type = $request->headers->get('Content-Type');

        if (!preg_match('/^application\/.*'.$contentType.'/', $type)) {
            throw $this->createErrorException(400, Error::TYPE_REQUEST, sprintf(
                'Invalid request content type. Expected "%s", but got "%s".', $contentType, $type
            ));
        }
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

            if (!$form->getExtraData() && strpos($message, 'This form should not contain extra fields') === false) {
                $errors[] = $message;
            }
        }

        foreach ($form->getExtraData() as $name => $value) {
            $errors[$name][] = 'Unknown parameter.';
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
        if (!$this->translator) {
            return $error->getMessage();
        }

        if (null !== $error->getMessagePluralization()) {
            return $this->translator->transChoice($error->getMessageTemplate(), $error->getMessagePluralization(), $error->getMessageParameters(), 'validators');
        }

        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }
}

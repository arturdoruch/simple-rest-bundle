<?php

namespace ArturDoruch\SimpleRestBundle\Error;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Error
{
    const TYPE_VALIDATION = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';
    const TYPE_AUTHENTICATION = 'authentication_error';

    private static $messages = array(
        self::TYPE_VALIDATION => 'There was a validation error',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        self::TYPE_AUTHENTICATION => 'Invalid or missing authentication',
    );

    /**
     * @var string The error type
     */
    private $type;

    /**
     * @var string The error custom message
     */
    private $message;

    /**
     * @var string The HTTP status code
     */
    private $statusCode;

    /**
     * @var array The error extra data
     */
    private $data;

    /**
     * @param int     $statusCode The HTTP status code
     * @param string  $type       The error type. One of ArturDoruch\SimpleRestBundle\Error\Error::Type_* constant.
     * @param string  $message    Custom error message
     * @param array   $data       Error extra data.
     */
    public function __construct($statusCode, $type = null, $message = null, array $data = [])
    {
        $this->statusCode = $statusCode;
        $this->type = $type;
        $this->data = $data;

        if ($type === null) {
            // The default type is about:blank and the message should be the standard status code message
            $this->type = 'about:blank';
            $this->message = isset(Response::$statusTexts[$statusCode]) ? Response::$statusTexts[$statusCode] : '';
        } else {
            $this->message = $message ?: (isset(self::$messages[$type]) ? self::$messages[$type] : '');
        }
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Adds extra error data.
     *
     * @param string $name
     * @param mixed $value
     */
    public function addData($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array The Error properties as array.
     */
    public function toArray()
    {
        return array_merge($this->data, [
                'code' => $this->statusCode,
                'type' => $this->type,
                'message' => $this->message,
            ]);
    }
}
 
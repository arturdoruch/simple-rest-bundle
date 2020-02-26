<?php

namespace ArturDoruch\SimpleRestBundle\Error;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Error
{
    const TYPE_REQUEST = 'request_error';
    const TYPE_AUTHENTICATION = 'authentication_error';
    // message self::TYPE_AUTHENTICATION = 'Invalid or missing authentication'

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
     * @param string  $type       Error type.
     * @param string  $message    Error message.
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
        }

        $this->message = $message ?: (isset(Response::$statusTexts[$statusCode]) ? Response::$statusTexts[$statusCode] : '');
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
     * Adds error extra data.
     *
     * @param string $name
     * @param mixed  $value
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
        return array_merge([
            'code' => $this->statusCode,
            'type' => $this->type,
            'message' => $this->message,
        ], $this->data);
    }
}
 
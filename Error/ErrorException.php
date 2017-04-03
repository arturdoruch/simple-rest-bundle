<?php

namespace ArturDoruch\SimpleRestBundle\Error;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ErrorException extends HttpException
{
    /**
     * @var Error
     */
    private $error;

    public function __construct(Error $error, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->error = $error;

        parent::__construct($error->getStatusCode(), $error->getMessage(), $previous, $headers, $code);
    }

    /**
     * @param int $statusCode
     * @param string $type
     * @param string $message
     * @param array  $data
     *
     * @return ErrorException
     */
    public static function create($statusCode = 400, $type = null, $message = null, array $data = [])
    {
        $error = new Error($statusCode, $type, $message, $data);

        return new self($error);
    }

    /**
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }
}

<?php

namespace ArturDoruch\SimpleRestBundle\Http\Exception;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class HttpException extends \RuntimeException implements HttpExceptionInterface
{
    /**
     * @var int The HTTP status code.
     */
    private $statusCode;

    /**
     * @var array
     */
    private $details = [];

    /**
     * @var string The error type.
     */
    private $type;

    public function __construct(string $message, array $details = [], int $statusCode = 400, \Exception $previous = null)
    {
        $this->statusCode = $statusCode;
        $this->details = $details;
        parent::__construct($message, 0, $previous);
    }


    public function getStatusCode(): int
    {
        return $this->statusCode;
    }


    public function getDetails(): array
    {
        return $this->details;
    }


    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type The error type.
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }
}

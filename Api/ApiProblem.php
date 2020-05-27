<?php

namespace ArturDoruch\SimpleRestBundle\Api;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ApiProblem implements ApiProblemInterface
{
    /**
     * @var int The HTTP status code.
     */
    private $status;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $details = [];

    /**
     * @var array
     */
    private $extensions = [];


    public function __construct(int $status, ?string $type = null, string $message = '', array $details = [])
    {
        $this->status = $status;
        $this->type = $type;
        $this->message = $message ?: (Response::$statusTexts[$status] ?? '');
        $this->details = $details;

        // The default type is about:blank and the message should be the standard status code message.
        if ($type === null) {
            $this->type = 'about:blank';
        }
    }


    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status The HTTP status code.
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }


    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }


    public function getMessage(): string
    {
        return $this->message;
    }


    public function setDetails(array $details)
    {
        $this->details = $details;
    }


    public function getDetails(): array
    {
        return $this->details;
    }


    public function addExtension(string $name, $value)
    {
        $this->extensions[$name] = $value;

        return $this;
    }


    public function getExtensions(): array
    {
        return $this->extensions;
    }


    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }


    public function toArray(): array
    {
        $data = [
            'status' => $this->status,
            'type' => $this->type,
            'message' => $this->message,
        ];

        if ($this->details) {
            $data['details'] = $this->details;
        }

        return array_merge($data, $this->extensions);
    }
}
 
<?php

namespace ArturDoruch\SimpleRestBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class HttpUtils
{
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
    public static function createResponse($data = '', int $statusCode = 200, array $headers = []): Response
    {
        if (!is_string($data)) {
            $data = SerializerAdapter::serialize($data);
        }

        $headers = array_merge(['Content-Type' => 'application/json'], $headers);

        return new Response($data, $statusCode, $headers);
    }

    /**
     * @param Request $request
     * @param bool $throwExceptionWhenMissing
     *
     * @return array|null Decoded request JSON content.
     * @throws \InvalidArgumentException when request JSON body is invalid.
     */
    public static function getRequestJsonData(Request $request, bool $throwExceptionWhenMissing = false): ?array
    {
        if (!$content = $request->getContent()) {
            if ($throwExceptionWhenMissing) {
                throw new \InvalidArgumentException('Missing request JSON body.');
            }

            return null;
        }

        if (is_array($data = json_decode($request->getContent(), true))) {
            return $data;
        }

        throw new \InvalidArgumentException('Invalid request JSON body.');
    }
}

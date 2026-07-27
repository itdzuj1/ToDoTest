<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionRenderer
{
    public function render(Throwable $exception): ?JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json(['errors' => $exception->errors()], 422);
        }

        $error = match (true) {
            $exception instanceof AuthenticationException => [401, 'unauthenticated', 'Authentication is required.'],
            $exception instanceof AuthorizationException => [403, 'forbidden', 'This action is not allowed.'],
            $exception instanceof ModelNotFoundException => [404, 'not_found', 'The requested resource was not found.'],
            $exception instanceof TokenMismatchException => [419, 'csrf_token_mismatch', 'The CSRF token is invalid.'],
            $exception instanceof ThrottleRequestsException => [429, 'too_many_requests', 'Too many requests.'],
            $exception instanceof HttpExceptionInterface => $this->httpError($exception->getStatusCode()),
            default => null,
        };

        if ($error === null) {
            return null;
        }

        [$status, $code, $message] = $error;

        return response()->json([
            'code' => $code,
            'message' => $message,
        ], $status);
    }

    /**
     * @return array{int, string, string}
     */
    private function httpError(int $status): array
    {
        return match ($status) {
            404 => [404, 'not_found', 'The requested resource was not found.'],
            405 => [405, 'method_not_allowed', 'The HTTP method is not allowed.'],
            419 => [419, 'csrf_token_mismatch', 'The CSRF token is invalid.'],
            429 => [429, 'too_many_requests', 'Too many requests.'],
            default => [$status, 'http_error', 'The request could not be completed.'],
        };
    }
}

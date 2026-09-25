<?php

namespace App\EventListener;

use App\Exception\GuitarNotFoundException;
use App\Exception\ToneProfileNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener]
class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();

        $isNotFoundDomainException = $exception instanceof GuitarNotFoundException
            || $exception instanceof ToneProfileNotFoundException;

        $statusCode = match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $isNotFoundDomainException => 404,
            default => 500,
        };

        $payload = match (true) {
            $exception instanceof HttpExceptionInterface, $isNotFoundDomainException => ['error' => $exception->getMessage()],
            default => ['error' => 'Internal server error'],
        };

        $previous = $exception->getPrevious();
        
        if ($previous instanceof ValidationFailedException) {
            $errors = [];
            foreach ($previous->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $payload = ['errors' => $errors];
        }

        $event->setResponse(new JsonResponse($payload, $statusCode));
    }
}
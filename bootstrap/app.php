<?php

use App\Enums\MethodEnum;
use App\Enums\ResponseStatusEnum;
use App\Exceptions\MethodNotFoundException;
use App\Exceptions\PairNotFoundException;
use App\Exceptions\RestMethodIsNotAllowedForApiMethodException;
use App\Http\Middleware\ApiTokenAuthMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'api.token' => ApiTokenAuthMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {

            return response()
                ->json([
                    'status' => ResponseStatusEnum::ERROR->value,
                    'code' => Response::HTTP_FORBIDDEN,
                    'message' => 'Invalid token',
                ])
                ->setStatusCode(Response::HTTP_FORBIDDEN);

        });

        $exceptions->renderable(function (MethodNotFoundException $e, Request $request) {

            return response()
                ->json([
                    'status' => ResponseStatusEnum::ERROR->value,
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Method doesnt exist. Allowed methods are: ' .
                        implode(',', array_map(static fn(BackedEnum $enum) => $enum->value, MethodEnum::cases())
                        )
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        });

        $exceptions->renderable(function (RestMethodIsNotAllowedForApiMethodException $e, Request $request) {

            $supportedMethods = implode(', ', $e->allowedMethods);
            return response()
                ->json([
                    'status' => ResponseStatusEnum::ERROR->value,
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => "REST method {$request->method()} for {$e->methodEnum->value} is not supported. Supported: $supportedMethods"
                ])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        });

        $exceptions->renderable(function (PairNotFoundException $e, Request $request) {

            return response()
                ->json([
                    'status' => ResponseStatusEnum::ERROR->value,
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => $e->getMessage()
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) {

            $errors = $e->validator->errors();

            return response()
                ->json([
                    'status' => ResponseStatusEnum::ERROR->value,
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Bad request.',
                    'errors' => $errors
                ])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        });

        $exceptions->renderable(function (Throwable $e, Request $request) {

            Log::channel('uncaught')
                ->critical('Uncaught exception', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

            return response()
                ->json([
                    'status' => ResponseStatusEnum::ERROR->value,
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Server error.',
                ])
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        });

    })->create();

<?php

namespace App\Http\Middleware;

use App\Enums\ResponseStatusEnum;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->responseForbidden();
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return $this->responseForbidden();
        }

        $user = $accessToken->tokenable;

        if (!$user) {
            return $this->responseForbidden();
        }

        auth()->setUser($user);

        return $next($request);
    }

    private function responseForbidden(): JsonResponse
    {
        return response()
            ->json([
                'status' => ResponseStatusEnum::ERROR->value,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'Invalid token',
            ])
            ->setStatusCode(Response::HTTP_FORBIDDEN);
    }
}

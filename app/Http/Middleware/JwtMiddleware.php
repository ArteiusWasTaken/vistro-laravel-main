<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 *
 */
class JwtMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return JsonResponse|mixed
     * @noinspection PhpUndefinedMethodInspection
     */
    public function handle($request, Closure $next): mixed
    {
        try {
            if (!$request->bearerToken() && $request->cookie('token')) {
                $token = $request->cookie('token');
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }

            $payload = JWTAuth::parseToken()->getPayload();
            $request->auth = $payload->get('sub');
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        return $next($request);
    }

}

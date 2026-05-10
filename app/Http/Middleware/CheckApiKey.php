<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-api-key') ?? $request->query('api_key');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API Key diperlukan. Sertakan header x-api-key.',
            ], 401);
        }

        $user = User::where('api_token', $apiKey)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak valid.',
            ], 401);
        }

        if ($user->isBanned()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dibanned. Hubungi admin.',
            ], 403);
        }

        // Make $request->user() return this user for all downstream middleware & controllers
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}

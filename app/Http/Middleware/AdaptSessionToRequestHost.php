<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdaptSessionToRequestHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestHost = strtolower($request->getHost());
        $configuredDomain = ltrim((string) config('session.domain', ''), '.');

        if ($configuredDomain !== '' && ! $this->hostMatchesDomain($requestHost, $configuredDomain)) {
            config(['session.domain' => null]);
        }

        if ((bool) config('session.secure') && $this->shouldDisableSecureCookies($request, $requestHost, $configuredDomain)) {
            config([
                'session.secure' => false,
                'session.partitioned' => false,
            ]);
        }

        return $next($request);
    }

    private function hostMatchesDomain(string $requestHost, string $configuredDomain): bool
    {
        return $requestHost === $configuredDomain || str_ends_with($requestHost, '.'.$configuredDomain);
    }

    private function shouldDisableSecureCookies(Request $request, string $requestHost, string $configuredDomain): bool
    {
        if ($request->isSecure()) {
            return false;
        }

        return $this->isLocalLikeHost($requestHost)
            || ($configuredDomain !== '' && ! $this->hostMatchesDomain($requestHost, $configuredDomain));
    }

    private function isLocalLikeHost(string $requestHost): bool
    {
        if (in_array($requestHost, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        if (filter_var($requestHost, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return str_ends_with($requestHost, '.localhost');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CentralDomainsOnly
{
    /**
     * Allow access only from central (landlord) domains.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower((string) $request->getHost());

        $centralDomains = array_map(
            static fn ($d) => strtolower(trim((string) $d)),
            (array) config('tenancy.central_domains', [])
        );

        if ($host === '' || in_array($host, $centralDomains, true)) {
            return $next($request);
        }

        abort(404);
    }
}


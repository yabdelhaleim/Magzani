<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

/**
 * TrustHosts Middleware
 *
 * Magzani is a multi-tenant application: each tenant gets its own subdomain
 * (e.g. kayan.remotelly1.site), and central domains (superdashboard.*) host
 * the landlord UI. We must trust those hostnames — otherwise Laravel will
 * throw "Bad Request: Hostname not trusted" for tenant hosts.
 *
 * The trust list is built from:
 *  - config('tenancy.central_domains') (landlord hosts)
 *  - env('TENANT_DOMAIN_SUFFIX')       (tenant suffix)
 *  - APP_URL host
 *  - env('TRUSTED_HOSTS')              (manual allow-list, comma-separated)
 */
class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $hosts = [];

        // 1. Central domains (comma-separated in config + env)
        $central = (array) config('tenancy.central_domains', []);
        foreach ($central as $domain) {
            if (is_string($domain) && $domain !== '') {
                $hosts[] = $domain;
            }
        }

        // 2. Tenant domain suffix (e.g. *.remotelly1.site)
        $tenantSuffix = env('TENANT_DOMAIN_SUFFIX');
        if (is_string($tenantSuffix) && $tenantSuffix !== '') {
            $hosts[] = '^(.+\.)?' . preg_quote(ltrim($tenantSuffix, '.'), '/') . '$';
        }

        // 3. APP_URL host
        $appUrl = env('APP_URL');
        if (is_string($appUrl) && $appUrl !== '') {
            $appHost = parse_url($appUrl, PHP_URL_HOST);
            if (is_string($appHost) && $appHost !== '') {
                $hosts[] = $appHost;
            }
        }

        // 4. Manual override from TRUSTED_HOSTS env
        $extra = env('TRUSTED_HOSTS');
        if (is_string($extra) && $extra !== '') {
            foreach (explode(',', $extra) as $h) {
                $h = trim($h);
                if ($h !== '') {
                    $hosts[] = $h;
                }
            }
        }

        // 5. Local development hosts (always trusted)
        $hosts[] = 'localhost';
        $hosts[] = '127.0.0.1';
        $hosts[] = $this->allSubdomainsOfApplicationUrl();

        return array_values(array_unique(array_filter($hosts, fn ($h) => is_string($h) && $h !== '')));
    }
}

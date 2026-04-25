<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SecurityTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:test {--domain=app.syedfoodimpex.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the security middleware implementation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $domain = $this->option('domain');

        $this->info('🔒 Testing Security Implementation for: ' . $domain);
        $this->line('');

        // Test 1: Domain validation
        $this->testDomainValidation($domain);

        // Test 2: Configuration check
        $this->testConfiguration();

        // Test 3: Middleware registration
        $this->testMiddlewareRegistration();

        // Test 4: Security headers
        $this->testSecurityHeaders();

        $this->line('');
        $this->info('✅ Security test completed!');

        return 0;
    }

    private function testDomainValidation(string $domain): void
    {
        $this->line('1. Testing Domain Validation...');

        $allowedDomains = config('security.allowed_domains', []);

        if (in_array($domain, $allowedDomains)) {
            $this->info("   ✅ Domain '{$domain}' is in allowed list");
        } else {
            $this->error("   ❌ Domain '{$domain}' is NOT in allowed list");
        }

        $this->line("   📋 Allowed domains: " . implode(', ', $allowedDomains));
        $this->line('');
    }

    private function testConfiguration(): void
    {
        $this->line('2. Testing Configuration...');

        $config = config('security');

        if ($config) {
            $this->info('   ✅ Security configuration loaded');

            // Check rate limiting
            $rateLimit = $config['ip_restrictions']['rate_limit'] ?? null;
            if ($rateLimit) {
                $this->info("   ✅ Rate limiting: {$rateLimit['max_requests']} requests per {$rateLimit['time_window']} seconds");
            }

            // Check security headers
            $headers = $config['security_headers'] ?? [];
            $this->info('   ✅ Security headers configured: ' . count($headers) . ' headers');

        } else {
            $this->error('   ❌ Security configuration not found');
        }

        $this->line('');
    }

    private function testMiddlewareRegistration(): void
    {
        $this->line('3. Testing Middleware Registration...');

        $middlewareClasses = [
            'App\Http\Middleware\DomainValidation',
            'App\Http\Middleware\IpRestriction',
            'App\Http\Middleware\EnvironmentAccessControl',
        ];

        foreach ($middlewareClasses as $class) {
            if (class_exists($class)) {
                $this->info("   ✅ {$class} exists");
            } else {
                $this->error("   ❌ {$class} not found");
            }
        }

        $this->line('');
    }

    private function testSecurityHeaders(): void
    {
        $this->line('4. Testing Security Headers Configuration...');

        $expectedHeaders = [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'X-XSS-Protection',
            'Referrer-Policy',
            'Content-Security-Policy',
            'Strict-Transport-Security',
            'Permissions-Policy',
        ];

        $configuredHeaders = config('security.security_headers', []);

        foreach ($expectedHeaders as $header) {
            if (isset($configuredHeaders[$header])) {
                $this->info("   ✅ {$header}: {$configuredHeaders[$header]}");
            } else {
                $this->warn("   ⚠️  {$header}: not configured");
            }
        }

        $this->line('');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleSocialiteConfigurationTest extends TestCase
{
    public function test_google_provider_uses_environment_backed_configuration(): void
    {
        $this->assertSame('test-google-client-id', config('services.google.client_id'));
        $this->assertSame('test-google-client-secret', config('services.google.client_secret'));
        $this->assertSame(
            'http://localhost/auth/google/callback',
            config('services.google.redirect'),
        );
    }
}

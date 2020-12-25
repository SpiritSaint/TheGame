<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testAuthenticationRedirect()
    {
        $response = $this->post('/api/geolocation', [
            'latitude' => 30.817642272425815,
            'longitude' => 78.99279350887142,
        ]);

        $response->assertRedirect('');

        $response = $this->json('POST', '/api/geolocation', [
            'latitude' => 30.817642272425815,
            'longitude' => 78.99279350887142,
        ]);

        $response->assertUnauthorized();
    }
}

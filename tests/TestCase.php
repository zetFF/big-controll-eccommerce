<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable any external services during testing
        $this->withoutExceptionHandling();
    }

    protected function signIn($user = null)
    {
        $user = $user ?: User::factory()->create();
        $this->actingAs($user);
        return $this;
    }
}

<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BearerTokenTest extends TestCase
{
    use DatabaseTransactions;
    public function testPassesRequirements(): void
    {
        $user = new User();
        $token = $user->createToken('testToken');
        $result = $token->plainTextToken;
        $this->assertEquals(64, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9\-_]+$/', $result);
    }
}

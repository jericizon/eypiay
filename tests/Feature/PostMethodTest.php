<?php

namespace Eypiay\Eypiay\Tests\Feature;

use Eypiay\Eypiay\Tests\TestCase;
use Illuminate\Support\Str;

class PostMethodTest extends TestCase
{
    public function test_get_post_validator()
    {
        $response = $this->json('POST', '/users');
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('name', $response['errors']);
        $this->assertArrayHasKey('email', $response['errors']);
    }

    public function test_can_send_post_request()
    {
        $randomName = Str::random(5) . ' random name';
        $password = Str::random(10);
        $response = $this->json('POST', '/users', [
            'name' => $randomName,
            'email' => Str::slug($randomName) . '@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertSame('New record added.', $response['message']);
        $this->assertSame($randomName, $response['result']['name']);
    }
}

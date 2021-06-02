<?php

namespace Eypiay\Eypiay\Tests\Feature;

use Eypiay\Eypiay\Tests\TestCase;
use Illuminate\Support\Str;

class GetMethodTest extends TestCase
{
    public function test_can_get_default_details()
    {
        $response = $this->json('GET', '/users');

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertArrayHasKey('current_page', $response['result']);
        $this->assertArrayHasKey('data', $response['result']);
        $this->assertArrayHasKey('first_page_url', $response['result']);
        $this->assertArrayHasKey('from', $response['result']);
        $this->assertArrayHasKey('last_page', $response['result']);
        $this->assertArrayHasKey('last_page_url', $response['result']);
        $this->assertArrayHasKey('next_page_url', $response['result']);
        $this->assertArrayHasKey('path', $response['result']);
        $this->assertArrayHasKey('per_page', $response['result']);
        $this->assertArrayHasKey('prev_page_url', $response['result']);
        $this->assertArrayHasKey('to', $response['result']);
        $this->assertArrayHasKey('total', $response['result']);
        $this->assertArrayHasKey('links', $response['result']);
    }

    public function test_can_filter_columns()
    {
        $keys = ['id', 'email'];
        $response = $this->json('GET', '/users', [
            'filter' => implode('|', $keys),
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertArrayHasKey('data', $response['result']);
        foreach ($keys as $value) {
            $this->assertArrayHasKey($value, $response['result']['data'][0]);
        }
        $this->assertSame(count($keys), count($response['result']['data'][0]));
    }

    public function test_can_sort_minimum_item()
    {
        $response = $this->json('GET', '/users', [
            'items' => -1
        ]);

        // $response->dump();
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertSame(config('eypiay.min_query'), $response['result']['per_page']);

        $this->assertArrayHasKey('data', $response['result']);
        $this->assertSame(config('eypiay.min_query'), count($response['result']['data']));
    }

    public function test_can_sort_maximum_item()
    {
        $response = $this->json('GET', '/users', [
            'items' => config('eypiay.max_query') + 10,
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertSame(config('eypiay.max_query'), $response['result']['per_page']);

        $this->assertArrayHasKey('data', $response['result']);
        $this->assertSame(config('eypiay.max_query'), count($response['result']['data']));
    }

    public function test_can_sort_items()
    {
        $items = rand(config('eypiay.min_query'), config('eypiay.max_query'));
        $response = $this->json('GET', '/users', [
            'items' => $items,
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertSame($items, $response['result']['per_page']);

        $this->assertArrayHasKey('data', $response['result']);
        $this->assertSame($items, count($response['result']['data']));
    }

    public function test_can_order_items()
    {
        $order = 'name';
        $orderBy = 'asc';
        $response = $this->json('GET', '/users', [
            'order' => "{$order}:{$orderBy}",
        ]);

        $item = \DB::table('users')->orderBy($order, $orderBy)->first();
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertArrayHasKey('data', $response['result']);
        $this->assertSame($item->id, $response['result']['data'][0]['id']);
    }

    public function test_can_search_in_column()
    {
        $keys = ['id:20'];
        $response = $this->json('GET', '/users', [
            'search' => implode('|', $keys)
        ]);

        $item = \DB::table('users')->where('id', 'LIKE', '20')->first();
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertArrayHasKey('data', $response['result']);
        $this->assertSame($item->id, $response['result']['data'][0]['id']);
    }

    public function test_can_search_sctrict()
    {
        $item = \DB::table('users')->inRandomOrder()->first();
        $keys = ["email:{$item->email}"];
        $response = $this->json('GET', '/users', [
            'search' => implode('|', $keys),
            'strict_search' => true,
        ]);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('result', $response);
        $this->assertArrayHasKey('data', $response['result']);
        $this->assertSame($item->id, $response['result']['data'][0]['id']);
    }
}

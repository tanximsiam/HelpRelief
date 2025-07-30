<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Ngo;

class NgoControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_ngos_index_returns_ngos()
    {
        Ngo::factory()->count(2)->create();

        $response = $this->getJson('/api/ngos');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }
}

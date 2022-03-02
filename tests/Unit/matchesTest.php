<?php

namespace Tests\Unit;

use Tests\TestCase;

class matchesTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_matches_endpoint()
    {
//        $resp = $this->get('/api/match/{property_id}');
        $resp = $this->json('GET', 'api/match/1',);

        $resp->assertStatus(200);
    }
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_matches_endpoint_returns_valid_format()
    {
//        $resp = $this->get('/api/match/{property_id}');
        $resp = $this->json('GET', 'api/match/1',);

        $resp->assertStatus(200)
            ->assertJsonStructure(
            [
                'data' => [
                    '*' => [
                        "searchProfileId" => 1,
                        "score" => 2,
                        "strictMatchesCount" => 3,
                        "looseMatchesCount" => 2
                    ]
                ]
            ]
        );;
    }
}

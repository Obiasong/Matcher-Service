<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\MatchMakingController;
use PHPUnit\Framework\TestCase;

class MatchMakingControllerTest extends TestCase
{
    public function test_matches_return_data_in_valid_format() {

        $this->json('get', 'api/match/3')
            ->assertStatus(Response::HTTP_OK)
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
            );
    }
}

<?php

namespace Tests\Feature\Movie\API;

use App\External\Interfaces\ExternalMovieApiServiceInterface;
use App\Models\Movie;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MovieDeleteTest extends TestCase
{
    use RefreshDatabase;

    const API_ROUTE = '/api/movies';
    private Generator $faker;

    public Movie $movie;
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = app(Generator::class);
        $this->movie = Movie::factory()->create();

        $externalMovieServiceMock = $this->getMockBuilder(ExternalMovieApiServiceInterface::class)
            ->getMock();
        $externalMovieServiceMock
            ->method('getMovieRating')
            ->willReturn(
                '9'
            );

        $this->app->instance(ExternalMovieApiServiceInterface::class, $externalMovieServiceMock);
    }

    /** @test */
    public function a_movie_delete_endpoint_exists(): void
    {
        $response = $this->deleteJson(self::API_ROUTE . '/' . $this->movie->id, []);
        $response->assertStatus(200);
    }

    /** @test  */
    public function a_movie_delete_endpoint_returns_no_error_if_does_not_exist() :void
    {
        $uuid = Str::uuid();
        $response = $this->deleteJson(self::API_ROUTE . '/' . $uuid, []);
        $response->assertStatus(200);
    }

    /** @test */
    public function a_movie_can_be_deleted(): void {
        $response = $this->deleteJson(self::API_ROUTE . '/' . $this->movie->id, []);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('movies', [
            'id' => $this->movie->id,
        ]);
    }
}

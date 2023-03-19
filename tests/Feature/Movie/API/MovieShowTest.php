<?php

namespace Tests\Feature\Movie\API;

use App\External\Interfaces\ExternalMovieApiServiceInterface;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MovieShowTest extends TestCase
{
    use RefreshDatabase;

    public Movie $movie;
    const API_ROUTE = '/api/movies/';

    public function setUp(): void
    {
        parent::setUp();
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
    public function a_movie_show_endpoint_exists(): void
    {
        $response = $this->get(self::API_ROUTE . $this->movie->id);
        $response->assertStatus(200);
    }

    /** @test */
    public function a_movie_show_endpoint_returns_expected_resource(): void
    {
        $response = $this->get(self::API_ROUTE . $this->movie->id);
        $response->assertJson([
            'id' => $this->movie->id,
            'title' => $this->movie->title,
            'description' => $this->movie->description,
        ]);
    }

    /** @test */
    public function a_movie_show_endpoint_returns_not_found_when_none_exists(): void
    {
        $uuid = Str::uuid();
        $response = $this->get(self::API_ROUTE . $uuid);
        $response->assertNotFound();
    }

    /** @test */
    public function a_movie_show_endpoint_can_have_genre(): void
    {
        $genre = Genre::factory()->create();
        $this->movie->genres()->attach($genre);

        $response = $this->get(self::API_ROUTE . $this->movie->id);

        $genreData = [
            'id' => $genre->id,
            'name' => $genre->name,
            'pivot' => [
                'movie_id' => $this->movie->id,
                'genre_id' => $genre->id
            ]
        ];

        $response->assertJsonFragment($genreData);
    }

    /** @test */
    public function a_movie_show_endpoint_can_have_multiple_genres(): void
    {
        $genre = Genre::factory()->create();
        $genre2 = Genre::factory()->create();
        $this->movie->genres()->attach([$genre->id, $genre2->id]);

        $response = $this->get(self::API_ROUTE . $this->movie->id);

        $response->assertJson([
            'genres' => [
                [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ],
                [
                    'id' => $genre2->id,
                    'name' => $genre2->name,
                ],
            ],
        ]);
    }

    /** @test */
    public function a_movie_show_endpoint_can_have_no_genres(): void
    {
        $response = $this->get(self::API_ROUTE . $this->movie->id);

        $response->assertJson([
            'genres' => []
        ]);
    }

    /** @test */
    public function a_movie_show_endpoint_can_have_year(): void
    {
        $year = Year::factory()->create();
        $this->movie->year()->associate($year);
        $this->movie->save();

        $response = $this->get(self::API_ROUTE . $this->movie->id);

        $response->assertJson([
            'year' => [
                'id' => $year->id,
                'released' => $year->released,
            ]
        ]);
    }

    /** @test */
    public function a_movie_show_endpoint_can_be_missing_year(): void
    {
        $movie = Movie::factory()->create();

        $response = $this->get(self::API_ROUTE . $movie->id);

        $response->assertJson([
            'year' => []
        ]);
    }

    /** @test */
    public function a_movie_show_endpoint_can_return_rating(): void
    {
        $response = $this->get(self::API_ROUTE . $this->movie->id);
        $response->assertJson([
            'id' => $this->movie->id,
            'title' => $this->movie->title,
            'description' => $this->movie->description,
            'rating' => 9,
        ]);
    }
}

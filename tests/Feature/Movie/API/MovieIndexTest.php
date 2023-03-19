<?php

namespace Tests\Feature\Movie\API;

use App\External\Interfaces\ExternalMovieApiServiceInterface;
use Tests\TestCase;
use App\Models\Year;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model as ModelAlias;
use Illuminate\Database\Eloquent\Collection as CollectionAlias;

class MovieIndexTest extends TestCase
{
    use RefreshDatabase;

    public CollectionAlias|ModelAlias $movies;
    const MOVIE_COUNT = 42;
    const API_ROUTE = '/api/movies';

    public function setUp(): void
    {
        parent::setUp();
        $this->movies = Movie::factory(self::MOVIE_COUNT)->create();

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
    public function a_movie_index_endpoint_exists(): void
    {
        $response = $this->get(self::API_ROUTE);
        $response->assertStatus(200);
    }

    /** @test */
    public function a_movie_index_endpoint_has_expected_number_of_results(): void
    {
        $response = $this->get(self::API_ROUTE);
        $response->assertJsonCount(self::MOVIE_COUNT);
    }

    /** @test */
    public function a_movie_index_can_get_movies_by_genre()
    {
        $uuid = Str::uuid();
        //Using an uuid here to prevent collision, just in case faker returns a name that's already been used
        $genre = Genre::factory()->create(['name' => $uuid]);
        $genreMovies = Movie::factory(11)->create()
            ->each(function ($movie) use ($genre) {
                $movie->genres()->attach($genre);
            });

        $response = $this->get(self::API_ROUTE . '?genre=' . $genre->name);

        $response->assertJsonCount(count($genreMovies));
    }

    /** @test */
    public function a_movie_index_not_found_if_genre_does_not_exist()
    {
        //Using an uuid here to prevent collision, just in case faker returns a name that's already been used
        $uuid = Str::uuid();
        $response = $this->get(self::API_ROUTE . '?genre=' . $uuid);

        $response->assertNotFound();
    }

    /** @test */
    public function a_movie_index_can_get_movies_by_year()
    {
        $year = Year::factory()->create();

        $yearMovies = Movie::factory(11)->create()
            ->each(function ($movie) use ($year) {
                $movie->year()->associate($year);
                $movie->save();
            });

        $response = $this->get(self::API_ROUTE . '?year=' . $year->released);

        $response->assertJsonCount(count($yearMovies));
    }

    /** @test */
    public function a_movie_index_not_found_if_year_does_not_exist()
    {
        $response = $this->get(self::API_ROUTE . '?year=' . 'abc');
        $response->assertNotFound();
    }
}

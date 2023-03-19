<?php

namespace Tests\Feature\Movie;

use App\External\Interfaces\ExternalMovieApiServiceInterface;
use Tests\TestCase;
use App\Models\Year;
use App\Models\Genre;
use App\Models\Movie;
use App\Services\MovieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model as ModelAlias;
use Illuminate\Database\Eloquent\Collection as CollectionAlias;

class MovieServiceTest extends TestCase
{
    use RefreshDatabase;

    public CollectionAlias|ModelAlias $movies;
    public MovieService $movieService;
    const MOVIE_COUNT = 42;
    const API_ROUTE = '/api/movies';

    public function setUp(): void
    {
        parent::setUp();

        $externalMovieServiceMock = $this->getMockBuilder(ExternalMovieApiServiceInterface::class)
            ->getMock();
        $externalMovieServiceMock
            ->method('getMovieRating')
            ->willReturn(
               '9'
            );

        $this->movieService = new MovieService($externalMovieServiceMock);
    }

    /** @test */
    public function _a_movie_service_gets_all_movies(): void
    {
        Movie::factory(self::MOVIE_COUNT)->create();
        $actual = $this->movieService->all();
        $this->assertCount(self::MOVIE_COUNT, $actual);
    }

    /** @test */
    public function a_movie_service_can_filter_by_genre(): void
    {
        Movie::factory(12)->create();

        $genre = Genre::factory()->create();
        $expected = Movie::factory(self::MOVIE_COUNT)->create()
            ->each(function ($movie) use ($genre) {
                $movie->genres()->attach($genre);
            });

        $filter = ['genre' => $genre->name];
        $actual = $this->movieService->all($filter);

        $this->assertCount($expected->count(), $actual);
    }

    /** @test */
    public function a_movie_service_can_filter_by_year(): void
    {
        Movie::factory(12)->create();
        $year = Year::factory()->create();

        $expected = Movie::factory(self::MOVIE_COUNT)->create()
            ->each(function ($movie) use ($year) {
                $movie->year()->associate($year);
                $movie->save();
            });

        $filter = ['year' => $year->released];
        $actual = $this->movieService->all($filter);

        $this->assertCount($expected->count(), $actual);
    }

    /** @test */
    public function a_movie_service_can_create_a_resource(): void
    {
        $title = fake()->sentence;
        $description = fake()->paragraph;
        $genre = Genre::factory()->create();
        $genre2 = Genre::factory()->create();
        $year = Year::factory()->create();

        $movieData = [
            'title' => $title,
            'description' => $description,
            'year' => $year->released,
            'genres' => [$genre->id, $genre2->id],
        ];

        $actual = $this->movieService->create($movieData);

        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'description' => $description,
            'year_id' => $year->id,
        ]);

        $this->assertCount(2, $actual->genres);
    }

    /** @test */
    public function a_movie_service_can_create_a_minimal_resource(): void
    {
        $title = fake()->sentence;
        $description = fake()->paragraph;

        $movieData = [
            'title' => $title,
            'description' => $description,
        ];

        $this->movieService->create($movieData);

        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'description' => $description,
        ]);
    }

    /** @test */
    public function a_movie_service_can_update_a_resource(): void
    {
        $title = fake()->sentence;
        $description = fake()->paragraph;
        $movie = Movie::factory()->create();
        $genre = Genre::factory()->create();
        $genre2 = Genre::factory()->create();
        $year = Year::factory()->create();

        $movieData = [
            'id' => $movie->id,
            'title' => $title,
            'description' => $description,
            'year' => $year->released,
            'genres' => [$genre->id, $genre2->id],
        ];

        $actual = $this->movieService->update($movieData);

        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'description' => $description,
            'year_id' => $year->id,
        ]);

        $this->assertCount(2, $actual->genres);
    }


    /** @test */
    public function a_movie_service_can_update_part_of_a_resource(): void
    {
        $movie = Movie::factory()->create();
        $description = fake()->paragraph;

        $movieData = [
            'id' => $movie->id,
            'description' => $description,
        ];

        $actual = $this->movieService->update($movieData);

        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'description' => $description,
        ]);

        $this->assertEquals($description, $actual->description);
    }

    /** @test */
    public function a_movie_can_be_retrieved(): void
    {
        $movie = Movie::factory()->create();
        $genre = Genre::factory()->create();
        $year = Year::factory()->create();
        $movie->genres()->attach($genre);
        $movie->year()->associate($year);

        $actual = $this->movieService->get($movie->id);

        $this->assertEquals($movie->id, $actual->id);
        $this->assertEquals($movie->title, $actual->title);
        $this->assertEquals($movie->description, $actual->description);
        $this->assertEquals($movie->year_id, $year->id);

        $this->assertCount(1, $actual->genres);
    }

    /** @test */
    public function a_movie_can_be_deleted(): void
    {
       $movie = Movie::factory()->create();

       $this->movieService->delete($movie->id);

       $this->assertDatabaseMissing('movies', [
           'id' => $movie->id,
       ]);
    }
}

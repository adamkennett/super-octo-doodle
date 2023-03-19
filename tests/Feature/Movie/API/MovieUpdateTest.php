<?php

namespace Tests\Feature\Movie\API;

use App\External\Interfaces\ExternalMovieApiServiceInterface;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Year;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MovieUpdateTest extends TestCase
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
    public function a_movie_update_endpoint_exists(): void
    {
        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_movie_update_endpoint_returns_not_found_when_no_movie_exists(): void
    {
        $uuid = Str::uuid();

        $response = $this->putJson(self::API_ROUTE . '/' . $uuid, [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ]);

        $response->assertNotFound();
    }

    /** @test */
    public function a_movie_can_be_updated(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;

        $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'title' => $title,
            'description' => $description,
        ]);

        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'description' => $description
        ]);
    }

    /** @test */
    public function a_movie_update_can_update_title_only(): void
    {
        $title = $this->faker->sentence;

        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'title' => $title,
        ]);

        $this->assertDatabaseHas('movies', [
            'title' => $title
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_movie_title_update_has_a_minimum_length(): void
    {
        $title = '1';

        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'title' => $title,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function a_movie_title_update_is_allowed_when_on_minimum_length(): void
    {
        $title = '12';

        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'title' => $title,
        ]);

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors(['title']);
    }

    /** @test */
    public function a_movie_update_can_update_description_only(): void
    {
        $description = $this->faker->paragraph;

        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'description' => $description,
        ]);

        $this->assertDatabaseHas('movies', [
            'description' => $description
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_movie_description_update_has_a_minimum_length(): void
    {
        $description = '01234';

        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'description' => $description,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
    }

    /** @test */
    public function a_movie_description_update_is_allowed_when_on_minimum_length(): void
    {
        $description = '012345';

        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'description' => $description,
        ]);

        $this->assertDatabaseHas('movies', [
            'description' => $description
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_movies_genres_can_be_updated(): void
    {
        $genre = Genre::factory()->create();

        $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'genres' => [$genre->id],
        ]);

        $this->assertCount(1, $this->movie->genres);
    }

    /** @test  */
    public function a_movies_genres_can_be_updated_when_one_is_removed(): void
    {
        $genre = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $this->movie->genres()->attach([$genre->id, $genre2->id]);

        $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'genres' => [$genre->id],
        ]);

        $this->assertCount(1, $this->movie->genres);
    }

    /** @test */
    public function a_movies_year_can_be_updated(): void
    {
        $year = Year::factory()->create();
        $this->movie->year()->associate($year);
        $this->movie->save();

        $expected = Year::factory()->create();

        $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'year' => $expected->released,
        ]);

        $this->assertDatabaseHas('movies', [
            'id' => $this->movie->id,
            'year_id' => $expected->id,
        ]);

        $this->assertDatabaseMissing('movies', [
            'id' => $this->movie->id,
            'year_id' => $year->id,
        ]);
    }

    /** @test */
    public function a_movies_year_update_still_has_validation(): void
    {
        $response = $this->putJson(self::API_ROUTE . '/' . $this->movie->id, [
            'year' => '12-12-2022',
        ]);

        $response->assertJsonValidationErrors(['year']);
    }
}

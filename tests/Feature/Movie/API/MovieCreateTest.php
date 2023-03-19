<?php

namespace Tests\Feature\Movie\API;

use App\External\Interfaces\ExternalMovieApiServiceInterface;
use App\Services\MovieService;
use Tests\TestCase;
use Faker\Generator;
use App\Models\Year;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MovieCreateTest extends TestCase
{
    use RefreshDatabase;

    const API_ROUTE = '/api/movies';
    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = app(Generator::class);

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
    public function a_movie_create_endpoint_exists(): void
    {
        $response = $this->postJson(self::API_ROUTE, [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_movie_can_be_created(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;

        $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
        ]);

        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'description' => $description
        ]);
    }

    /** @test */
    public function a_movie_can_be_created_with_year_when_year_does_not_yet_exist(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;

        //Create with a date format that could come from an api
        $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
            'year' => '1988-04-15',
        ]);

        //Check the years database has had something added
        $this->assertDatabaseHas('years', [
            'released' => '1988-04-15'
        ]);

        //Get the year that was created
        $year = Year::first();

        //Then check that year id has been assigned to the movie in question
        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'description' => $description,
            'year_id' => $year->id,
        ]);
    }

    /** @test */
    public function a_movie_can_be_created_with_year_that_exists(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;
        $year = Year::factory()->create();

        $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
            'year' => $year->released,
        ]);

        $this->assertDatabaseHas('movies', [
            'title' => $title,
            'description' => $description,
            'year_id' => $year->id,
        ]);
    }

    /** @test */
    public function a_movie_requires_a_correct_data(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;

        $response = $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
            'year' => '123',
        ]);

        $this->assertDatabaseMissing('movies', [
            'title' => $title,
            'description' => $description,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['year']);
    }

    /** @test */
    public function a_movie_requires_a_correct_data_format(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;

        $response = $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
            'year' => '12-01-12',
        ]);

        $this->assertDatabaseMissing('movies', [
            'title' => $title,
            'description' => $description,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['year']);
    }

    /** @test */
    public function a_movie_requires_a_title(): void
    {
        $description = $this->faker->paragraph;

        $response = $this->postJson(self::API_ROUTE, [
            'description' => $description,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function a_movie_title_has_a_minimum_length(): void
    {
        $title = '1';
        $description = $this->faker->paragraph;

        $response = $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function a_movie_title_is_allowed_when_on_minimum_length(): void
    {
        $title = '12';
        $description = $this->faker->paragraph;

        $response = $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
        ]);

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors(['title']);
    }

    /** @test */
    public function a_movie_requires_a_description(): void
    {
        $title = $this->faker->sentence;

        $response = $this->postJson(self::API_ROUTE, [
            'title' => $title,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
    }

    /** @test */
    public function a_movie_description_has_a_minimum_length(): void
    {
        $title =  $this->faker->sentence;
        $description = '01234';

        $response = $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
    }

    /** @test */
    public function a_movie_can_be_created_with_a_genre(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;
        $genre = Genre::factory()->create();

        $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
            'genres' => [$genre->id],
        ]);

        $movie = Movie::first();

        $this->assertEquals($genre->title, $movie->genres->first()->title);
        $this->assertCount(1, $movie->genres);
    }

    /** @test */
    public function a_movie_can_be_created_with_multiples_genres(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;
        $genre = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
            'genres' => [$genre->id, $genre2->id],
        ]);

        $movie = Movie::first();

        $this->assertCount(2, $movie->genres);
    }

    /** @test */
    public function a_movie_can_ignore_a_genre_id_that_does_not_exist(): void
    {
        $title = $this->faker->sentence;
        $description = $this->faker->paragraph;
        $genre = Genre::factory()->create();

        $uuid = Str::uuid();

        $this->postJson(self::API_ROUTE, [
            'title' => $title,
            'description' => $description,
            'genres' => [$genre->id, $uuid],
        ]);

        $movie = Movie::first();

        $this->assertCount(1, $movie->genres);
    }
}

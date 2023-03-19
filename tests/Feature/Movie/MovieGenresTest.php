<?php

namespace Tests\Feature\Movie;

use Tests\TestCase;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MovieGenresTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_movie_can_have_no_genre(): void
    {
        $movie = Movie::factory()->create();
        $this->assertCount(0, $movie->genres);
    }

    /** @test */
    public function a_movie_can_set_a_genre(): void
    {
        $movie = Movie::factory()->create();

        $movie->genres()->attach(Genre::factory()->create());

        $this->assertCount(1, $movie->genres);
    }

    /** @test */
    public function a_movie_can_set_multiple_genres(): void
    {
        $movie = Movie::factory()->create();

        $movie->genres()->attach(Genre::factory(3)->create());

        $this->assertCount(3, $movie->genres);
    }
}

<?php

namespace Tests\Feature\Genre;

use Tests\TestCase;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenresMovieTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_genre_can_have_no_movie(): void
    {
        $genre = Genre::factory()->create();
        $this->assertCount(0, $genre->movies);
    }

    /** @test */
    public function a_genre_can_set_a_movie(): void
    {
        $genre = Genre::factory()->create();

        $genre->movies()->attach(Movie::factory()->create());

        $this->assertCount(1, $genre->movies);
    }

    /** @test */
    public function a_genre_can_set_multiple_movies(): void
    {
        $genre = Genre::factory()->create();

        $genre->movies()->attach(Movie::factory(3)->create());

        $this->assertCount(3, $genre->movies);
    }
}

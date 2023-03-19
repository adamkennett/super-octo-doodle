<?php

namespace Tests\Feature\Year;

use App\Models\Movie;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class YearMovieTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_year_can_have_no_movies(): void
    {
        $year = Year::factory()->create();
        $this->assertCount(0, $year->movies);
    }

    /** @test */
    public function a_year_can_have_a_movie(): void
    {
        $movie = Movie::factory()->create();
        $year = Year::factory()->create();

        $year->movies()->save($movie);
        $year->save();

        $this->assertCount(1, $year->movies);
    }

    /** @test */
    public function a_year_can_have_multiple_movies(): void
    {
        $movies = Movie::factory(7)->create();
        $year = Year::factory()->create();

        foreach ($movies as $movie) {
            $year->movies()->save($movie);
        }
        $year->save();

        $this->assertCount(7, $year->movies);
    }
}

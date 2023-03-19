<?php

namespace Tests\Feature\Movie;

use App\Models\Movie;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MovieYearTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function a_movie_can_have_no_year_set() : void
    {
        $movie = Movie::factory()->create();
        $this->assertNull($movie->year);
    }

    /** @test */
    public function a_movie_can_set_a_year(): void
    {
        $year = Year::factory()->create();
        $movie = Movie::factory()->create();

        $movie->year()->associate($year);
        $movie->save();

        $this->assertEquals($year, $movie->year);
    }

    /** @test */
    public function a_movie_can_update_the_year_relationship() : void
    {
        $year = Year::factory()->create();
        $expected = Year::factory()->create();

        $movie = Movie::factory()->create(['year_id' => $year->id]);
        $movie->year()->associate($expected);
        $movie->save();

        $this->assertEquals($expected->id, $movie->year->id);
    }
}

<?php

namespace App\Services;

use App\External\Interfaces\ExternalMovieApiServiceInterface;
use App\Models\Year;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Request as RequestAlias;

class MovieService implements Interfaces\MovieServiceInterface
{
    public ExternalMovieApiServiceInterface $externalMovieService;
    public function __construct(ExternalMovieApiServiceInterface $externalMovieService)
    {
        $this->externalMovieService = $externalMovieService;
    }

    public function all(array $filter = null): Collection
    {
        if(isset($filter['genre'])) {
            $genre = Genre::where('name', $filter['genre'])->firstOrFail();
            return $genre->movies;
        }

        if(isset($filter['year'])) {
            $year = Year::where('released', $filter['year'])->firstOrFail();
            return $year->movies;
        }

        return Movie::all();
    }

    public function get(string $id): Movie
    {
        $movie = Movie::with(['genres', 'year'])->findOrFail($id);
        $movie->rating = $this->externalMovieService->getMovieRating($movie->title);

        return $movie;
    }

    public function create(array $data): Movie
    {
        $movieData = [
            'title' => $data['title'],
            'description' => $data['description'],
        ];

        if (isset($data['year'])) {
            $year = Year::firstOrCreate(['released' => $data['year']]);
            $movieData['year_id'] = $year->id;
        }

        $movie = Movie::create($movieData);

        if(isset($data['genres'])) {
            $genres = Genre::whereIn('id', $data['genres'])->get();
            $movie->genres()->attach($genres);
            $movie->save();
        }

        return $movie;
    }

    public function update(array $data): Movie
    {
        $movie = Movie::findOrFail($data['id']);

        if(isset($data['genres'])) {
            $genres = Genre::whereIn('id', $data['genres'])->get();
            $movie->genres()->sync($genres);
        }

        if (isset($data['year'])) {
            $year = Year::firstOrCreate(['released' => $data['year']]);
            $movie->year()->associate($year);
        }

        $movie->fill($data);
        $movie->save();
        return $movie;
    }

    public function delete(string $id): void
    {
        Movie::destroy($id);
    }

}

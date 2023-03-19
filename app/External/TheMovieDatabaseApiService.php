<?php

namespace App\External;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class TheMovieDatabaseApiService implements Interfaces\ExternalMovieApiServiceInterface
{
    private ClientInterface $client;
    private string $endpoint;
    private string $apiKey;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->endpoint = 'https://api.themoviedb.org/3/search/movie';
        $this->apiKey = env('TMDB_API_KEY');
    }

    //TODO: Error handling
    public function getMovieRating(string $movieName): string
    {
        $response = $this->client->request('GET', $this->endpoint, [
            'query' => [
                'api_key' => $this->apiKey,
                'query' => $movieName,
            ]
        ]);

        $movie = json_decode($response->getBody(), true);

        if (isset($movie["results"][0]['vote_average'])) {
            return $movie["results"][0]['vote_average'];
        }

        //No rating found
        return "0";
    }

}

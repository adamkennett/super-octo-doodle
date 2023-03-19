<?php

namespace App\External;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use App\External\Interfaces\ExternalMovieApiServiceInterface;
class OpenMovieApiService implements ExternalMovieApiServiceInterface
{
    private ClientInterface $client;
    private string $endpoint;
    private string $apiKey;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->endpoint = 'http://www.omdbapi.com/';
        $this->apiKey = env('OMDB_API_KEY');
    }

    //TODO: Error handling
    public function getMovieRating(string $movieName): string
    {
        $response = $this->client->request('GET', $this->endpoint, [
            'query' => [
                'apikey' => $this->apiKey,
                't' => $movieName,
            ]
        ]);

        $movie = json_decode($response->getBody(), true);

        if (isset($movie["imdbRating"])) {
            return $movie["imdbRating"];
        }

        //No rating found
        return "0";
    }
}

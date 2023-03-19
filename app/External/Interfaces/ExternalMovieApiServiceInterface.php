<?php

namespace App\External\Interfaces;

interface ExternalMovieApiServiceInterface
{
    public function getMovieRating(string $movieName): string;
}

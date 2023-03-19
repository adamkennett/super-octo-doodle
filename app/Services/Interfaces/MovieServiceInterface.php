<?php

namespace App\Services\Interfaces;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;

interface MovieServiceInterface
{
    public function all(array $filter): Collection;

    public function get(string $id): Movie;

    public function create(array $data): Movie;

    public function update(array $data): Movie;

    public function delete(string $id): void;
}

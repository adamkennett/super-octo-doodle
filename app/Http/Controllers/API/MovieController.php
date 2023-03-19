<?php

namespace App\Http\Controllers\API;

use App\Services\MovieService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Services\Interfaces\MovieServiceInterface;
use Illuminate\Support\Facades\Request as RequestAlias;

class MovieController extends Controller
{
    public MovieServiceInterface $movieService;

    public function __construct(MovieServiceInterface $movieService)
    {
        $this->movieService = $movieService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->movieService->all(RequestAlias::input());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMovieRequest $request)
    {
        $this->movieService->create($request->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->movieService->get($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMovieRequest $request, string $id)
    {
        $this->movieService->update(array_merge($request->toArray(), ['id' => $id]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->movieService->delete($id);
    }

}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CurrencyResource::collection(Currency::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCurrencyRequest $request): void
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Currency $currency)
    {
        return new CurrencyResource($currency);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency): void
    {
        //
    }
}

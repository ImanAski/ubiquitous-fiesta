<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $paginated = Currency::paginate($limit, ['*'], 'page', $page);
        return CurrencyResource::collection($paginated);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCurrencyRequest $request)
    {
        $data = $request->validated();

        $currency = Currency::create($data);

        return response()->json([
            'data' => new ClientResource($currency),
            'message' => __('The new currency has been created'),
            'status' => 201
        ]);
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
    public function destroy(Currency $currency)
    {
        $currency->delete();

        return response()->json(null, 204);
    }
}

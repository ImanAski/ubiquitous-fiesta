<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomersRequest;
use App\Http\Requests\UpdateCustomersRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customers;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CustomerResource::collection(Customers::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function findCustomer(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'exact' => 'sometimes|boolean'
        ]);

        $customer = Customers::searchMetadata(
            $request->input('key'),
            $request->input('value'),
            $request->boolean('exact', true)
        )->firstOrFail();

        return new CustomerResource($customer);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomersRequest $request)
    {
        $data = $request->validated();

        $customer = Customers::create($data);

        return response()->json([]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customers $customers)
    {
        return new CustomerResource($customers);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customers $customers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomersRequest $request, Customers $customers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customers $customers)
    {
        //
    }
}

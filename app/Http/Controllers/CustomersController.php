<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomersRequest;
use App\Http\Requests\UpdateCustomersRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\TransactionResource;
use App\Models\Customers;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $query = Customers::query();

        if ($request->has('filters') && is_array($request->input('filters'))) {
            foreach ($request->input('filters') as $key => $value) {
                $query->searchMetadata($key, $value, $request->boolean('exact', true));
            }
        }

        $paginated = $query->latest()->paginate($limit, ['*'], 'page', $page);
        return CustomerResource::collection($paginated);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function wallets(Request $request, Customers $customer)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $paginated = $customer->wallets()
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return CustomerResource::collection($paginated);
    }

    /**
     * @param Request $request
     * @param Customers $customer
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function transactions(Request $request, Customers $customer)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);


        $transactions = $customer->transactions()
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomersRequest $request)
    {
        $data = $request->validated();

        $customer = Customers::create($data);

        return response()->json([
            'user' => $customer
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customers $customer)
    {
        return new CustomerResource($customer);
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
        $customers->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', (bool) $request->status);
        }

        $customers = $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    
    public function create()
    {
        return view('customers.create', [
            'customer' => new Customer(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer/party created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load('sales');

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $this->validateCustomer($request);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer/party updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return redirect()
                ->route('customers.index')
                ->with('error', 'This party has sales records and cannot be deleted.');
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer/party deleted successfully.');
    }

    private function validateCustomer(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['customer', 'supplier', 'both'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'boolean'],
        ]);
    }






    
public function ajaxStore(\Illuminate\Http\Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'type' => ['required', \Illuminate\Validation\Rule::in([
            'customer',
            'supplier',
            'both',
        ])],
        'phone' => ['nullable', 'string', 'max:30'],
        'email' => ['nullable', 'email', 'max:255'],
        'address' => ['nullable', 'string', 'max:2000'],
        'note' => ['nullable', 'string', 'max:2000'],
    ]);

    $validated['status'] = true;

    $customer = \App\Models\Customer::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Party added successfully.',
        'party' => [
            'id' => $customer->id,
            'name' => $customer->name,
            'type' => $customer->type,
            'phone' => $customer->phone,
        ],
    ], 201);
}

public function ajaxOptions(\Illuminate\Http\Request $request)
{
    $type = $request->query('type', 'customer');

    if (!in_array($type, ['customer', 'supplier'], true)) {
        return response()->json([
            'message' => 'Invalid party type.',
        ], 422);
    }

    $types = $type === 'customer'
        ? ['customer', 'both']
        : ['supplier', 'both'];

    $parties = \App\Models\Customer::query()
        ->where('status', true)
        ->whereIn('type', $types)
        ->orderBy('name')
        ->get(['id', 'name', 'type', 'phone']);

    return response()->json([
        'parties' => $parties,
    ]);
}

}

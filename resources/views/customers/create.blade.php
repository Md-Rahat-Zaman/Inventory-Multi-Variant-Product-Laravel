
@extends('layouts.app_bootstrap')

@section('title', 'Create Customer')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create Customer / Supplier</h4>
            <div class="text-muted small">Add a customer, supplier, or both.</div>
        </div>

        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                @include('customers._form')
            </form>
        </div>
    </div>

</div>
@endsection

@extends('layouts.app_bootstrap')

@section('title', 'Create Product')

@section('content')

<div class="container-fluid form-page">

    @include('products.partials._page-header')

    <div class="card form-card">

        @include('products.partials._form-header')

        <div class="form-card-body">

            <form method="POST" action="{{ route('products.store') }}">
                @csrf

                @include('products.partials._basic-information')

                @include('products.partials._pricing-inventory')

                @include('products.partials._product-variants')

                @include('products.partials._additional-information')

                @include('components.form.submit-buttons', [
                    'backUrl' => route('products.index'),
                    'buttonText' => 'Save Product'
                ])

            </form>

        </div>
    </div>
</div>

@include('components.quick-create-modal')
@push('scripts')
    @include('products.partials._variant-scripts')
@endpush
@endsection

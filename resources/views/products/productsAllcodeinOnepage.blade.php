@extends('layouts.app_bootstrap')

@section('title', 'Create Product')

@section('content')

<div class="container-fluid form-page">

    {{-- =====================================================
         Page Header
    ====================================================== --}}
    <div class="form-header-bar">

        <div>
            <h3 class="form-page-title">
                Create Product
            </h3>

            <p class="form-page-subtitle">
                Add a new product to your inventory
            </p>
        </div>

        <a href="{{ route('products.index') }}"
           class="btn btn-outline-secondary form-back-btn">

            <i class="bi bi-arrow-left"></i>
            Back

        </a>

    </div>


    {{-- =====================================================
         Form Card
    ====================================================== --}}
    <div class="card form-card">

        {{-- Card Header --}}
        <div class="form-card-header">

            <div class="form-card-heading">

                <div class="form-card-icon">
                    <i class="bi bi-box-seam"></i>
                </div>

                <div>
                    <h5 class="form-card-title">
                        Product Information
                    </h5>

                    <p class="form-card-subtitle">
                        Enter the product details below
                    </p>
                </div>

            </div>

        </div>


        {{-- Card Body --}}
        <div class="form-card-body">

            <form method="POST"
                  action="{{ route('products.store') }}">

                @csrf


                {{-- =================================================
                     Basic Information
                ================================================== --}}
                <div class="form-section">

                    <div class="form-section-header">

                        <h6 class="form-section-title">
                            Basic Information
                        </h6>

                        <span class="form-required-note">
                            * Required
                        </span>

                    </div>


                    <div class="row g-3">

                        {{-- Product Name --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Product Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter product name"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- SKU --}}
                        {{-- <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    SKU
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="sku"
                                    value="{{ old('sku') }}"
                                    class="form-control @error('sku') is-invalid @enderror"
                                    placeholder="e.g. PRD-00001"
                                    required
                                >

                                @error('sku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Barcode --}}
                        {{-- <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Barcode
                                </label>

                                <input
                                    type="text"
                                    name="barcode"
                                    value="{{ old('barcode') }}"
                                    class="form-control @error('barcode') is-invalid @enderror"
                                    placeholder="Enter barcode"
                                >

                                @error('barcode')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Category --}}
                        {{-- Category --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <div class="d-flex justify-content-between align-items-center mb-1">

                                    <label class="form-label mb-0">
                                        Category
                                        <span class="text-danger">*</span>
                                    </label>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary quick-create-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#quickCreateModal"
                                            data-type="category"
                                            data-mode="create">

                                        <i class="bi bi-plus-lg"></i>
                                        Add

                                    </button>

                                </div>

                                <select
                                    name="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror"
                                    required
                                >

                                    <option value="">
                                        Select category
                                    </option>

                                    @foreach($categories ?? [] as $category)

                                        <option
                                            value="{{ $category->id }}"
                                            @selected(old('category_id') == $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('category_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Brand --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Brand
                                </label>

                                <select
                                    name="brand_id"
                                    class="form-select @error('brand_id') is-invalid @enderror"
                                >

                                    <option value="">
                                        Select brand
                                    </option>

                                    @foreach($brands ?? [] as $brand)

                                        <option
                                            value="{{ $brand->id }}"
                                            @selected(old('brand_id') == $brand->id)
                                        >
                                            {{ $brand->name }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('brand_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Unit --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Unit
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="unit_id"
                                    class="form-select @error('unit_id') is-invalid @enderror"
                                    required
                                >

                                    <option value="">
                                        Select unit
                                    </option>

                                    @foreach($units ?? [] as $unit)

                                        <option
                                            value="{{ $unit->id }}"
                                            @selected(old('unit_id') == $unit->id)
                                        >
                                            {{ $unit->name }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('unit_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     Pricing & Inventory
                ================================================== --}}
                <div class="form-section">

                    <div class="form-section-header">

                        <h6 class="form-section-title">
                            Pricing & Inventory
                        </h6>

                    </div>


                    <div class="row g-3">

                        {{-- Purchase Price --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Purchase Price
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="purchase_price"
                                    value="{{ old('purchase_price') }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('purchase_price') is-invalid @enderror"
                                    placeholder="0.00"
                                    required
                                >

                                @error('purchase_price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Sale Price --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Sale Price
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="sale_price"
                                    value="{{ old('sale_price') }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('sale_price') is-invalid @enderror"
                                    placeholder="0.00"
                                    required
                                >

                                @error('sale_price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Tax --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Tax (%)
                                </label>

                                <input
                                    type="number"
                                    name="tax_rate"
                                    value="{{ old('tax_rate', 0) }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('tax_rate') is-invalid @enderror"
                                    placeholder="0.00"
                                >

                                @error('tax_rate')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Opening Stock --}}
                        {{-- <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Opening Stock
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="opening_stock"
                                    value="{{ old('opening_stock', 0) }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('opening_stock') is-invalid @enderror"
                                    placeholder="0"
                                    required
                                >

                                @error('opening_stock')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Minimum Stock --}}
                        {{-- <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Minimum Stock
                                </label>

                                <input
                                    type="number"
                                    name="minimum_stock"
                                    value="{{ old('minimum_stock', 0) }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('minimum_stock') is-invalid @enderror"
                                    placeholder="0"
                                >

                                @error('minimum_stock')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Status --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Status
                                </label>

                                <select
                                    name="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                >

                                    <option value="1"
                                        @selected(old('status', 1) == 1)>
                                        Active
                                    </option>

                                    <option value="0"
                                        @selected(old('status') == 0)>
                                        Inactive
                                    </option>

                                </select>

                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>
                {{-- variant section --}}
                {{-- =================================================
                        Product Variants
                    ================================================== --}}
                    <div class="form-section">

                        <div class="form-section-header">

                            <div>
                                <h6 class="form-section-title mb-1">
                                    Product Variants
                                </h6>

                                <p class="text-muted small mb-0">
                                    Select colors and sizes for this product.
                                </p>
                            </div>

                        </div>


                        <div class="row g-3">

                            {{-- Colors --}}
                            <div class="col-lg-6">

                                <div class="form-group">

                                    <label class="form-label">
                                        Colors
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        name="colors[]"
                                        class="form-select"
                                        multiple
                                        id="variantColors"
                                    >

                                        @foreach($colors ?? [] as $color)

                                            <option
                                                value="{{ $color->id }}"
                                                @selected(in_array(
                                                    $color->id,
                                                    old('colors', [])
                                                ))
                                            >
                                                {{ $color->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                    <div class="form-text">
                                        Hold Ctrl to select multiple colors.
                                    </div>

                                    @error('colors')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>


                            {{-- Sizes --}}
                            <div class="col-lg-6">

                                <div class="form-group">

                                    <label class="form-label">
                                        Sizes
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        name="sizes[]"
                                        class="form-select"
                                        multiple
                                        id="variantSizes"
                                    >

                                        @foreach($sizes ?? [] as $size)

                                            <option
                                                value="{{ $size->id }}"
                                                @selected(in_array(
                                                    $size->id,
                                                    old('sizes', [])
                                                ))
                                            >
                                                {{ $size->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                    <div class="form-text">
                                        Hold Ctrl to select multiple sizes.
                                    </div>

                                    @error('sizes')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>

                        </div>


                        {{-- Generate Button --}}
                        <div class="mt-3">

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="generateVariants"
                            >
                                <i class="bi bi-shuffle"></i>
                                Generate Variants
                            </button>

                        </div>


                        {{-- Generated Variants --}}
                        <div
                            id="variantContainer"
                            class="mt-4 d-none"
                        >

                            <div class="table-responsive">

                                <table class="table table-bordered align-middle">

                                    <thead class="table-light">

                                        <tr>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>SKU</th>
                                            <th>Barcode</th>
                                            <th>Purchase Price</th>
                                            <th>Sale Price</th>
                                            <th>Opening Stock</th>
                                            <th></th>
                                        </tr>

                                    </thead>

                                    <tbody id="variantTableBody">

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>
                {{-- =================================================
                     Additional Information
                ================================================== --}}
                <div class="form-section">

                    <div class="form-section-header">

                        <h6 class="form-section-title">
                            Additional Information
                        </h6>

                    </div>


                    <div class="row g-3">

                        {{-- Note --}}
                        <div class="col-lg-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Note
                                </label>

                                <textarea
                                    name="note"
                                    rows="3"
                                    class="form-control @error('note') is-invalid @enderror"
                                    placeholder="Optional internal note..."
                                >{{ old('note') }}</textarea>

                                @error('note')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Description --}}
                        <div class="col-lg-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Description
                                </label>

                                <textarea
                                    name="description"
                                    rows="3"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Write product description..."
                                >{{ old('description') }}</textarea>

                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     Form Actions
                ================================================== --}}
                @include('components.form.submit-buttons', [
                    'backUrl' => route('products.index'),
                    'buttonText' => 'Save Product'
                ])

            </form>

        </div>

    </div>

</div>
@include('components.quick-create-modal')


{{-- variant generate ar javascript --}}
@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const colorSelect = document.getElementById('variantColors');
    const sizeSelect = document.getElementById('variantSizes');

    const generateButton = document.getElementById('generateVariants');

    const variantContainer = document.getElementById('variantContainer');
    const variantTableBody = document.getElementById('variantTableBody');


    generateButton.addEventListener('click', function () {

        const colors = Array.from(colorSelect.selectedOptions).map(option => ({
            id: option.value,
            name: option.text
        }));

        const sizes = Array.from(sizeSelect.selectedOptions).map(option => ({
            id: option.value,
            name: option.text
        }));


        if (!colors.length) {
            alert('Please select at least one color.');
            return;
        }

        if (!sizes.length) {
            alert('Please select at least one size.');
            return;
        }


        variantTableBody.innerHTML = '';


        let index = 0;


        colors.forEach(function (color) {

            sizes.forEach(function (size) {

                const row = `
                    <tr>

                        <td>
                            ${color.name}

                            <input
                                type="hidden"
                                name="variants[${index}][color_id]"
                                value="${color.id}"
                            >
                        </td>


                        <td>
                            ${size.name}

                            <input
                                type="hidden"
                                name="variants[${index}][size_id]"
                                value="${size.id}"
                            >
                        </td>


                        <td>
                            <input
                                type="text"
                                name="variants[${index}][sku]"
                                class="form-control form-control-sm"
                                placeholder="SKU"
                            >
                        </td>


                        <td>
                            <input
                                type="text"
                                name="variants[${index}][barcode]"
                                class="form-control form-control-sm"
                                placeholder="Barcode"
                            >
                        </td>


                        <td>
                            <input
                                type="number"
                                name="variants[${index}][purchase_price]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="{{ old('purchase_price', 0) }}"
                            >
                        </td>


                        <td>
                            <input
                                type="number"
                                name="variants[${index}][sale_price]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="{{ old('sale_price', 0) }}"
                            >
                        </td>


                        <td>
                            <input
                                type="number"
                                name="variants[${index}][opening_stock]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="0"
                            >
                        </td>


                        <td class="text-center">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger remove-variant"
                            >
                                <i class="bi bi-trash"></i>
                            </button>

                        </td>

                    </tr>
                `;

                variantTableBody.insertAdjacentHTML(
                    'beforeend',
                    row
                );

                index++;

            });

        });


        variantContainer.classList.remove('d-none');

    });


    // Remove variant row
    variantTableBody.addEventListener('click', function (e) {

        const button = e.target.closest('.remove-variant');

        if (!button) {
            return;
        }

        button.closest('tr').remove();

    });

});

</script>

@endpush
@endsection

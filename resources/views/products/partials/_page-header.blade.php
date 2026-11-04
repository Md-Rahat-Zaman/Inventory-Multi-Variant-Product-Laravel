 {{-- =====================================================
         Page Header
    ====================================================== --}}
    <div class="form-header-bar">

        <div>
    <h3 class="form-page-title">
        {{ isset($product) ? 'Edit Product' : 'Create Product' }}
    </h3>

    <p class="form-page-subtitle">
        {{ isset($product) ? 'Update product information' : 'Add a new product to your inventory' }}
    </p>
</div>

        <a href="{{ route('products.index') }}"
           class="btn btn-outline-secondary form-back-btn">

            <i class="bi bi-arrow-left"></i>
            Back

        </a>

    </div>
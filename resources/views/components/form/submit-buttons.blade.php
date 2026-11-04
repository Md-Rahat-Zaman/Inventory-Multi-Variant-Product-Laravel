
<div class="form-submit-area">

    <a href="{{ $backUrl }}"
       class="btn btn-light form-back-btn">
        Cancel
    </a>

    <button type="submit"
            class="btn btn-success form-submit-btn">
        <i class="bi bi-check-circle me-1"></i>
        {{ isset($product) ? 'Update Product' : ($buttonText ?? 'Save Product') }}
    </button>

</div>

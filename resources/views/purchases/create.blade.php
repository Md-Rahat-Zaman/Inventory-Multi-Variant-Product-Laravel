
@extends('layouts.app_bootstrap')

@section('title', 'Create Purchase')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create Purchase</h4>
            <div class="text-muted small">Record a supplier purchase and update inventory.</div>
        </div>

        <a href="{{ route('purchases.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Back to Purchases
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Please check the following:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Purchase Information</h6>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="supplier_id" id="supplier_id"
                                    class="form-select @error('supplier_id') is-invalid @enderror" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }}
                                        @if($supplier->company_name)
                                            — {{ $supplier->company_name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#partyQuickCreateModal"
                            data-party-type="supplier"
                            data-target-select="#my_party_id"
                        >
                            <i class="bi bi-plus-circle me-1"></i> Add Party
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" id="purchase_date"
                               class="form-control" value="{{ old('purchase_date', now()->toDateString()) }}" required>
                    </div>

                    <div class="col-12">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" rows="2"
                                  class="form-control" placeholder="Optional note">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="fw-bold mb-0">Purchase Items</h6>
                    <button type="button" class="btn btn-sm btn-outline-success" id="addItem">
                        <i class="bi bi-plus-lg me-1"></i> Add Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 270px;">Product Variant</th>
                                <th style="min-width: 110px;">Quantity</th>
                                <th style="min-width: 140px;">Purchase Price</th>
                                <th style="min-width: 130px;">Discount</th>
                                <th style="min-width: 140px;" class="text-end">Line Total</th>
                                <th style="width: 55px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>

                <div class="row justify-content-end mt-3">
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Subtotal</span>
                            <strong id="subtotalText">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="discount" class="form-label">Invoice Discount</label>
                            <input type="number" name="discount" id="discount"
                                   class="form-control" min="0" step="0.01"
                                   value="{{ old('discount', '0.00') }}">
                        </div>

                        <div class="d-flex justify-content-between border-top pt-3 mb-3">
                            <span class="fw-bold">Grand Total</span>
                            <strong id="grandTotalText" class="fs-5">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paid_amount"
                                   class="form-control" min="0" step="0.01"
                                   value="{{ old('paid_amount', '0.00') }}" required>
                        </div>

                        <div class="d-flex justify-content-between py-2 border-top">
                            <span class="text-muted">Due Amount</span>
                            <strong id="dueAmountText">0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('purchases.index') }}" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-success" id="submitPurchase">
                <i class="bi bi-check-circle me-1"></i> Save Purchase
            </button>
        </div>
    </form>
</div>


@php
    $variantsData = $variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'product_id' => $variant->product_id,
            'product' => $variant->product->name ?? 'Product',
            'color' => $variant->color->name ?? 'N/A',
            'size' => $variant->size->name ?? 'N/A',
            'price' => (float) $variant->purchase_price,
        ];
    })->values();
@endphp
<script>

document.addEventListener('DOMContentLoaded', function () {
    const variants = @json($variantsData);

    const body = document.getElementById('itemsBody');
    const addButton = document.getElementById('addItem');
    const discountInput = document.getElementById('discount');
    const paidInput = document.getElementById('paid_amount');
    const form = document.getElementById('purchaseForm');

    let rowIndex = 0;

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function addRow(selectedVariantId = '', quantity = '1', price = '', discount = '0') {
        const index = rowIndex++;
        const row = document.createElement('tr');

        const options = variants.map(function (variant) {
            const label = variant.product + ' / ' + variant.color + ' / ' + variant.size;

            return `<option value="${variant.id}"
                data-price="${variant.price}"
                ${String(selectedVariantId) === String(variant.id) ? 'selected' : ''}>
                ${escapeHtml(label)}
            </option>`;
        }).join('');

        row.innerHTML = `
            <td>
                <select name="items[${index}][product_variant_id]"
                        class="form-select variant-select" required>
                    <option value="">Select Variant</option>
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]"
                       class="form-control quantity-input"
                       min="0.01" step="0.01" value="${escapeHtml(quantity)}" required>
            </td>
            <td>
                <input type="number" name="items[${index}][purchase_price]"
                       class="form-control price-input"
                       min="0" step="0.01" value="${escapeHtml(price)}" required>
            </td>
            <td>
                <input type="number" name="items[${index}][discount]"
                       class="form-control item-discount-input"
                       min="0" step="0.01" value="${escapeHtml(discount)}">
            </td>
            <td class="text-end fw-semibold line-total">0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row"
                        aria-label="Remove item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        body.appendChild(row);

        const variantSelect = row.querySelector('.variant-select');
        const priceInput = row.querySelector('.price-input');

        variantSelect.addEventListener('change', function () {
            const option = variantSelect.selectedOptions[0];

            if (option && option.value) {
                priceInput.value = option.dataset.price || '0.00';
            }

            calculateTotals();
        });

        row.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', calculateTotals);
        });

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            calculateTotals();
        });

        if (!price && selectedVariantId) {
            const option = variantSelect.selectedOptions[0];
            priceInput.value = option ? option.dataset.price : '0.00';
        }

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;

        body.querySelectorAll('tr').forEach(function (row) {
            const quantity = Math.max(0, Number(row.querySelector('.quantity-input').value) || 0);
            const price = Math.max(0, Number(row.querySelector('.price-input').value) || 0);
            const discount = Math.max(0, Number(row.querySelector('.item-discount-input').value) || 0);

            const gross = quantity * price;
            const lineTotal = Math.max(0, gross - discount);

            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            subtotal += gross;
        });

        const itemDiscounts = Array.from(body.querySelectorAll('.item-discount-input'))
            .reduce(function (sum, input) {
                return sum + Math.max(0, Number(input.value) || 0);
            }, 0);

        const invoiceDiscount = Math.max(0, Number(discountInput.value) || 0);
        const grandTotal = Math.max(0, subtotal - itemDiscounts - invoiceDiscount);
        const paid = Math.max(0, Number(paidInput.value) || 0);
        const due = Math.max(0, grandTotal - paid);

        document.getElementById('subtotalText').textContent = subtotal.toFixed(2);
        document.getElementById('grandTotalText').textContent = grandTotal.toFixed(2);
        document.getElementById('dueAmountText').textContent = due.toFixed(2);
    }

    addButton.addEventListener('click', function () {
        addRow();
    });

    discountInput.addEventListener('input', calculateTotals);
    paidInput.addEventListener('input', calculateTotals);

    form.addEventListener('submit', function (event) {
        const rows = body.querySelectorAll('tr');

        if (!rows.length) {
            event.preventDefault();
            alert('Please add at least one purchase item.');
            return;
        }

        let valid = true;

        rows.forEach(function (row) {
            const quantity = Number(row.querySelector('.quantity-input').value) || 0;
            const price = Number(row.querySelector('.price-input').value) || 0;
            const itemDiscount = Number(row.querySelector('.item-discount-input').value) || 0;

            if (
                !row.querySelector('.variant-select').value ||
                quantity <= 0 ||
                price < 0 ||
                itemDiscount < 0 ||
                itemDiscount > quantity * price
            ) {
                valid = false;
            }
        });

        const subtotal = Array.from(body.querySelectorAll('tr')).reduce(function (sum, row) {
            return sum + (
                (Number(row.querySelector('.quantity-input').value) || 0) *
                (Number(row.querySelector('.price-input').value) || 0)
            );
        }, 0);

        const itemDiscounts = Array.from(body.querySelectorAll('.item-discount-input'))
            .reduce(function (sum, input) {
                return sum + (Number(input.value) || 0);
            }, 0);

        const invoiceDiscount = Number(discountInput.value) || 0;
        const grandTotal = subtotal - itemDiscounts - invoiceDiscount;
        const paid = Number(paidInput.value) || 0;

        if (!valid || invoiceDiscount < 0 || invoiceDiscount > subtotal - itemDiscounts) {
            event.preventDefault();
            alert('Please check item quantities, prices and discounts.');
            return;
        }

        if (paid < 0 || paid > grandTotal) {
            event.preventDefault();
            alert('Paid amount cannot exceed the grand total.');
        }
    });

    addRow();
});
</script>
@endsection

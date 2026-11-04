
@extends('layouts.app_bootstrap')

@section('title', 'Create Sale')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create Sale</h4>
            <p class="text-muted mb-0">Sell products and update variant stock.</p>
        </div>

        <a href="{{ route('sales.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Sale Information</h6>

                <div class="row g-3">
                     <div class="col-12 col-md-6">
                    <label for="customer_id" class="form-label">Customer</label>

                    <div class="input-group">
                        <select
                            name="customer_id"
                            id="customer_id"
                            class="form-select"
                            data-party-type="customer"
                        >
                            <option value="">Walk-in Customer</option>

                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->name }}
                                    {{ $customer->phone ? ' - ' . $customer->phone : '' }}
                                </option>
                            @endforeach
                        </select>

                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#partyQuickCreateModal"
                            data-party-type="customer"
                            data-target-select="#customer_id"
                        >
                            <i class="bi bi-plus-lg"></i> Add Customer
                        </button>
                    </div>
                </div>

                    <div class="col-12 col-md-6">
                        <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" id="sale_date"
                               class="form-control"
                               value="{{ old('sale_date', now()->toDateString()) }}"
                               required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name"
                               class="form-control" maxlength="150"
                               value="{{ old('customer_name') }}"
                               placeholder="Optional for walk-in customer">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="customer_phone" class="form-label">Customer Phone</label>
                        <input type="text" name="customer_phone" id="customer_phone"
                               class="form-control" maxlength="30"
                               value="{{ old('customer_phone') }}"
                               placeholder="Optional">
                    </div>

                    <div class="col-12">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" rows="2"
                                  class="form-control"
                                  placeholder="Optional sale note">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="fw-bold mb-0">Sale Items</h6>

                    <button type="button" id="addItem" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 300px;">Product / Variant</th>
                                <th style="min-width: 110px;">Available</th>
                                <th style="min-width: 110px;">Quantity</th>
                                <th style="min-width: 130px;">Sale Price</th>
                                <th style="min-width: 120px;">Discount</th>
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
                                   value="{{ old('discount', '0.00') }}" required>
                        </div>

                        <div class="d-flex justify-content-between border-top py-3">
                            <strong>Grand Total</strong>
                            <strong id="grandTotalText" class="fs-5">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paid_amount"
                                   class="form-control" min="0" step="0.01"
                                   value="{{ old('paid_amount', '0.00') }}" required>
                        </div>

                        <div class="d-flex justify-content-between border-top pt-3">
                            <span class="text-muted">Due Amount</span>
                            <strong id="dueAmountText">0.00</strong>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
            <a href="{{ route('sales.index') }}" class="btn btn-light border">Cancel</a>
            <button type="submit" id="submitSale" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i> Complete Sale
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @php
    $variantData = $variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'product' => $variant->product->name ?? 'Product',
            'color' => $variant->color->name ?? 'N/A',
            'size' => $variant->size->name ?? 'N/A',
            'stock' => (float) $variant->stock,
            'price' => (float) $variant->sale_price,
        ];
    })->values();
@endphp


    const variants = @json($variantData);


    const body = document.getElementById('itemsBody');
    const customerSelect = document.getElementById('customer_id');
    const customerName = document.getElementById('customer_name');
    const customerPhone = document.getElementById('customer_phone');
    const discountInput = document.getElementById('discount');
    const paidInput = document.getElementById('paid_amount');
    const form = document.getElementById('saleForm');

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

    function addRow() {
        const index = rowIndex++;
        const row = document.createElement('tr');

        const options = variants.map(function (variant) {
            const label = variant.product + ' / ' + variant.color + ' / ' + variant.size;

            return `<option value="${variant.id}"
                data-stock="${variant.stock}"
                data-price="${variant.price}">
                ${escapeHtml(label)}
            </option>`;
        }).join('');

        row.innerHTML = `
            <td>
                <select name="items[${index}][product_variant_id]"
                        class="form-select variant-select" required>
                    <option value="">Select product variant</option>
                    ${options}
                </select>
            </td>
            <td class="text-center available-stock">—</td>
            <td>
                <input type="number" name="items[${index}][quantity]"
                       class="form-control quantity-input"
                       min="0.01" step="0.01" value="1" required>
            </td>
            <td>
                <input type="number" class="form-control price-display"
                       value="" readonly tabindex="-1">
            </td>
            <td>
                <input type="number" name="items[${index}][discount]"
                       class="form-control item-discount-input"
                       min="0" step="0.01" value="0" required>
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
        const quantityInput = row.querySelector('.quantity-input');
        const priceDisplay = row.querySelector('.price-display');
        const availableStock = row.querySelector('.available-stock');

        variantSelect.addEventListener('change', function () {
            const option = variantSelect.selectedOptions[0];

            priceDisplay.value = option && option.value
                ? Number(option.dataset.price).toFixed(2)
                : '';

            availableStock.textContent = option && option.value
                ? Number(option.dataset.stock).toFixed(2)
                : '—';

            quantityInput.max = option && option.value
                ? option.dataset.stock
                : '';

            calculateTotals();
        });

        row.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', calculateTotals);
        });

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            calculateTotals();
        });

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let itemDiscounts = 0;

        body.querySelectorAll('tr').forEach(function (row) {
            const select = row.querySelector('.variant-select');
            const option = select.selectedOptions[0];

            const quantity = Math.max(
                0,
                Number(row.querySelector('.quantity-input').value) || 0
            );

            const price = option && option.value
                ? Number(option.dataset.price) || 0
                : 0;

            const discount = Math.max(
                0,
                Number(row.querySelector('.item-discount-input').value) || 0
            );

            const gross = quantity * price;
            const lineTotal = Math.max(0, gross - discount);

            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);

            subtotal += gross;
            itemDiscounts += discount;
        });

        const invoiceDiscount = Math.max(0, Number(discountInput.value) || 0);
        const grandTotal = Math.max(
            0,
            subtotal - itemDiscounts - invoiceDiscount
        );

        const paid = Math.max(0, Number(paidInput.value) || 0);
        const due = Math.max(0, grandTotal - paid);

        document.getElementById('subtotalText').textContent = subtotal.toFixed(2);
        document.getElementById('grandTotalText').textContent = grandTotal.toFixed(2);
        document.getElementById('dueAmountText').textContent = due.toFixed(2);
    }

    document.getElementById('addItem').addEventListener('click', addRow);

    discountInput.addEventListener('input', calculateTotals);
    paidInput.addEventListener('input', calculateTotals);

    customerSelect.addEventListener('change', function () {
        const option = customerSelect.selectedOptions[0];

        if (customerSelect.value) {
            customerName.value = option.dataset.name || '';
            customerPhone.value = option.dataset.phone || '';
            customerName.readOnly = true;
            customerPhone.readOnly = true;
        } else {
            customerName.readOnly = false;
            customerPhone.readOnly = false;
            customerName.value = '';
            customerPhone.value = '';
        }
    });

    customerSelect.dispatchEvent(new Event('change'));

    form.addEventListener('submit', function (event) {
        let valid = true;
        const rows = body.querySelectorAll('tr');

        if (!rows.length) {
            valid = false;
        }

        const selectedVariantIds = [];

        rows.forEach(function (row) {
            const select = row.querySelector('.variant-select');
            const option = select.selectedOptions[0];

            const quantity = Number(row.querySelector('.quantity-input').value);
            const discount = Number(row.querySelector('.item-discount-input').value);
            const price = option && option.value
                ? Number(option.dataset.price)
                : 0;
            const stock = option && option.value
                ? Number(option.dataset.stock)
                : 0;

            if (
                !select.value ||
                !Number.isFinite(quantity) ||
                quantity <= 0 ||
                quantity > stock ||
                !Number.isFinite(discount) ||
                discount < 0 ||
                discount > quantity * price
            ) {
                valid = false;
            }

            if (select.value) {
                selectedVariantIds.push(select.value);
            }
        });

        if (new Set(selectedVariantIds).size !== selectedVariantIds.length) {
            valid = false;
        }

        const subtotal = Array.from(rows).reduce(function (sum, row) {
            const option = row.querySelector('.variant-select').selectedOptions[0];
            const price = option && option.value ? Number(option.dataset.price) : 0;
            const quantity = Number(row.querySelector('.quantity-input').value) || 0;

            return sum + (price * quantity);
        }, 0);

        const itemDiscounts = Array.from(
            body.querySelectorAll('.item-discount-input')
        ).reduce(function (sum, input) {
            return sum + (Number(input.value) || 0);
        }, 0);

        const invoiceDiscount = Number(discountInput.value);
        const paid = Number(paidInput.value);
        const grandTotal = subtotal - itemDiscounts - invoiceDiscount;

        if (
            !Number.isFinite(invoiceDiscount) ||
            invoiceDiscount < 0 ||
            invoiceDiscount > subtotal - itemDiscounts ||
            !Number.isFinite(paid) ||
            paid < 0 ||
            paid > grandTotal
        ) {
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
            alert('Please check variants, available stock, quantities, discounts and payment amount.');
        }
    });

    addRow();
});
</script>
@endsection

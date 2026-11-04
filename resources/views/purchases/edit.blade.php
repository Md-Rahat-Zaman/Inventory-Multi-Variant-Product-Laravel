@extends('layouts.app_bootstrap')

@section('title', 'Edit Purchase')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Purchase</h4>
            <div class="text-muted small">
                Update supplier purchase and inventory.
            </div>
        </div>

        <a href="{{ route('purchases.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Back to Purchases
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Please check the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('purchases.update', $purchase->id) }}"
        id="purchaseForm"
    >
        @csrf
        @method('PUT')

        {{-- Purchase Information --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h6 class="fw-bold mb-3">Purchase Information</h6>

                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <label for="supplier_id" class="form-label">
                            Supplier <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">
                            <select
                                name="supplier_id"
                                id="supplier_id"
                                class="form-select @error('supplier_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Select Supplier</option>

                                @foreach ($suppliers as $supplier)
                                    <option
                                        value="{{ $supplier->id }}"
                                        @selected(
                                            (string) old('supplier_id', $purchase->supplier_id)
                                            === (string) $supplier->id
                                        )
                                    >
                                        {{ $supplier->name }}
                                        @if ($supplier->company_name)
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
                                data-target-select="#supplier_id"
                            >
                                <i class="bi bi-plus-circle me-1"></i> Add Party
                            </button>
                        </div>

                        @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="purchase_date" class="form-label">
                            Purchase Date <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="purchase_date"
                            id="purchase_date"
                            class="form-control @error('purchase_date') is-invalid @enderror"
                            value="{{ old(
                                'purchase_date',
                                \Carbon\Carbon::parse($purchase->purchase_date)->toDateString()
                            ) }}"
                            required
                        >

                        @error('purchase_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label">Purchase Number</label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchase->purchase_no }}"
                            readonly
                        >
                    </div>

                    <div class="col-12">
                        <label for="note" class="form-label">Note</label>

                        <textarea
                            name="note"
                            id="note"
                            rows="2"
                            class="form-control"
                            placeholder="Optional note"
                        >{{ old('note', $purchase->note) }}</textarea>

                        @error('note')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Purchase Items --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="fw-bold mb-0">Purchase Items</h6>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-success"
                        id="addItem"
                    >
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
                                <th style="min-width: 140px;" class="text-end">
                                    Line Total
                                </th>
                                <th style="width: 55px;"></th>
                            </tr>
                        </thead>

                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>

                @error('items')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror

                <div class="row justify-content-end mt-3">
                    <div class="col-12 col-md-6 col-xl-4">

                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Subtotal</span>
                            <strong id="subtotalText">0.00</strong>
                        </div>

                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Item Discount</span>
                            <strong id="itemDiscountText">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="discount" class="form-label">
                                Invoice Discount
                            </label>

                            <input
                                type="number"
                                name="discount"
                                id="discount"
                                class="form-control @error('discount') is-invalid @enderror"
                                min="0"
                                step="0.01"
                                value="{{ old('discount', $invoiceDiscount) }}"
                            >

                            @error('discount')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between border-top pt-3 mb-3">
                            <span class="fw-bold">Grand Total</span>
                            <strong id="grandTotalText" class="fs-5">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">
                                Paid Amount <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                name="paid_amount"
                                id="paid_amount"
                                class="form-control @error('paid_amount') is-invalid @enderror"
                                min="0"
                                step="0.01"
                                value="{{ old('paid_amount', $purchase->paid_amount) }}"
                                required
                            >

                            @error('paid_amount')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
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
            <a href="{{ route('purchases.index') }}" class="btn btn-light border">
                Cancel
            </a>

            <button type="submit" class="btn btn-success" id="submitPurchase">
                <i class="bi bi-check-circle me-1"></i> Update Purchase
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const variants = {{ Illuminate\Support\Js::from($variantsData) }};
    const existingItems = {{ Illuminate\Support\Js::from(old('items', $existingItems)) }};

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

    function getVariant(variantId) {
        return variants.find(function (variant) {
            return String(variant.id) === String(variantId);
        });
    }

    function money(value) {
        return Number(value || 0).toFixed(2);
    }

    function addRow(item = {}) {
        const index = rowIndex++;
        const row = document.createElement('tr');

        const selectedVariantId = item.product_variant_id ?? '';
        const quantity = item.quantity ?? '1';
        const price = item.purchase_price ?? '';
        const itemDiscount = item.discount ?? '0';

        const options = variants.map(function (variant) {
            const label = variant.product + ' / ' + variant.color + ' / ' + variant.size;

            return `
                <option
                    value="${variant.id}"
                    ${String(selectedVariantId) === String(variant.id) ? 'selected' : ''}
                >
                    ${escapeHtml(label)}
                </option>
            `;
        }).join('');

        row.innerHTML = `
            <td>
                <select
                    name="items[${index}][product_variant_id]"
                    class="form-select variant-select"
                    required
                >
                    <option value="">Select Variant</option>
                    ${options}
                </select>
            </td>

            <td>
                <input
                    type="number"
                    name="items[${index}][quantity]"
                    class="form-control quantity-input"
                    min="0.01"
                    step="0.01"
                    value="${escapeHtml(quantity)}"
                    required
                >
            </td>

            <td>
                <input
                    type="number"
                    name="items[${index}][purchase_price]"
                    class="form-control price-input"
                    min="0"
                    step="0.01"
                    value="${escapeHtml(price)}"
                    required
                >
            </td>

            <td>
                <input
                    type="number"
                    name="items[${index}][discount]"
                    class="form-control item-discount-input"
                    min="0"
                    step="0.01"
                    value="${escapeHtml(itemDiscount)}"
                >
            </td>

            <td class="text-end fw-semibold line-total">0.00</td>

            <td class="text-center">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger remove-row"
                    aria-label="Remove item"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        body.appendChild(row);

        const variantSelect = row.querySelector('.variant-select');
        const priceInput = row.querySelector('.price-input');

        variantSelect.addEventListener('change', function () {
            const variant = getVariant(variantSelect.value);

            if (variant) {
                priceInput.value = money(variant.price);
            } else {
                priceInput.value = '';
            }

            calculateTotals();
            updateVariantOptions();
        });

        row.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', calculateTotals);
        });

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            updateVariantOptions();
            calculateTotals();
        });

        if (selectedVariantId && price === '') {
            const variant = getVariant(selectedVariantId);

            if (variant) {
                priceInput.value = money(variant.price);
            }
        }

        updateVariantOptions();
        calculateTotals();
    }

    function updateVariantOptions() {
        const selects = Array.from(body.querySelectorAll('.variant-select'));

        selects.forEach(function (select) {
            const currentValue = select.value;

            const selectedElsewhere = selects
                .filter(function (otherSelect) {
                    return otherSelect !== select && otherSelect.value !== '';
                })
                .map(function (otherSelect) {
                    return String(otherSelect.value);
                });

            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                option.disabled =
                    selectedElsewhere.includes(String(option.value)) &&
                    String(option.value) !== String(currentValue);
            });
        });
    }

    function calculateTotals() {
        let subtotal = 0;
        let itemDiscountTotal = 0;

        body.querySelectorAll('tr').forEach(function (row) {
            const quantity = Math.max(
                0,
                Number(row.querySelector('.quantity-input').value) || 0
            );

            const price = Math.max(
                0,
                Number(row.querySelector('.price-input').value) || 0
            );

            const itemDiscount = Math.max(
                0,
                Number(row.querySelector('.item-discount-input').value) || 0
            );

            const gross = quantity * price;
            const lineTotal = Math.max(0, gross - itemDiscount);

            row.querySelector('.line-total').textContent = money(lineTotal);

            subtotal += gross;
            itemDiscountTotal += itemDiscount;
        });

        const invoiceDiscount = Math.max(
            0,
            Number(discountInput.value) || 0
        );

        const grandTotal = Math.max(
            0,
            subtotal - itemDiscountTotal - invoiceDiscount
        );

        const paidAmount = Math.max(
            0,
            Number(paidInput.value) || 0
        );

        const dueAmount = Math.max(0, grandTotal - paidAmount);

        document.getElementById('subtotalText').textContent = money(subtotal);
        document.getElementById('itemDiscountText').textContent = money(itemDiscountTotal);
        document.getElementById('grandTotalText').textContent = money(grandTotal);
        document.getElementById('dueAmountText').textContent = money(dueAmount);
    }

    addButton.addEventListener('click', function () {
        addRow();
    });

    discountInput.addEventListener('input', calculateTotals);
    paidInput.addEventListener('input', calculateTotals);

    form.addEventListener('submit', function (event) {
        const rows = Array.from(body.querySelectorAll('tr'));

        if (rows.length === 0) {
            event.preventDefault();
            alert('Please add at least one purchase item.');
            return;
        }

        let subtotal = 0;
        let itemDiscountTotal = 0;
        const selectedIds = new Set();

        for (const row of rows) {
            const variantId = row.querySelector('.variant-select').value;
            const quantity = Number(row.querySelector('.quantity-input').value);
            const price = Number(row.querySelector('.price-input').value);
            const itemDiscount = Number(row.querySelector('.item-discount-input').value || 0);

            if (!variantId || !getVariant(variantId)) {
                event.preventDefault();
                alert('Please select a valid product variant for every item.');
                return;
            }

            if (selectedIds.has(String(variantId))) {
                event.preventDefault();
                alert('The same product variant cannot be added more than once.');
                return;
            }

            selectedIds.add(String(variantId));

            if (
                !Number.isFinite(quantity) ||
                quantity <= 0 ||
                !Number.isFinite(price) ||
                price < 0 ||
                !Number.isFinite(itemDiscount) ||
                itemDiscount < 0
            ) {
                event.preventDefault();
                alert('Please enter valid quantities, prices and discounts.');
                return;
            }

            const gross = quantity * price;

            if (itemDiscount > gross) {
                event.preventDefault();
                alert('Item discount cannot exceed the item amount.');
                return;
            }

            subtotal += gross;
            itemDiscountTotal += itemDiscount;
        }

        const invoiceDiscount = Number(discountInput.value || 0);
        const paidAmount = Number(paidInput.value || 0);
        const remainingTotal = subtotal - itemDiscountTotal;

        if (
            !Number.isFinite(invoiceDiscount) ||
            invoiceDiscount < 0 ||
            invoiceDiscount > remainingTotal
        ) {
            event.preventDefault();
            alert('Invoice discount cannot exceed the remaining bill amount.');
            return;
        }

        const grandTotal = remainingTotal - invoiceDiscount;

        if (
            !Number.isFinite(paidAmount) ||
            paidAmount < 0 ||
            paidAmount > grandTotal
        ) {
            event.preventDefault();
            alert('Paid amount cannot exceed the grand total.');
        }
    });

    if (Array.isArray(existingItems) && existingItems.length > 0) {
        existingItems.forEach(function (item) {
            addRow(item);
        });
    } else {
        addRow();
    }

    calculateTotals();
});
</script>
@endpush
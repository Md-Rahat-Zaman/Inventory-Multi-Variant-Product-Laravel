@extends('layouts.app_bootstrap')

@section('title', 'Edit Sale')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Sale</h4>
            <div class="text-muted small">Update sale information and items</div>
        </div>

        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Please fix the following errors:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="saleForm">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Sale Information</h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">
                            Customer
                        </label>

                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">Walk-in Customer</option>

                            @foreach ($customers as $customer)
                                <option
                                    value="{{ $customer->id }}"
                                    {{ (string) old('customer_id', $sale->customer_id) === (string) $customer->id ? 'selected' : '' }}
                                >
                                    {{ $customer->name }}
                                    @if ($customer->phone)
                                        - {{ $customer->phone }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('customer_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="sale_date" class="form-label">
                            Sale Date <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="sale_date"
                            id="sale_date"
                            class="form-control"
                            value="{{ old('sale_date', \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d')) }}"
                            required
                        >

                        @error('sale_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Sale Number</label>
                        <input
                            type="text"
                            class="form-control"
                            value="{{ $sale->sale_no }}"
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
                        >{{ old('note', $sale->note) }}</textarea>

                        @error('note')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0">Sale Items</h6>

                <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="saleItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 280px;">Product / Variant</th>
                                <th style="min-width: 110px;">Stock</th>
                                <th style="min-width: 130px;">Sale Price</th>
                                <th style="min-width: 120px;">Quantity</th>
                                <th style="min-width: 130px;">Item Discount</th>
                                <th style="min-width: 130px;">Total</th>
                                <th style="width: 60px;">Action</th>
                            </tr>
                        </thead>

                        <tbody id="saleItemsBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                <div class="row justify-content-end">
                    <div class="col-md-5 col-lg-4">

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong id="subtotalText">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="discount" class="form-label">
                                Invoice Discount
                            </label>

                            <input
                                type="number"
                                name="discount"
                                id="discount"
                                class="form-control"
                                min="0"
                                step="0.01"
                                value="{{ old('discount', $invoiceDiscount) }}"
                            >

                            @error('discount')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Item Discount</span>
                            <strong id="itemDiscountText">0.00</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Grand Total</span>
                            <strong id="grandTotalText" class="text-primary">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">
                                Paid Amount <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                name="paid_amount"
                                id="paid_amount"
                                class="form-control"
                                min="0"
                                step="0.01"
                                value="{{ old('paid_amount', $sale->paid_amount) }}"
                                required
                            >

                            @error('paid_amount')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Due Amount</span>
                            <strong id="dueAmountText" class="text-danger">0.00</strong>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('sales.index') }}" class="btn btn-light border">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary" id="submitSaleBtn">
                <i class="bi bi-check-circle me-1"></i> Update Sale
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const variants = {{ Illuminate\Support\Js::from(
        $variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'product' => $variant->product->name ?? 'Product',
                'color' => $variant->color->name ?? 'N/A',
                'size' => $variant->size->name ?? 'N/A',
                'stock' => (float) $variant->stock,
                'price' => (float) $variant->sale_price,
            ];
        })->values()
    ) }};

    const existingItems = {{ Illuminate\Support\Js::from(old('items', $existingItems)) }};

    const originalQuantities = {};

    existingItems.forEach(function (item) {
        const variantId = Number(item.product_variant_id);
        originalQuantities[variantId] =
            (originalQuantities[variantId] || 0) + Number(item.quantity || 0);
    });

    const itemsBody = document.getElementById('saleItemsBody');
    const addItemBtn = document.getElementById('addItemBtn');
    const discountInput = document.getElementById('discount');
    const paidAmountInput = document.getElementById('paid_amount');
    const saleForm = document.getElementById('saleForm');

    let rowIndex = 0;

    function money(amount) {
        return Number(amount || 0).toFixed(2);
    }

    function getVariant(variantId) {
        return variants.find(function (variant) {
            return Number(variant.id) === Number(variantId);
        });
    }

    function getAvailableStock(variantId) {
        const variant = getVariant(variantId);

        if (!variant) {
            return 0;
        }

        return Number(variant.stock || 0) +
            Number(originalQuantities[variantId] || 0);
    }

    function getSelectedVariantIds(exceptRow) {
        const selectedIds = [];

        itemsBody.querySelectorAll('.variant-select').forEach(function (select) {
            const row = select.closest('tr');

            if (row !== exceptRow && select.value !== '') {
                selectedIds.push(Number(select.value));
            }
        });

        return selectedIds;
    }

    function updateVariantOptions() {
        const selectedIds = getSelectedVariantIds(null);

        itemsBody.querySelectorAll('tr').forEach(function (row) {
            const select = row.querySelector('.variant-select');
            const currentValue = select.value;

            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const optionId = Number(option.value);

                option.disabled =
                    selectedIds.includes(optionId) &&
                    optionId !== Number(currentValue);
            });
        });
    }

    function updateRow(row) {
        const select = row.querySelector('.variant-select');
        const stockInput = row.querySelector('.stock-display');
        const priceInput = row.querySelector('.price-display');
        const quantityInput = row.querySelector('.quantity-input');
        const itemDiscountInput = row.querySelector('.item-discount-input');
        const totalText = row.querySelector('.line-total');

        const variant = getVariant(select.value);

        if (!variant) {
            stockInput.value = '';
            priceInput.value = '';
            totalText.textContent = money(0);
            calculateTotals();
            return;
        }

        const availableStock = getAvailableStock(variant.id);
        const quantity = Number(quantityInput.value || 0);
        const price = Number(variant.price || 0);
        const itemDiscount = Number(itemDiscountInput.value || 0);

        stockInput.value = money(availableStock);
        priceInput.value = money(price);

        const gross = quantity * price;
        const lineTotal = Math.max(0, gross - itemDiscount);

        totalText.textContent = money(lineTotal);

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let itemDiscountTotal = 0;

        itemsBody.querySelectorAll('tr').forEach(function (row) {
            const select = row.querySelector('.variant-select');
            const variant = getVariant(select.value);

            if (!variant) {
                return;
            }

            const quantity = Number(
                row.querySelector('.quantity-input').value || 0
            );

            const itemDiscount = Number(
                row.querySelector('.item-discount-input').value || 0
            );

            subtotal += quantity * Number(variant.price || 0);
            itemDiscountTotal += itemDiscount;
        });

        const invoiceDiscount = Number(discountInput.value || 0);
        const grandTotal = Math.max(
            0,
            subtotal - itemDiscountTotal - invoiceDiscount
        );

        const paidAmount = Number(paidAmountInput.value || 0);
        const dueAmount = Math.max(0, grandTotal - paidAmount);

        document.getElementById('subtotalText').textContent = money(subtotal);
        document.getElementById('itemDiscountText').textContent = money(itemDiscountTotal);
        document.getElementById('grandTotalText').textContent = money(grandTotal);
        document.getElementById('dueAmountText').textContent = money(dueAmount);
    }

    function addRow(item = {}) {
        const row = document.createElement('tr');
        const currentIndex = rowIndex++;

        const selectedVariantId = item.product_variant_id
            ? Number(item.product_variant_id)
            : '';

        const quantity = item.quantity !== undefined
            ? item.quantity
            : 1;

        const itemDiscount = item.discount !== undefined
            ? item.discount
            : 0;

        let options = '<option value="">Select Product / Variant</option>';

        variants.forEach(function (variant) {
            const label =
                variant.product + ' - ' +
                variant.color + ' - ' +
                variant.size;

            const selected = Number(variant.id) === Number(selectedVariantId)
                ? 'selected'
                : '';

            options +=
                '<option value="' + variant.id + '" ' + selected + '>' +
                label + '</option>';
        });

        row.innerHTML = `
            <td>
                <select
                    name="items[${currentIndex}][product_variant_id]"
                    class="form-select variant-select"
                    required
                >
                    ${options}
                </select>
            </td>

            <td>
                <input type="text" class="form-control stock-display" readonly>
            </td>

            <td>
                <input type="text" class="form-control price-display" readonly>
            </td>

            <td>
                <input
                    type="number"
                    name="items[${currentIndex}][quantity]"
                    class="form-control quantity-input"
                    min="0.01"
                    step="0.01"
                    value="${quantity}"
                    required
                >
            </td>

            <td>
                <input
                    type="number"
                    name="items[${currentIndex}][discount]"
                    class="form-control item-discount-input"
                    min="0"
                    step="0.01"
                    value="${itemDiscount}"
                >
            </td>

            <td>
                <strong class="line-total">0.00</strong>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        itemsBody.appendChild(row);

        const select = row.querySelector('.variant-select');
        const quantityInput = row.querySelector('.quantity-input');
        const itemDiscountInput = row.querySelector('.item-discount-input');

        select.addEventListener('change', function () {
            updateVariantOptions();
            updateRow(row);
        });

        quantityInput.addEventListener('input', function () {
            updateRow(row);
        });

        itemDiscountInput.addEventListener('input', function () {
            updateRow(row);
        });

        row.querySelector('.remove-item').addEventListener('click', function () {
            row.remove();
            updateVariantOptions();
            calculateTotals();
        });

        updateVariantOptions();
        updateRow(row);
    }

    addItemBtn.addEventListener('click', function () {
        addRow();
    });

    discountInput.addEventListener('input', calculateTotals);
    paidAmountInput.addEventListener('input', calculateTotals);

    saleForm.addEventListener('submit', function (event) {
        const rows = itemsBody.querySelectorAll('tr');

        if (rows.length === 0) {
            event.preventDefault();
            alert('Please add at least one sale item.');
            return;
        }

        let valid = true;
        let message = '';

        const selectedIds = [];

        rows.forEach(function (row) {
            const select = row.querySelector('.variant-select');
            const quantityInput = row.querySelector('.quantity-input');
            const itemDiscountInput = row.querySelector('.item-discount-input');

            const variant = getVariant(select.value);
            const quantity = Number(quantityInput.value || 0);
            const itemDiscount = Number(itemDiscountInput.value || 0);

            if (!variant) {
                valid = false;
                message = 'Please select a product for every row.';
                return;
            }

            if (selectedIds.includes(Number(variant.id))) {
                valid = false;
                message = 'The same product variant cannot be added more than once.';
                return;
            }

            selectedIds.push(Number(variant.id));

            if (quantity <= 0) {
                valid = false;
                message = 'Quantity must be greater than zero.';
                return;
            }

            if (quantity > getAvailableStock(variant.id)) {
                valid = false;
                message = 'Quantity exceeds available stock for ' +
                    variant.product + ' - ' + variant.color + ' - ' + variant.size;
                return;
            }

            const gross = quantity * Number(variant.price || 0);

            if (itemDiscount < 0 || itemDiscount > gross) {
                valid = false;
                message = 'Item discount cannot exceed the item total.';
                return;
            }
        });

        if (!valid) {
            event.preventDefault();
            alert(message);
            return;
        }

        const invoiceDiscount = Number(discountInput.value || 0);
        const paidAmount = Number(paidAmountInput.value || 0);

        let subtotal = 0;
        let itemDiscountTotal = 0;

        rows.forEach(function (row) {
            const variant = getVariant(row.querySelector('.variant-select').value);

            if (!variant) {
                return;
            }

            const quantity = Number(
                row.querySelector('.quantity-input').value || 0
            );

            const itemDiscount = Number(
                row.querySelector('.item-discount-input').value || 0
            );

            subtotal += quantity * Number(variant.price || 0);
            itemDiscountTotal += itemDiscount;
        });

        const remainingTotal = Math.max(0, subtotal - itemDiscountTotal);

        if (invoiceDiscount < 0 || invoiceDiscount > remainingTotal) {
            event.preventDefault();
            alert('Invoice discount cannot exceed the remaining sale total.');
            return;
        }

        const grandTotal = Math.max(
            0,
            subtotal - itemDiscountTotal - invoiceDiscount
        );

        if (paidAmount < 0 || paidAmount > grandTotal) {
            event.preventDefault();
            alert('Paid amount cannot exceed the grand total.');
        }
    });

    if (existingItems.length > 0) {
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
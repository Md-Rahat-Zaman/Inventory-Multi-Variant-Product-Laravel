@extends('layouts.app_bootstrap')

@section('title', 'Point of Sale')

@section('content')
@php
    $posVariants = $variants->map(function ($variant) {
        $productName = $variant->product->name ?? 'Unknown Product';
        $colorName = $variant->color->name ?? '';
        $sizeName = $variant->size->name ?? '';

        return [
            'id' => (int) $variant->id,
            'product_id' => (int) $variant->product_id,
            'name' => $productName,
            'color' => $colorName,
            'size' => $sizeName,
            'sku' => $variant->product->sku ?? '',
            'barcode' => $variant->product->barcode ?? '',
            'price' => (float) $variant->sale_price,
            'stock' => (float) $variant->stock,
            'label' => trim(
                $productName . ' ' . $colorName . ' ' . $sizeName
            ),
        ];
    })->values();
@endphp

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-cart3 me-2"></i>Point of Sale
            </h4>
            <p class="text-muted mb-0">Sell products and manage payments.</p>
        </div>

        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-receipt me-1"></i> Sales History
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('sales.store') }}" method="POST" id="posForm">
        @csrf

        <div class="row g-3">

            {{-- Product Selection --}}
            <div class="col-xl-7">

                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-semibold mb-0">Products</h5>
                            <span class="badge bg-primary-subtle text-primary"
                                  id="productCount">0 products</span>
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- Search --}}
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>

                            <input type="text"
                                   id="productSearch"
                                   class="form-control"
                                   placeholder="Search product, SKU, barcode, color or size..."
                                   autocomplete="off">

                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="clearSearch">
                                Clear
                            </button>
                        </div>

                        <div class="small text-muted mb-3">
                            Search by product name or barcode, then click anywhere on the product card or Add to Cart.
                            Barcode scanner: scan the barcode and press Enter to add it automatically when it matches one variant.
                        </div>

                        {{-- Product Cards --}}
                        <div class="row g-3" id="productGrid"></div>

                        <div id="noProducts"
                             class="text-center text-muted py-5 d-none">
                            <i class="bi bi-search fs-1 d-block mb-2"></i>
                            No matching products found.
                        </div>

                    </div>
                </div>

            </div>

            {{-- Cart and Checkout --}}
            <div class="col-xl-5">

                {{-- Cart --}}
                <div class="card border-0 shadow-sm mb-3">

                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-basket2 me-2"></i>Shopping Cart
                        </h5>

                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                id="clearCart">
                            <i class="bi bi-trash me-1"></i> Clear Cart
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Product</th>
                                        <th style="min-width: 85px;">Qty</th>
                                        <th style="min-width: 95px;">Discount</th>
                                        <th class="text-end pe-3">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody id="cartBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                                            Cart is empty.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="card-body border-top">

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <strong id="subtotalDisplay">0.00</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Item Discount</span>
                            <strong id="itemDiscountDisplay">0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="discount" class="form-label fw-semibold">
                                Invoice Discount
                            </label>

                            <input type="number"
                                   name="discount"
                                   id="discount"
                                   class="form-control"
                                   value="{{ old('discount', 0) }}"
                                   min="0"
                                   step="0.01">

                            <div class="form-text">
                                Additional discount for the entire sale.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
                            <span class="fw-bold fs-5">Grand Total</span>
                            <span class="fw-bold fs-4 text-primary"
                                  id="grandTotalDisplay">0.00</span>
                        </div>

                    </div>
                </div>

                {{-- Checkout --}}
                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-credit-card me-2"></i>Checkout
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label for="customer_id" class="form-label fw-semibold">
                                Customer
                            </label>

                            <select name="customer_id"
                                    id="customer_id"
                                    class="form-select">
                                <option value="">Walk-in Customer</option>

                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        data-name="{{ $customer->name }}"
                                        data-phone="{{ $customer->phone }}"
                                        @selected((string) old('customer_id') === (string) $customer->id)>
                                        {{ $customer->name }}
                                        @if ($customer->phone)
                                            — {{ $customer->phone }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label">
                                    Customer Name
                                </label>

                                <input type="text"
                                       name="customer_name"
                                       id="customer_name"
                                       class="form-control"
                                       maxlength="150"
                                       value="{{ old('customer_name') }}"
                                       placeholder="Walk-in customer">
                            </div>

                            <div class="col-md-6">
                                <label for="customer_phone" class="form-label">
                                    Phone
                                </label>

                                <input type="text"
                                       name="customer_phone"
                                       id="customer_phone"
                                       class="form-control"
                                       maxlength="30"
                                       value="{{ old('customer_phone') }}"
                                       placeholder="Customer phone">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sale_date" class="form-label fw-semibold">
                                Sale Date
                            </label>

                            <input type="date"
                                   name="sale_date"
                                   id="sale_date"
                                   class="form-control"
                                   value="{{ old('sale_date', now()->format('Y-m-d')) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="paid_amount" class="form-label fw-semibold">
                                Paid Amount
                            </label>

                            <input type="number"
                                   name="paid_amount"
                                   id="paid_amount"
                                   class="form-control form-control-lg"
                                   value="{{ old('paid_amount', 0) }}"
                                   min="0"
                                   step="0.01"
                                   required>

                            <div class="form-text">
                                Enter the amount received now. Leave 0 for a fully unpaid sale.
                            </div>
                        </div>

                        <div class="rounded-3 bg-light p-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Grand Total</span>
                                <strong id="checkoutTotal">0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Paid</span>
                                <strong class="text-success" id="paidDisplay">0.00</strong>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">Due</span>
                                <strong class="text-danger" id="dueDisplay">0.00</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>

                            <textarea name="note"
                                      id="note"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Optional note">{{ old('note') }}</textarea>
                        </div>

                        <button type="submit"
                                id="completeSale"
                                class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-check-circle me-2"></i>
                            Complete Sale
                        </button>

                        <div class="text-muted small text-center mt-2">
                            Stock will be updated after the sale is saved.
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<style>
    .product-card:focus-visible {
        outline: 3px solid var(--bs-primary);
        outline-offset: 2px;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const variants = {{ Illuminate\Support\Js::from($posVariants) }};

    const productGrid = document.getElementById('productGrid');
    const productSearch = document.getElementById('productSearch');
    const productCount = document.getElementById('productCount');
    const noProducts = document.getElementById('noProducts');

    const cartBody = document.getElementById('cartBody');
    const discountInput = document.getElementById('discount');
    const paidInput = document.getElementById('paid_amount');

    const customerSelect = document.getElementById('customer_id');
    const customerName = document.getElementById('customer_name');
    const customerPhone = document.getElementById('customer_phone');

    const completeSaleButton = document.getElementById('completeSale');

    let cart = [];

    function money(value) {
        return Number(value || 0).toLocaleString('en-BD', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            const replacements = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return replacements[char];
        });
    }

    function variantLabel(variant) {
        let label = variant.name;

        if (variant.color) {
            label += ' / ' + variant.color;
        }

        if (variant.size) {
            label += ' / ' + variant.size;
        }

        return label;
    }

    function renderProducts() {
        const search = productSearch.value.trim().toLowerCase();

        const filteredVariants = variants.filter(function (variant) {
            const searchableText = [
                variant.name,
                variant.sku,
                variant.barcode,
                variant.color,
                variant.size,
                variant.label
            ].join(' ').toLowerCase();

            return searchableText.includes(search);
        });

        productCount.textContent = filteredVariants.length + ' products';
        noProducts.classList.toggle('d-none', filteredVariants.length > 0);

        productGrid.innerHTML = filteredVariants.map(function (variant) {
            const alreadyInCart = cart.some(function (item) {
                return item.id === variant.id;
            });

            return `
                <div class="col-sm-6 col-lg-6">
                    <div class="card h-100 border product-card"
                         data-add-variant="${variant.id}"
                         role="button"
                         tabindex="0"
                         title="Click to add this product to the cart"
                         style="cursor: pointer;">
                        <div class="card-body d-flex flex-column">
                            <div class="fw-semibold mb-2">
                                ${escapeHtml(variant.name)}
                            </div>

                            <div class="small text-muted mb-1">
                                ${escapeHtml(variant.color || 'No color')}
                                /
                                ${escapeHtml(variant.size || 'No size')}
                            </div>

                            <div class="small text-muted mb-2">
                                SKU: ${escapeHtml(variant.sku || '—')}
                            </div>

                            <div class="small text-muted mb-2">
                                Stock: ${money(variant.stock)}
                            </div>

                            <div class="fw-bold text-primary fs-5 mb-3">
                                ${money(variant.price)}
                            </div>

                            <button type="button"
                                    class="btn btn-sm ${alreadyInCart ? 'btn-outline-secondary' : 'btn-outline-primary'} mt-auto"
                                    data-add-variant="${variant.id}"
                                    ${alreadyInCart || variant.stock <= 0 ? 'disabled' : ''}>
                                <i class="bi bi-plus-circle me-1"></i>
                                ${alreadyInCart ? 'Added to Cart' : 'Add to Cart'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function addToCart(variantId) {
        const variant = variants.find(function (item) {
            return item.id === Number(variantId);
        });

        if (!variant || variant.stock <= 0) {
            return;
        }

        const existingItem = cart.find(function (item) {
            return item.id === variant.id;
        });

        if (existingItem) {
            if (existingItem.quantity + 1 > variant.stock) {
                alert('Not enough stock available.');
                return;
            }

            existingItem.quantity += 1;
        } else {
            cart.push({
                id: variant.id,
                name: variant.name,
                color: variant.color,
                size: variant.size,
                price: variant.price,
                stock: variant.stock,
                quantity: 1,
                discount: 0
            });
        }

        renderCart();
        renderProducts();
    }

    function renderCart() {
        if (cart.length === 0) {
            cartBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                        Cart is empty.
                    </td>
                </tr>
            `;

            updateTotals();
            return;
        }

        cartBody.innerHTML = cart.map(function (item, index) {
            const lineTotal = Math.max(
                0,
                (item.quantity * item.price) - item.discount
            );

            return `
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold">
                            ${escapeHtml(item.name)}
                        </div>

                        <div class="small text-muted">
                            ${escapeHtml(item.color || '')}
                            ${item.color && item.size ? '/' : ''}
                            ${escapeHtml(item.size || '')}
                        </div>

                        <div class="small text-muted">
                            ${money(item.price)} each
                        </div>

                        <input type="hidden"
                               name="items[${index}][product_variant_id]"
                               value="${item.id}">
                    </td>

                    <td>
                        <input type="number"
                               name="items[${index}][quantity]"
                               class="form-control form-control-sm cart-quantity"
                               data-index="${index}"
                               min="0.01"
                               max="${item.stock}"
                               step="any"
                               value="${item.quantity}"
                               required>
                    </td>

                    <td>
                        <input type="number"
                               name="items[${index}][discount]"
                               class="form-control form-control-sm cart-discount"
                               data-index="${index}"
                               min="0"
                               step="0.01"
                               value="${item.discount}"
                               required>
                    </td>

                    <td class="text-end pe-2 fw-semibold">
                        ${money(lineTotal)}
                    </td>

                    <td class="text-end pe-2">
                        <button type="button"
                                class="btn btn-sm btn-outline-danger remove-item"
                                data-index="${index}"
                                title="Remove item">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        updateTotals();
    }

    function getTotals() {
        let subtotal = 0;
        let itemDiscountTotal = 0;

        cart.forEach(function (item) {
            subtotal += item.quantity * item.price;
            itemDiscountTotal += item.discount;
        });

        subtotal = Math.round((subtotal + Number.EPSILON) * 100) / 100;
        itemDiscountTotal = Math.round(
            (itemDiscountTotal + Number.EPSILON) * 100
        ) / 100;

        const remainingAmount = Math.max(
            0,
            subtotal - itemDiscountTotal
        );

        const invoiceDiscount = Math.max(
            0,
            Number(discountInput.value) || 0
        );

        const grandTotal = Math.max(
            0,
            remainingAmount - invoiceDiscount
        );

        const paid = Math.max(
            0,
            Number(paidInput.value) || 0
        );

        const due = Math.max(0, grandTotal - paid);

        return {
            subtotal: subtotal,
            itemDiscount: itemDiscountTotal,
            invoiceDiscount: invoiceDiscount,
            grandTotal: Math.round(grandTotal * 100) / 100,
            paid: paid,
            due: Math.round(due * 100) / 100
        };
    }

    function updateTotals() {
        const totals = getTotals();

        document.getElementById('subtotalDisplay').textContent =
            money(totals.subtotal);

        document.getElementById('itemDiscountDisplay').textContent =
            money(totals.itemDiscount);

        document.getElementById('grandTotalDisplay').textContent =
            money(totals.grandTotal);

        document.getElementById('checkoutTotal').textContent =
            money(totals.grandTotal);

        document.getElementById('paidDisplay').textContent =
            money(totals.paid);

        document.getElementById('dueDisplay').textContent =
            money(totals.due);

        const invalidDiscount =
            totals.invoiceDiscount > totals.subtotal - totals.itemDiscount;

        const invalidPaid = totals.paid > totals.grandTotal;

        discountInput.setCustomValidity(
            invalidDiscount
                ? 'Invoice discount exceeds the remaining sale amount.'
                : ''
        );

        paidInput.setCustomValidity(
            invalidPaid
                ? 'Paid amount cannot exceed the grand total.'
                : ''
        );

        completeSaleButton.disabled = cart.length === 0;
    }

    productGrid.addEventListener('click', function (event) {
        const productCard = event.target.closest('[data-add-variant]');

        if (!productCard) {
            return;
        }

        addToCart(productCard.dataset.addVariant);
    });

    // Allow keyboard users to add a product while a product card is focused.
    productGrid.addEventListener('keydown', function (event) {
        const productCard = event.target.closest('.product-card');

        if (!productCard || event.target.closest('button')) {
            return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            addToCart(productCard.dataset.addVariant);
        }
    });

    // Barcode scanners commonly type the barcode and send Enter afterward.
    // Automatically add only when the barcode matches exactly one variant.
    productSearch.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') {
            return;
        }

        const searchedValue = productSearch.value.trim().toLowerCase();

        if (!searchedValue) {
            return;
        }

        const exactBarcodeMatches = variants.filter(function (variant) {
            return String(variant.barcode || '').trim().toLowerCase() === searchedValue;
        });

        if (exactBarcodeMatches.length === 1) {
            event.preventDefault();
            addToCart(exactBarcodeMatches[0].id);
            productSearch.value = '';
            renderProducts();
            return;
        }

        if (exactBarcodeMatches.length > 1) {
            event.preventDefault();
            alert('This barcode matches multiple variants. Please click the correct product card.');
        }
    });

    cartBody.addEventListener('change', function (event) {
        const index = Number(event.target.dataset.index);

        if (!Number.isInteger(index) || !cart[index]) {
            return;
        }

        if (event.target.classList.contains('cart-quantity')) {
            const quantity = Number(event.target.value);

            if (
                !Number.isFinite(quantity) ||
                quantity <= 0 ||
                quantity > cart[index].stock
            ) {
                alert('Quantity must be greater than zero and cannot exceed available stock.');
                renderCart();
                return;
            }

            cart[index].quantity = quantity;
            renderCart();
        }

        if (event.target.classList.contains('cart-discount')) {
            const discount = Number(event.target.value);
            const lineGross = cart[index].quantity * cart[index].price;

            if (
                !Number.isFinite(discount) ||
                discount < 0 ||
                discount > lineGross
            ) {
                alert('Item discount cannot exceed the line amount.');
                renderCart();
                return;
            }

            cart[index].discount = discount;
            renderCart();
        }
    });

    cartBody.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-item');

        if (!button) {
            return;
        }

        cart.splice(Number(button.dataset.index), 1);

        renderCart();
        renderProducts();
    });

    productSearch.addEventListener('input', renderProducts);

    document.getElementById('clearSearch').addEventListener('click', function () {
        productSearch.value = '';
        renderProducts();
        productSearch.focus();
    });

    document.getElementById('clearCart').addEventListener('click', function () {
        if (cart.length > 0 && !confirm('Are you sure you want to clear the cart?')) {
            return;
        }

        cart = [];

        renderCart();
        renderProducts();
    });

    discountInput.addEventListener('input', updateTotals);
    paidInput.addEventListener('input', updateTotals);

    customerSelect.addEventListener('change', function () {
        const option = customerSelect.options[customerSelect.selectedIndex];

        if (customerSelect.value) {
            customerName.value = option.dataset.name || '';
            customerPhone.value = option.dataset.phone || '';
        }
    });

    document.getElementById('posForm').addEventListener('submit', function (event) {
        if (cart.length === 0) {
            event.preventDefault();
            alert('Please add at least one product to the cart.');
            return;
        }

        const totals = getTotals();

        if (totals.invoiceDiscount > totals.subtotal - totals.itemDiscount) {
            event.preventDefault();
            alert('Invoice discount cannot exceed the remaining sale amount.');
            discountInput.focus();
            return;
        }

        if (totals.paid > totals.grandTotal) {
            event.preventDefault();
            alert('Paid amount cannot exceed the grand total.');
            paidInput.focus();
            return;
        }

        for (const item of cart) {
            if (
                item.quantity <= 0 ||
                item.quantity > item.stock ||
                item.discount < 0 ||
                item.discount > item.quantity * item.price
            ) {
                event.preventDefault();
                alert('Please check product quantity and discount.');
                return;
            }
        }

        completeSaleButton.disabled = true;
        completeSaleButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Processing Sale...';
    });

    renderProducts();
    renderCart();

    if (customerSelect.value) {
        customerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
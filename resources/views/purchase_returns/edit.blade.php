@extends('layouts.app_bootstrap')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Edit Purchase Return: {{ $purchaseReturn->return_no }}</h4>

        <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Purchase</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $purchase->purchase_no ?? $purchase->id }}"
                           readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Supplier</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $purchase->supplier->name ?? 'N/A' }}"
                           readonly>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('purchase-returns.update', $purchaseReturn) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Return Date</label>
                        <input type="date"
                               name="return_date"
                               class="form-control"
                               value="{{ old('return_date', $purchaseReturn->return_date->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Note</label>
                        <input type="text"
                               name="note"
                               class="form-control"
                               value="{{ old('note', $purchaseReturn->note) }}">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Purchased Qty</th>
                                <th>Max Return Qty</th>
                                <th class="text-end">Purchase Price</th>
                                <th style="width:160px">Return Qty</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($purchaseItems as $item)
                                @php
                                    $currentQty = (float) ($currentQuantities[$item->id] ?? 0);
                                    $maxQty = (float) $item->returnable_quantity;
                                    $inputQty = old('items.' . $loop->index . '.quantity', $currentQty);
                                @endphp

                                <tr>
                                    <td>
                                        {{ $item->product->name ?? 'Product #' . $item->product_id }}
                                        <div class="small text-muted">
                                            Variant #{{ $item->product_variant_id }}
                                        </div>
                                    </td>

                                    <td>{{ number_format((float) $item->quantity, 3) }}</td>

                                    <td>{{ number_format($maxQty, 3) }}</td>

                                    <td class="text-end">
                                        {{ number_format((float) $item->purchase_price, 2) }}
                                    </td>

                                    <td>
                                        <input type="hidden"
                                               name="items[{{ $loop->index }}][purchase_item_id]"
                                               value="{{ $item->id }}">

                                        <input type="number"
                                               name="items[{{ $loop->index }}][quantity]"
                                               class="form-control return-qty"
                                               min="0"
                                               max="{{ $maxQty }}"
                                               step="0.001"
                                               value="{{ $inputQty }}"
                                               data-price="{{ $item->purchase_price }}">
                                    </td>

                                    <td class="text-end line-total">0.00</td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total Return</th>
                                <th class="text-end" id="grand-total">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Update this purchase return?')">
                    <i class="bi bi-check-circle"></i> Update Purchase Return
                </button>
            </form>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.return-qty');
    const grandTotal = document.getElementById('grand-total');

    function calculateTotal() {
        let total = 0;

        inputs.forEach(function (input) {
            const quantity = parseFloat(input.value) || 0;
            const max = parseFloat(input.max) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            const rowTotal = quantity * price;

            input.setCustomValidity(
                quantity > max
                    ? 'Return quantity cannot exceed returnable quantity.'
                    : ''
            );

            input.closest('tr').querySelector('.line-total').textContent =
                rowTotal.toFixed(2);

            total += rowTotal;
        });

        grandTotal.textContent = total.toFixed(2);
    }

    inputs.forEach(input => input.addEventListener('input', calculateTotal));
    calculateTotal();
});
</script>
@endsection
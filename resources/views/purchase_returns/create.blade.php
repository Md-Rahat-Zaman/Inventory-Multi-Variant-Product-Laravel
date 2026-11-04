@extends('layouts.app_bootstrap')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Add Purchase Return</h4>

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

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('purchase-returns.create') }}">
                <label class="form-label">Select Purchase</label>

                <div class="input-group">
                    <select name="purchase_id" class="form-select" required>
                        <option value="">Choose Purchase</option>

                        @foreach($purchases as $p)
                            <option value="{{ $p->id }}"
                                @selected((string) $selectedPurchaseId === (string) $p->id)>
                                {{ $p->purchase_no ?? ('Purchase #' . $p->id) }}
                                — {{ $p->supplier->name ?? 'Supplier not found' }}
                            </option>
                        @endforeach
                    </select>

                    <button class="btn btn-primary" type="submit">
                        Load Products
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($purchase)
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="mb-3">
                    <strong>Purchase:</strong>
                    {{ $purchase->purchase_no ?? $purchase->id }}

                    <span class="ms-3">
                        <strong>Supplier:</strong>
                        {{ $purchase->supplier->name ?? 'N/A' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('purchase-returns.store') }}">
                    @csrf

                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Return Date</label>
                            <input type="date"
                                   name="return_date"
                                   class="form-control"
                                   value="{{ old('return_date', now()->toDateString()) }}"
                                   required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Note</label>
                            <input type="text"
                                   name="note"
                                   class="form-control"
                                   value="{{ old('note') }}"
                                   placeholder="Optional note">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Purchase Qty</th>
                                    <th>Already Returned</th>
                                    <th>Returnable Qty</th>
                                    <th class="text-end">Purchase Price</th>
                                    <th style="width:160px">Return Qty</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($purchaseItems as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product->name ?? 'Product #' . $item->product_id }}
                                            <div class="small text-muted">
                                                Variant #{{ $item->product_variant_id }}
                                            </div>
                                        </td>

                                        <td>{{ number_format((float) $item->quantity, 3) }}</td>

                                        <td>
                                            {{ number_format((float) $item->already_returned_quantity, 3) }}
                                        </td>

                                        <td>{{ number_format((float) $item->returnable_quantity, 3) }}</td>

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
                                                   max="{{ $item->returnable_quantity }}"
                                                   step="0.001"
                                                   value="{{ old('items.' . $loop->index . '.quantity', 0) }}"
                                                   data-price="{{ $item->purchase_price }}"
                                                   {{ $item->returnable_quantity <= 0 ? 'disabled' : '' }}>
                                        </td>

                                        <td class="text-end line-total">0.00</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No purchase items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-end">Total Return</th>
                                    <th class="text-end" id="grand-total">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button type="submit"
                            class="btn btn-success"
                            onclick="return confirm('Save this purchase return?')">
                        <i class="bi bi-check-circle"></i> Save Purchase Return
                    </button>
                </form>
            </div>
        </div>
    @endif

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

        if (grandTotal) {
            grandTotal.textContent = total.toFixed(2);
        }
    }

    inputs.forEach(input => input.addEventListener('input', calculateTotal));
    calculateTotal();
});
</script>
@endsection
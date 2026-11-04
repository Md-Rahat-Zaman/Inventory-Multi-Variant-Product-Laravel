@extends('layouts.app_bootstrap')

@section('title', 'Stock Adjustment')

@section('content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('inventory.index') }}" class="text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Back to stock overview
            </a>
            <h1 class="h3 mt-2 mb-1">Stock Adjustment</h1>
            <p class="text-muted mb-0">Increase, decrease, or set stock and record why it changed.</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.adjustments.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label for="item" class="form-label">Product / variant</label>
                            <select id="item" name="item" class="form-select @error('item') is-invalid @enderror" required>
                                <option value="">Select an inventory item</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item['key'] }}" @selected(old('item') === $item['key'])>
                                        {{ $item['name'] }}{{ $item['variant'] ? ' — '.$item['variant'] : '' }}
                                        (Stock: {{ number_format($item['stock'], 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('item')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-4">
                            <label for="adjustment_type" class="form-label">Adjustment type</label>
                            <select id="adjustment_type" name="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                <option value="increase" @selected(old('adjustment_type') === 'increase')>Increase stock</option>
                                <option value="decrease" @selected(old('adjustment_type') === 'decrease')>Decrease stock</option>
                                <option value="set" @selected(old('adjustment_type') === 'set')>Set exact stock</option>
                            </select>
                            @error('adjustment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="0" step="0.01"
                                   value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror" required>
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="minimum_stock" class="form-label">Minimum stock level</label>
                            <input id="minimum_stock" name="minimum_stock" type="number" min="0" step="0.01"
                                   value="{{ old('minimum_stock') }}" class="form-control @error('minimum_stock') is-invalid @enderror">
                            <div class="form-text">Optional. Set this to enable low-stock alerts for the item.</div>
                            @error('minimum_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="reason" class="form-label">Reason</label>
                            <input id="reason" name="reason" type="text" maxlength="255" value="{{ old('reason') }}"
                                   class="form-control @error('reason') is-invalid @enderror" required>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

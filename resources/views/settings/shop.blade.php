@extends('layouts.app_bootstrap')

@section('title', 'Shop Information')

@section('content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <h1 class="h3 mb-1">Shop Information</h1>
            <p class="text-muted mb-0">Manage the details and branding shown across your inventory system.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('shop-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 mb-0">Shop profile</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="shop_name" class="form-label">Shop name <span class="text-danger">*</span></label>
                            <input id="shop_name" name="shop_name" type="text"
                                   value="{{ old('shop_name', $shopSettings->shop_name) }}"
                                   class="form-control @error('shop_name') is-invalid @enderror" maxlength="150" required>
                            @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tagline" class="form-label">Tagline</label>
                            <input id="tagline" name="tagline" type="text"
                                   value="{{ old('tagline', $shopSettings->tagline) }}"
                                   class="form-control @error('tagline') is-invalid @enderror" maxlength="255">
                            @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email"
                                   value="{{ old('email', $shopSettings->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" maxlength="255">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input id="phone" name="phone" type="tel"
                                   value="{{ old('phone', $shopSettings->phone) }}"
                                   class="form-control @error('phone') is-invalid @enderror" maxlength="50">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror"
                                      maxlength="2000">{{ old('address', $shopSettings->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="website" class="form-label">Website</label>
                            <input id="website" name="website" type="url"
                                   value="{{ old('website', $shopSettings->website) }}"
                                   class="form-control @error('website') is-invalid @enderror"
                                   placeholder="https://example.com" maxlength="255">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="tax_number" class="form-label">Tax / VAT number</label>
                            <input id="tax_number" name="tax_number" type="text"
                                   value="{{ old('tax_number', $shopSettings->tax_number) }}"
                                   class="form-control @error('tax_number') is-invalid @enderror" maxlength="100">
                            @error('tax_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label for="currency_code" class="form-label">Currency code</label>
                            <input id="currency_code" name="currency_code" type="text"
                                   value="{{ old('currency_code', $shopSettings->currency_code) }}"
                                   class="form-control text-uppercase @error('currency_code') is-invalid @enderror"
                                   minlength="3" maxlength="3" pattern="[A-Za-z]{3}" required>
                            @error('currency_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 mb-0">Shop logo</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="border rounded-3 bg-light d-flex align-items-center justify-content-center"
                             style="width: 96px; height: 96px; overflow: hidden;">
                            @if ($shopSettings->logo_path)
                                <img id="shop-logo-preview" src="{{ asset('storage/'.$shopSettings->logo_path) }}"
                                     alt="{{ $shopSettings->shop_name }} logo"
                                     style="width: 100%; height: 100%; object-fit: contain;">
                            @else
                                <img id="shop-logo-preview" src="" alt="Shop logo preview"
                                     style="display: none; width: 100%; height: 100%; object-fit: contain;">
                                <i id="shop-logo-placeholder" class="bi bi-shop fs-2 text-secondary"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <label for="logo" class="form-label">Upload logo</label>
                            <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                   class="form-control @error('logo') is-invalid @enderror">
                            <div class="form-text">JPG, PNG or WebP; maximum file size 2 MB. The logo appears on login and in the sidebar.</div>
                            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($shopSettings->logo_path)
                                <div class="form-check mt-2">
                                    <input id="remove_logo" name="remove_logo" type="checkbox" value="1"
                                           class="form-check-input" @checked(old('remove_logo'))>
                                    <label for="remove_logo" class="form-check-label">Remove current logo</label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i>Save shop information
                    </button>
                </div>
            </div>
        </form>
    </div>
@push('scripts')
    <script>
        document.getElementById('logo')?.addEventListener('change', function () {
            const preview = document.getElementById('shop-logo-preview');
            const placeholder = document.getElementById('shop-logo-placeholder');
            const file = this.files?.[0];
            if (!file) return;

            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        });
    </script>
@endpush
@endsection

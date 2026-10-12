{{-- =================================================
                     Pricing & Inventory
                ================================================== --}}
                <div class="form-section">

                    <div class="form-section-header">

                        <h6 class="form-section-title">
                            Pricing & Inventory
                        </h6>

                    </div>


                    <div class="row g-3">

                        {{-- Purchase Price --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Purchase Price
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="purchase_price"
                                    value="{{ old('purchase_price') }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('purchase_price') is-invalid @enderror"
                                    placeholder="0.00"
                                    required
                                >

                                @error('purchase_price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Sale Price --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Sale Price
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="sale_price"
                                    value="{{ old('sale_price') }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('sale_price') is-invalid @enderror"
                                    placeholder="0.00"
                                    required
                                >

                                @error('sale_price')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Tax --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Tax (%)
                                </label>

                                <input
                                    type="number"
                                    name="tax_rate"
                                    value="{{ old('tax_rate', 0) }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('tax_rate') is-invalid @enderror"
                                    placeholder="0.00"
                                >

                                @error('tax_rate')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Opening Stock --}}
                        {{-- <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Opening Stock
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="opening_stock"
                                    value="{{ old('opening_stock', 0) }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('opening_stock') is-invalid @enderror"
                                    placeholder="0"
                                    required
                                >

                                @error('opening_stock')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Minimum Stock --}}
                        {{-- <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Minimum Stock
                                </label>

                                <input
                                    type="number"
                                    name="minimum_stock"
                                    value="{{ old('minimum_stock', 0) }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control @error('minimum_stock') is-invalid @enderror"
                                    placeholder="0"
                                >

                                @error('minimum_stock')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Status --}}
                        <div class="col-lg-3 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Status
                                </label>

                                <select
                                    name="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                >

                                    <option value="1"
                                        @selected(old('status', 1) == 1)>
                                        Active
                                    </option>

                                    <option value="0"
                                        @selected(old('status') == 0)>
                                        Inactive
                                    </option>

                                </select>

                                @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>
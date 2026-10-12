{{-- =================================================
                     Basic Information
                ================================================== --}}
                <div class="form-section">

                    <div class="form-section-header">

                        <h6 class="form-section-title">
                            Basic Information
                        </h6>

                        <span class="form-required-note">
                            * Required
                        </span>

                    </div>


                    <div class="row g-3">

                        {{-- Product Name --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Product Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter product name"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- SKU --}}
                        {{-- <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    SKU
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="sku"
                                    value="{{ old('sku') }}"
                                    class="form-control @error('sku') is-invalid @enderror"
                                    placeholder="e.g. PRD-00001"
                                    required
                                >

                                @error('sku')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Barcode --}}
                        {{-- <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Barcode
                                </label>

                                <input
                                    type="text"
                                    name="barcode"
                                    value="{{ old('barcode') }}"
                                    class="form-control @error('barcode') is-invalid @enderror"
                                    placeholder="Enter barcode"
                                >

                                @error('barcode')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div> --}}


                        {{-- Category --}}
                        {{-- Category --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <div class="d-flex justify-content-between align-items-center mb-1">

                                    <label class="form-label mb-0">
                                        Category
                                        <span class="text-danger">*</span>
                                    </label>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary quick-create-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#quickCreateModal"
                                            data-type="category"
                                            data-mode="create">

                                        <i class="bi bi-plus-lg"></i>
                                        Add

                                    </button>

                                </div>

                                <select
                                    name="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror"
                                    required
                                >

                                    <option value="">
                                        Select category
                                    </option>

                                    @foreach($categories ?? [] as $category)

                                        <option
                                            value="{{ $category->id }}"
                                            @selected(old('category_id') == $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('category_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Brand --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Brand
                                </label>

                                <select
                                    name="brand_id"
                                    class="form-select @error('brand_id') is-invalid @enderror"
                                >

                                    <option value="">
                                        Select brand
                                    </option>

                                    @foreach($brands ?? [] as $brand)

                                        <option
                                            value="{{ $brand->id }}"
                                            @selected(old('brand_id') == $brand->id)
                                        >
                                            {{ $brand->name }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('brand_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Unit --}}
                        <div class="col-lg-4 col-md-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Unit
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="unit_id"
                                    class="form-select @error('unit_id') is-invalid @enderror"
                                    required
                                >

                                    <option value="">
                                        Select unit
                                    </option>

                                    @foreach($units ?? [] as $unit)

                                        <option
                                            value="{{ $unit->id }}"
                                            @selected(old('unit_id') == $unit->id)
                                        >
                                            {{ $unit->name }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('unit_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>
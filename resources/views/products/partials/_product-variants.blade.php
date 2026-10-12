  {{-- variant section --}}
                {{-- =================================================
                        Product Variants
                    ================================================== --}}
                    <div class="form-section">

                        <div class="form-section-header">

                            <div>
                                <h6 class="form-section-title mb-1">
                                    Product Variants
                                </h6>

                                <p class="text-muted small mb-0">
                                    Select colors and sizes for this product.
                                </p>
                            </div>

                        </div>


                        <div class="row g-3">

                            {{-- Colors --}}
                            <div class="col-lg-6">

                                <div class="form-group">

                                    <label class="form-label">
                                        Colors
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        name="colors[]"
                                        class="form-select"
                                        multiple
                                        id="variantColors"
                                    >

                                        @foreach($colors ?? [] as $color)

                                            <option
                                                value="{{ $color->id }}"
                                                @selected(in_array(
                                                    $color->id,
                                                    old('colors', [])
                                                ))
                                            >
                                                {{ $color->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                    <div class="form-text">
                                        Hold Ctrl to select multiple colors.
                                    </div>

                                    @error('colors')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>


                            {{-- Sizes --}}
                            <div class="col-lg-6">

                                <div class="form-group">

                                    <label class="form-label">
                                        Sizes
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        name="sizes[]"
                                        class="form-select"
                                        multiple
                                        id="variantSizes"
                                    >

                                        @foreach($sizes ?? [] as $size)

                                            <option
                                                value="{{ $size->id }}"
                                                @selected(in_array(
                                                    $size->id,
                                                    old('sizes', [])
                                                ))
                                            >
                                                {{ $size->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                    <div class="form-text">
                                        Hold Ctrl to select multiple sizes.
                                    </div>

                                    @error('sizes')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>

                        </div>


                        {{-- Generate Button --}}
                        <div class="mt-3">

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="generateVariants"
                            >
                                <i class="bi bi-shuffle"></i>
                                Generate Variants
                            </button>

                        </div>


                        {{-- Generated Variants --}}
                        <div
                            id="variantContainer"
                            class="mt-4 d-none"
                        >

                            <div class="table-responsive">

                                <table class="table table-bordered align-middle">

                                    <thead class="table-light">

                                        <tr>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>SKU</th>
                                            <th>Barcode</th>
                                            <th>Purchase Price</th>
                                            <th>Sale Price</th>
                                            <th>Opening Stock</th>
                                            <th></th>
                                        </tr>

                                    </thead>

                                    <tbody id="variantTableBody">

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>
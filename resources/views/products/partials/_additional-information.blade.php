{{-- =================================================
                     Additional Information
                ================================================== --}}
                <div class="form-section">

                    <div class="form-section-header">

                        <h6 class="form-section-title">
                            Additional Information
                        </h6>

                    </div>


                    <div class="row g-3">

                        {{-- Note --}}
                        <div class="col-lg-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Note
                                </label>

                                <textarea
                                    name="note"
                                    rows="3"
                                    class="form-control @error('note') is-invalid @enderror"
                                    placeholder="Optional internal note..."
                                >{{ old('note') }}</textarea>

                                @error('note')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        {{-- Description --}}
                        <div class="col-lg-6">

                            <div class="form-group">

                                <label class="form-label">
                                    Description
                                </label>

                                <textarea
                                    name="description"
                                    rows="3"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Write product description..."
                                >{{ old('description') }}</textarea>

                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>
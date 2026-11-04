
<div
    class="modal fade"
    id="partyQuickCreateModal"
    tabindex="-1"
    aria-labelledby="partyQuickCreateModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="partyQuickCreateModalLabel">
                        <i class="bi bi-person-plus me-2"></i>
                        Add Customer / Supplier
                    </h5>
                    <div class="small text-muted">
                        Add a party without leaving this page.
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <form
                id="partyQuickCreateForm"
                action="{{ route('customers.ajax-store') }}"
                method="POST"
            >
                @csrf

                <div class="modal-body">
                    <div
                        id="partyQuickCreateAlert"
                        class="alert d-none"
                        role="alert"
                    ></div>

                    <input type="hidden" id="partyTargetSelect" value="">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label for="partyName" class="form-label">
                                Name <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                id="partyName"
                                class="form-control"
                                maxlength="255"
                                required
                            >

                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>

                        <div class="col-md-4">
                            <label for="partyType" class="form-label">
                                Party Type <span class="text-danger">*</span>
                            </label>

                            <select
                                name="type"
                                id="partyType"
                                class="form-select"
                                required
                            >
                                <option value="customer">Customer</option>
                                <option value="supplier">Supplier</option>
                                <option value="both">Both</option>
                            </select>

                            <div class="invalid-feedback" data-error-for="type"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="partyPhone" class="form-label">Phone</label>

                            <input
                                type="text"
                                name="phone"
                                id="partyPhone"
                                class="form-control"
                                maxlength="30"
                            >

                            <div class="invalid-feedback" data-error-for="phone"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="partyEmail" class="form-label">Email</label>

                            <input
                                type="email"
                                name="email"
                                id="partyEmail"
                                class="form-control"
                                maxlength="255"
                            >

                            <div class="invalid-feedback" data-error-for="email"></div>
                        </div>

                        <div class="col-12">
                            <label for="partyAddress" class="form-label">Address</label>

                            <textarea
                                name="address"
                                id="partyAddress"
                                class="form-control"
                                rows="2"
                                maxlength="2000"
                            ></textarea>

                            <div class="invalid-feedback" data-error-for="address"></div>
                        </div>

                        <div class="col-12">
                            <label for="partyNote" class="form-label">Note</label>

                            <textarea
                                name="note"
                                id="partyNote"
                                class="form-control"
                                rows="2"
                                maxlength="2000"
                            ></textarea>

                            <div class="invalid-feedback" data-error-for="note"></div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        id="partyQuickCreateSubmit"
                        class="btn btn-primary"
                    >
                        <span id="partyQuickCreateSpinner"
                              class="spinner-border spinner-border-sm me-1 d-none"
                              aria-hidden="true"></span>

                        <i class="bi bi-check-circle me-1" id="partyQuickCreateIcon"></i>
                        <span id="partyQuickCreateSubmitText">Save Party</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

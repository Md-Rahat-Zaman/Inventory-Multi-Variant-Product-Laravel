<div class="modal fade" id="quickCreateModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content quick-create-modal">

            <div class="modal-header">

                <div>
                    <h5 class="modal-title" id="quickCreateModalTitle">
                        Add Item
                    </h5>

                    <p class="quick-create-subtitle" id="quickCreateModalSubtitle">
                        Create a new item quickly
                    </p>
                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form id="quickCreateForm"
                  method="POST"
                  action="">

                @csrf

                <input type="hidden"
                       name="_method"
                       id="quickCreateMethod"
                       value="POST">

                <input type="hidden"
                       name="type"
                       id="quickCreateType">

                <div class="modal-body">

                    <div class="form-group">

                        <label for="quickCreateName" class="form-label">
                            Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               id="quickCreateName"
                               name="name"
                               class="form-control"
                               placeholder="Enter name"
                               required>

                        <div class="invalid-feedback"></div>

                    </div>

                    <div class="form-group mt-3"
                         id="quickCreateDescriptionGroup">

                        <label for="quickCreateDescription" class="form-label">
                            Description
                        </label>

                        <textarea id="quickCreateDescription"
                                  name="description"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Optional description"></textarea>

                    </div>

                    <div class="form-group mt-3">

                        <label for="quickCreateStatus" class="form-label">
                            Status
                        </label>

                        <select id="quickCreateStatus"
                                name="status"
                                class="form-select">

                            <option value="1">Active</option>
                            <option value="0">Inactive</option>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            id="quickCreateSaveBtn">

                        <i class="bi bi-check-lg"></i>

                        <span id="quickCreateSaveText">
                            Save Item
                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
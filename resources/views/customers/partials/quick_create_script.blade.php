
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('partyQuickCreateModal');
    const form = document.getElementById('partyQuickCreateForm');

    if (!modalElement || !form) {
        console.error('Party quick create modal or form not found.');
        return;
    }

    if (form.dataset.quickCreateInitialized === 'true') {
        return;
    }

    form.dataset.quickCreateInitialized = 'true';

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    const alertBox = document.getElementById('partyQuickCreateAlert');
    const submitButton = document.getElementById('partyQuickCreateSubmit');
    const spinner = document.getElementById('partyQuickCreateSpinner');
    const submitIcon = document.getElementById('partyQuickCreateIcon');
    const submitText = document.getElementById('partyQuickCreateSubmitText');

    const targetInput = document.getElementById('partyTargetSelect');
    const typeInput = document.getElementById('partyType');

    function showAlert(message, type = 'danger') {
        if (!alertBox) return;

        alertBox.className = 'alert alert-' + type;
        alertBox.textContent = message;
    }

    function clearAlert() {
        if (!alertBox) return;

        alertBox.className = 'alert d-none';
        alertBox.textContent = '';
    }

    function clearValidation() {
        form.querySelectorAll('.is-invalid').forEach(function (element) {
            element.classList.remove('is-invalid');
        });

        form.querySelectorAll('[data-error-for]').forEach(function (element) {
            element.textContent = '';
        });
    }

    function showValidationErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const input = form.querySelector('[name="' + field + '"]');
            const feedback = form.querySelector('[data-error-for="' + field + '"]');

            if (input) {
                input.classList.add('is-invalid');
            }

            if (feedback) {
                feedback.textContent = errors[field][0];
            }
        });
    }

    function setLoading(loading) {
        if (submitButton) {
            submitButton.disabled = loading;
        }

        if (spinner) {
            spinner.classList.toggle('d-none', !loading);
        }

        if (submitIcon) {
            submitIcon.classList.toggle('d-none', loading);
        }

        if (submitText) {
            submitText.textContent = loading ? 'Saving...' : 'Save Party';
        }
    }

    function canUseInDropdown(partyType, dropdownType) {
        if (dropdownType === 'customer') {
            return ['customer', 'both'].includes(partyType);
        }

        if (dropdownType === 'supplier') {
            return ['supplier', 'both'].includes(partyType);
        }

        return false;
    }

    function addPartyToDropdown(party) {
        const selector = targetInput ? targetInput.value : '';

        if (!selector) {
            return;
        }

        let selectElement;

        try {
            selectElement = document.querySelector(selector);
        } catch (error) {
            console.error('Invalid target dropdown selector:', selector);
            return;
        }

        if (!selectElement || selectElement.tagName !== 'SELECT') {
            console.error('Target dropdown not found:', selector);
            return;
        }

        const dropdownType = selectElement.dataset.partyType || '';

        if (!canUseInDropdown(party.type, dropdownType)) {
            showAlert(
                'Party saved, but its type does not match this dropdown.',
                'warning'
            );
            return;
        }

        let option = Array.from(selectElement.options).find(function (item) {
            return item.value === String(party.id);
        });

        if (!option) {
            option = new Option(
                party.name + (party.phone ? ' - ' + party.phone : ''),
                String(party.id)
            );

            selectElement.add(option);
        }

        selectElement.value = String(party.id);
        option.selected = true;

        selectElement.dispatchEvent(
            new Event('change', { bubbles: true })
        );
    }

    // Modal open হলে form প্রস্তুত করা
    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const requestedType = button?.dataset.partyType || 'customer';

        form.reset();
        clearAlert();
        clearValidation();

        if (targetInput) {
            targetInput.value = button?.dataset.targetSelect || '';
        }

        if (typeInput) {
            typeInput.value = ['customer', 'supplier', 'both'].includes(requestedType)
                ? requestedType
                : 'customer';
        }

        setLoading(false);
    });

    // AJAX দিয়ে Party save করা
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        clearAlert();
        clearValidation();
        setLoading(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    showValidationErrors(result.errors);
                    showAlert('Please correct the highlighted fields.');
                    return;
                }

                if (response.status === 419) {
                    showAlert('Session expired. Refresh the page and try again.');
                    return;
                }

                showAlert(result.message || 'Unable to save this party.');
                return;
            }

            if (!result.success || !result.party) {
                showAlert('Unable to save this party.');
                return;
            }

            addPartyToDropdown(result.party);

            modal.hide();

            form.reset();
            clearValidation();
            clearAlert();

        } catch (error) {
            console.error('Party save error:', error);
            showAlert('A network or server error occurred. Please try again.');

        } finally {
            setLoading(false);
        }
    });
});
</script>
@endpush

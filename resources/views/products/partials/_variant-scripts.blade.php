{{-- variant generate ar javascript --}}
@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const colorSelect = document.getElementById('variantColors');
    const sizeSelect = document.getElementById('variantSizes');

    const generateButton = document.getElementById('generateVariants');

    const variantContainer = document.getElementById('variantContainer');
    const variantTableBody = document.getElementById('variantTableBody');


    generateButton.addEventListener('click', function () {

        const colors = Array.from(colorSelect.selectedOptions).map(option => ({
            id: option.value,
            name: option.text
        }));

        const sizes = Array.from(sizeSelect.selectedOptions).map(option => ({
            id: option.value,
            name: option.text
        }));


        if (!colors.length) {
            alert('Please select at least one color.');
            return;
        }

        if (!sizes.length) {
            alert('Please select at least one size.');
            return;
        }


        variantTableBody.innerHTML = '';


        let index = 0;


        colors.forEach(function (color) {

            sizes.forEach(function (size) {

                const row = `
                    <tr>

                        <td>
                            ${color.name}

                            <input
                                type="hidden"
                                name="variants[${index}][color_id]"
                                value="${color.id}"
                            >
                        </td>


                        <td>
                            ${size.name}

                            <input
                                type="hidden"
                                name="variants[${index}][size_id]"
                                value="${size.id}"
                            >
                        </td>


                        <td>
                            <input
                                type="text"
                                name="variants[${index}][sku]"
                                class="form-control form-control-sm"
                                placeholder="SKU"
                            >
                        </td>


                        <td>
                            <input
                                type="text"
                                name="variants[${index}][barcode]"
                                class="form-control form-control-sm"
                                placeholder="Barcode"
                            >
                        </td>


                        <td>
                            <input
                                type="number"
                                name="variants[${index}][purchase_price]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="{{ old('purchase_price', 0) }}"
                            >
                        </td>


                        <td>
                            <input
                                type="number"
                                name="variants[${index}][sale_price]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="{{ old('sale_price', 0) }}"
                            >
                        </td>


                        <td>
                            <input
                                type="number"
                                name="variants[${index}][opening_stock]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="0"
                            >
                        </td>


                        <td class="text-center">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger remove-variant"
                            >
                                <i class="bi bi-trash"></i>
                            </button>

                        </td>

                    </tr>
                `;

                variantTableBody.insertAdjacentHTML(
                    'beforeend',
                    row
                );

                index++;

            });

        });


        variantContainer.classList.remove('d-none');

    });


    // Remove variant row
    variantTableBody.addEventListener('click', function (e) {

        const button = e.target.closest('.remove-variant');

        if (!button) {
            return;
        }

        button.closest('tr').remove();

    });

});

</script>

@endpush
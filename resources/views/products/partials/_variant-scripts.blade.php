
{{-- =================================================
     Product Variant Scripts
================================================== --}}

@php
    $initialVariants = old(
        'variants',
        isset($product)
            ? $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                    'sku' => $variant->sku ?? '',
                    'barcode' => $variant->barcode ?? '',
                    'purchase_price' => $variant->purchase_price ?? 0,
                    'sale_price' => $variant->sale_price ?? 0,
                    'opening_stock' => $variant->stock ?? 0,
                ];
            })->values()->toArray()
            : []
    );
@endphp

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const colorSelect = document.getElementById('variantColors');
    const sizeSelect = document.getElementById('variantSizes');
    const generateButton = document.getElementById('generateVariants');
    const variantContainer = document.getElementById('variantContainer');
    const variantTableBody = document.getElementById('variantTableBody');

    if (
        !colorSelect ||
        !sizeSelect ||
        !generateButton ||
        !variantContainer ||
        !variantTableBody
    ) {
        return;
    }

    const initialVariants = @json($initialVariants);

    const defaultPurchasePrice = @json(old(
        'purchase_price',
        $product->purchase_price ?? 0
    ));

    const defaultSalePrice = @json(old(
        'sale_price',
        $product->sale_price ?? 0
    ));

    function getSelectedOptions(selectElement) {
        return Array.from(selectElement.selectedOptions).map(option => ({
            id: option.value,
            name: option.text
        }));
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function getVariantKey(colorId, sizeId) {
        return `${colorId}_${sizeId}`;
    }

    function renderVariants(colors, sizes, existingVariants = []) {

        const existingMap = new Map();

        existingVariants.forEach(function (variant) {
            existingMap.set(
                getVariantKey(variant.color_id, variant.size_id),
                variant
            );
        });

        variantTableBody.innerHTML = '';

        let index = 0;

        colors.forEach(function (color) {

            sizes.forEach(function (size) {

                const key = getVariantKey(color.id, size.id);
                const existing = existingMap.get(key) || {};

                const row = `
                    <tr>

                        <td>
                            ${escapeHtml(color.name)}

                            <input
                                type="hidden"
                                name="variants[${index}][id]"
                                value="${escapeHtml(existing.id || '')}"
                            >

                            <input
                                type="hidden"
                                name="variants[${index}][color_id]"
                                value="${escapeHtml(color.id)}"
                            >
                        </td>

                        <td>
                            ${escapeHtml(size.name)}

                            <input
                                type="hidden"
                                name="variants[${index}][size_id]"
                                value="${escapeHtml(size.id)}"
                            >
                        </td>

                        <td>
                            <input
                                type="text"
                                name="variants[${index}][sku]"
                                class="form-control form-control-sm"
                                placeholder="SKU"
                                value="${escapeHtml(existing.sku || '')}"
                            >
                        </td>

                        <td>
                            <input
                                type="text"
                                name="variants[${index}][barcode]"
                                class="form-control form-control-sm"
                                placeholder="Barcode"
                                value="${escapeHtml(existing.barcode || '')}"
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                name="variants[${index}][purchase_price]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="${escapeHtml(
                                    existing.purchase_price ?? defaultPurchasePrice
                                )}"
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                name="variants[${index}][sale_price]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="${escapeHtml(
                                    existing.sale_price ?? defaultSalePrice
                                )}"
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                name="variants[${index}][opening_stock]"
                                class="form-control form-control-sm"
                                step="0.01"
                                min="0"
                                value="${escapeHtml(
                                    existing.opening_stock ?? 0
                                )}"
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

                variantTableBody.insertAdjacentHTML('beforeend', row);

                index++;
            });
        });

        variantContainer.classList.toggle(
            'd-none',
            variantTableBody.children.length === 0
        );
    }

    // Generate variants
    generateButton.addEventListener('click', function () {

        const colors = getSelectedOptions(colorSelect);
        const sizes = getSelectedOptions(sizeSelect);

        if (!colors.length) {
            alert('Please select at least one color.');
            return;
        }

        if (!sizes.length) {
            alert('Please select at least one size.');
            return;
        }

        // Keep existing input values for matching color-size combinations.
        const existingVariants = Array.from(
            variantTableBody.querySelectorAll('tr')
        ).map(function (row) {

            function getValue(name) {
                return row.querySelector(`[name$="[${name}]"]`)?.value ?? '';
            }

            return {
                id: getValue('id'),
                color_id: getValue('color_id'),
                size_id: getValue('size_id'),
                sku: getValue('sku'),
                barcode: getValue('barcode'),
                purchase_price: getValue('purchase_price'),
                sale_price: getValue('sale_price'),
                opening_stock: getValue('opening_stock')
            };
        });

        renderVariants(colors, sizes, existingVariants);
    });

    // Remove variant row
    variantTableBody.addEventListener('click', function (event) {

        const button = event.target.closest('.remove-variant');

        if (!button) {
            return;
        }

        button.closest('tr').remove();

        if (!variantTableBody.children.length) {
            variantContainer.classList.add('d-none');
        }
    });

    // Automatically load existing variants in Edit mode.
    if (initialVariants.length > 0) {

        const existingColorIds = [
            ...new Set(initialVariants.map(v => String(v.color_id)))
        ];

        const existingSizeIds = [
            ...new Set(initialVariants.map(v => String(v.size_id)))
        ];

        const colors = Array.from(colorSelect.options)
            .filter(option => existingColorIds.includes(option.value))
            .map(option => ({
                id: option.value,
                name: option.text
            }));

        const sizes = Array.from(sizeSelect.options)
            .filter(option => existingSizeIds.includes(option.value))
            .map(option => ({
                id: option.value,
                name: option.text
            }));

        renderVariants(colors, sizes, initialVariants);
    }

});
</script>

@endpush

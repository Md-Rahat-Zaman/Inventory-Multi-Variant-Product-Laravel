<form method="GET" action="{{ url()->current() }}" class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label for="list-search" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input id="list-search" name="search" type="search" class="form-control"
                           value="{{ $filters['search'] }}" placeholder="{{ $searchPlaceholder }}">
                </div>
            </div>
            <div class="col-md-3">
                <label for="list-filter-match" class="form-label">Advanced filter logic</label>
                <select id="list-filter-match" name="match" class="form-select">
                    <option value="all" @selected($filters['match'] === 'all')>Match all (AND)</option>
                    <option value="any" @selected($filters['match'] === 'any')>Match any (OR)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </div>

        <div class="border-top mt-3 pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <h6 class="mb-1">Advanced filters</h6>
                    <p class="text-muted small mb-0">Combine conditions with AND or OR.</p>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-list-filter-add>
                    <i class="bi bi-plus-lg me-1"></i>Add condition
                </button>
            </div>

            <div class="d-grid gap-2" data-list-filter-rows>
                @php
                    $conditions = $filters['conditions'] ?: [['field' => '', 'operator' => 'eq', 'value' => '']];
                @endphp
                @foreach ($conditions as $index => $condition)
                    @php
                        $field = $filterFields[$condition['field'] ?? ''] ?? null;
                        $type = $field['type'] ?? 'text';
                        $operator = $condition['operator'] ?? 'eq';
                        $value = $condition['value'] ?? '';
                    @endphp
                    <div class="row g-2 align-items-end border rounded-3 p-3 mx-0" data-list-filter-row data-index="{{ $index }}">
                        <div class="col-md-4">
                            <label class="form-label small">Field</label>
                            <select name="conditions[{{ $index }}][field]" class="form-select" data-list-filter-field>
                                <option value="">Select a field</option>
                                @foreach ($filterFields as $key => $availableField)
                                    <option value="{{ $key }}" data-type="{{ $availableField['type'] }}"
                                            data-options="{{ json_encode($availableField['options'] ?? []) }}"
                                        @selected(($condition['field'] ?? '') === $key)>{{ $availableField['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Condition</label>
                            <select name="conditions[{{ $index }}][operator]" class="form-select" data-list-filter-operator>
                                @foreach ([
                                    'eq' => 'Equals',
                                    'neq' => 'Does not equal',
                                    'contains' => 'Contains',
                                    'starts_with' => 'Starts with',
                                    'ends_with' => 'Ends with',
                                    'gt' => 'Greater than',
                                    'gte' => 'Greater than or equal',
                                    'lt' => 'Less than',
                                    'lte' => 'Less than or equal',
                                    'is_empty' => 'Is empty',
                                    'is_not_empty' => 'Is not empty',
                                ] as $key => $label)
                                    <option value="{{ $key }}" @selected($operator === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Value</label>
                            <input type="{{ $type === 'date' ? 'date' : ($type === 'number' ? 'number' : 'search') }}"
                                   name="conditions[{{ $index }}][value]" value="{{ $value }}"
                                   class="form-control" data-list-filter-value
                                   @if ($type === 'number') step="any" @endif
                                   @disabled($type === 'select' || in_array($operator, ['is_empty', 'is_not_empty'], true))
                                   @if ($type === 'select' || in_array($operator, ['is_empty', 'is_not_empty'], true)) hidden @endif>
                            <select name="conditions[{{ $index }}][value]" class="form-select" data-list-filter-select
                                    @disabled($type !== 'select' || in_array($operator, ['is_empty', 'is_not_empty'], true))
                                    @if ($type !== 'select' || in_array($operator, ['is_empty', 'is_not_empty'], true)) hidden @endif>
                                <option value="">Select a value</option>
                                @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="button" class="btn btn-outline-danger" data-list-filter-remove aria-label="Remove condition">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <template data-list-filter-template>
                <div class="row g-2 align-items-end border rounded-3 p-3 mx-0" data-list-filter-row data-index="__INDEX__">
                    <div class="col-md-4">
                        <label class="form-label small">Field</label>
                        <select name="conditions[__INDEX__][field]" class="form-select" data-list-filter-field>
                            <option value="">Select a field</option>
                            @foreach ($filterFields as $key => $field)
                                <option value="{{ $key }}" data-type="{{ $field['type'] }}"
                                        data-options="{{ json_encode($field['options'] ?? []) }}">{{ $field['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Condition</label>
                        <select name="conditions[__INDEX__][operator]" class="form-select" data-list-filter-operator>
                            <option value="eq">Equals</option>
                            <option value="neq">Does not equal</option>
                            <option value="contains">Contains</option>
                            <option value="starts_with">Starts with</option>
                            <option value="ends_with">Ends with</option>
                            <option value="gt">Greater than</option>
                            <option value="gte">Greater than or equal</option>
                            <option value="lt">Less than</option>
                            <option value="lte">Less than or equal</option>
                            <option value="is_empty">Is empty</option>
                            <option value="is_not_empty">Is not empty</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Value</label>
                        <input type="search" name="conditions[__INDEX__][value]" class="form-control" data-list-filter-value>
                        <select name="conditions[__INDEX__][value]" class="form-select" data-list-filter-select hidden disabled>
                            <option value="">Select a value</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger" data-list-filter-remove aria-label="Remove condition">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</form>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('form').forEach(function (form) {
                    const rows = form.querySelector('[data-list-filter-rows]');
                    const template = form.querySelector('[data-list-filter-template]');
                    const addButton = form.querySelector('[data-list-filter-add]');
                    if (!rows || !template || !addButton) return;

                    let nextIndex = Math.max(-1, ...Array.from(rows.children, row => Number(row.dataset.index))) + 1;
                    const operatorLabels = {
                        eq: 'Equals', neq: 'Does not equal', contains: 'Contains',
                        starts_with: 'Starts with', ends_with: 'Ends with',
                        gt: 'Greater than', gte: 'Greater than or equal',
                        lt: 'Less than', lte: 'Less than or equal',
                        is_empty: 'Is empty', is_not_empty: 'Is not empty'
                    };
                    const operatorsByType = {
                        text: ['eq', 'neq', 'contains', 'starts_with', 'ends_with', 'is_empty', 'is_not_empty'],
                        select: ['eq', 'neq'],
                        number: ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'],
                        date: ['eq', 'neq', 'gt', 'gte', 'lt', 'lte']
                    };

                    function updateRow(row) {
                        const field = row.querySelector('[data-list-filter-field]');
                        const operator = row.querySelector('[data-list-filter-operator]');
                        const input = row.querySelector('[data-list-filter-value]');
                        const select = row.querySelector('[data-list-filter-select]');
                        const option = field.selectedOptions[0];
                        const type = option?.dataset.type || 'text';
                        const allowedOperators = operatorsByType[type] || operatorsByType.text;
                        const previousOperator = operator.value;

                        operator.replaceChildren(...allowedOperators.map(value => {
                            const choice = document.createElement('option');
                            choice.value = value;
                            choice.textContent = operatorLabels[value];
                            return choice;
                        }));
                        operator.value = allowedOperators.includes(previousOperator)
                            ? previousOperator
                            : (type === 'text' ? 'contains' : 'eq');

                        const options = JSON.parse(option?.dataset.options || '{}');
                        const previousValue = type === 'select' ? select.value : input.value;
                        select.replaceChildren(new Option('Select a value', ''));
                        Object.entries(options).forEach(([value, label]) => select.add(new Option(label, value)));

                        const requiresValue = !['is_empty', 'is_not_empty'].includes(operator.value);
                        input.type = type === 'number' ? 'number' : (type === 'date' ? 'date' : 'search');
                        if (type === 'number') input.step = 'any';
                        else input.removeAttribute('step');
                        input.hidden = type === 'select' || !requiresValue;
                        input.disabled = type === 'select' || !requiresValue;
                        select.hidden = type !== 'select' || !requiresValue;
                        select.disabled = type !== 'select' || !requiresValue;
                        if (type === 'select' && requiresValue) select.value = previousValue;
                        if (!requiresValue) {
                            input.value = '';
                            select.value = '';
                        }
                    }

                    rows.addEventListener('change', function (event) {
                        if (event.target.matches('[data-list-filter-field], [data-list-filter-operator]')) {
                            updateRow(event.target.closest('[data-list-filter-row]'));
                        }
                    });
                    rows.addEventListener('click', function (event) {
                        const button = event.target.closest('[data-list-filter-remove]');
                        if (button) button.closest('[data-list-filter-row]').remove();
                    });
                    addButton.addEventListener('click', function () {
                        const row = template.content.firstElementChild.cloneNode(true);
                        row.innerHTML = row.innerHTML.replace(/__INDEX__/g, String(nextIndex));
                        row.dataset.index = String(nextIndex++);
                        rows.appendChild(row);
                        updateRow(row);
                    });
                    rows.querySelectorAll('[data-list-filter-row]').forEach(updateRow);
                });
            });
        </script>
    @endpush
@endonce

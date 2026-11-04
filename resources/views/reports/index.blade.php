
@extends('layouts.app_bootstrap')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="mb-1">{{ $title }}</h4>
            <div class="text-muted small">Inventory Management Reports</div>
        </div>

        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('reports.products') }}"
                   class="btn btn-sm {{ request()->routeIs('reports.products') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-box-seam me-1"></i> Products
                </a>

                <a href="{{ route('reports.sales') }}"
                   class="btn btn-sm {{ request()->routeIs('reports.sales') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-receipt me-1"></i> Sales
                </a>

                <a href="{{ route('reports.purchases') }}"
                   class="btn btn-sm {{ request()->routeIs('reports.purchases') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-cart-check me-1"></i> Purchases
                </a>

                <a href="{{ route('reports.stock') }}"
                   class="btn btn-sm {{ request()->routeIs('reports.stock') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-boxes me-1"></i> Stock
                </a>

                <a href="{{ route('reports.profit_loss') }}"
                   class="btn btn-sm {{ request()->routeIs('reports.profit_loss') ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="bi bi-graph-up-arrow me-1"></i> Profit & Loss
                </a>
            </div>

            <form method="GET" action="{{ url()->current() }}" id="report-filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input
                            type="search"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ $filters['search'] }}"
                            placeholder="Search by name, invoice, SKU..."
                        >
                    </div>

                    @if (!request()->routeIs('reports.products') && !request()->routeIs('reports.stock'))
                        <div class="col-md-3">
                            <label for="from" class="form-label">From Date</label>
                            <input type="date" name="from" id="from" class="form-control" value="{{ $filters['from'] }}">
                        </div>

                        <div class="col-md-3">
                            <label for="to" class="form-label">To Date</label>
                            <input type="date" name="to" id="to" class="form-control" value="{{ $filters['to'] }}">
                        </div>
                    @endif

                    <div class="col-md-2">
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </a>
                    </div>
                </div>

                <div class="border-top mt-4 pt-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="mb-1"><i class="bi bi-funnel me-1"></i>Advanced filters</h6>
                            <p class="text-muted small mb-0">Add conditions and choose how they should match.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="filter-match" class="form-label mb-0 small">Match</label>
                            <select id="filter-match" name="match" class="form-select form-select-sm w-auto">
                                <option value="all" @selected($filters['match'] === 'all')>All conditions (AND)</option>
                                <option value="any" @selected($filters['match'] === 'any')>Any condition (OR)</option>
                            </select>
                        </div>
                    </div>

                    <div id="report-filter-rows" class="d-grid gap-2">
                        @php
                            $conditions = $filters['conditions'] ?: [['field' => '', 'operator' => 'contains', 'value' => '']];
                        @endphp
                        @foreach ($conditions as $index => $condition)
                            @php
                                $fieldType = $filterFields[$condition['field'] ?? '']['type'] ?? 'text';
                                $operator = $condition['operator'] ?? 'contains';
                                $inputType = $fieldType === 'number' ? 'number' : ($fieldType === 'date' ? 'date' : 'search');
                            @endphp
                            <div class="row g-2 align-items-end border rounded-3 p-3 mx-0 report-filter-row" data-index="{{ $index }}">
                                <div class="col-md-4">
                                    <label class="form-label small">Field</label>
                                    <select name="conditions[{{ $index }}][field]" class="form-select report-filter-field">
                                        <option value="">Select a field</option>
                                        @foreach ($filterFields as $key => $field)
                                            <option value="{{ $key }}" data-type="{{ $field['type'] }}"
                                                @selected(($condition['field'] ?? '') === $key)>{{ $field['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Condition</label>
                                    <select name="conditions[{{ $index }}][operator]" class="form-select report-filter-operator">
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
                                        ] as $operatorKey => $operatorLabel)
                                            <option value="{{ $operatorKey }}" @selected($operator === $operatorKey)>{{ $operatorLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Value</label>
                                    <input name="conditions[{{ $index }}][value]" type="{{ $inputType }}"
                                           @if ($inputType === 'number') step="any" @endif
                                           class="form-control report-filter-value"
                                           value="{{ $condition['value'] ?? '' }}"
                                           @disabled(in_array($operator, ['is_empty', 'is_not_empty'], true))>
                                </div>
                                <div class="col-md-1 d-grid">
                                    <button type="button" class="btn btn-outline-danger report-filter-remove" aria-label="Remove condition">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template id="report-filter-template">
                        <div class="row g-2 align-items-end border rounded-3 p-3 mx-0 report-filter-row" data-index="__INDEX__">
                            <div class="col-md-4">
                                <label class="form-label small">Field</label>
                                <select name="conditions[__INDEX__][field]" class="form-select report-filter-field">
                                    <option value="">Select a field</option>
                                    @foreach ($filterFields as $key => $field)
                                        <option value="{{ $key }}" data-type="{{ $field['type'] }}">{{ $field['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Condition</label>
                                <select name="conditions[__INDEX__][operator]" class="form-select report-filter-operator">
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
                                <input name="conditions[__INDEX__][value]" type="search" class="form-control report-filter-value">
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="button" class="btn btn-outline-danger report-filter-remove" aria-label="Remove condition">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="d-flex flex-wrap justify-content-between gap-2 mt-3">
                        <button type="button" id="report-filter-add" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg me-1"></i>Add condition
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>Apply filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if (count($summary))
        <div class="row g-3 mb-4">
            @foreach ($summary as $item)
                <div class="col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small mb-2">
                                {{ $item['label'] }}
                            </div>
                            <h5 class="mb-0">
                                ৳{{ number_format((float) $item['value'], 2) }}
                            </h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if (request()->routeIs('reports.profit_loss'))
        <div class="alert alert-warning small">
            Profit & Loss-এর cost বর্তমান product/variant purchase price থেকে estimate করা হচ্ছে।
            এটি বিক্রির দিনের historical cost নয়। Returns ও operating expenses এই gross-profit
            calculation-এ অন্তর্ভুক্ত নয়।
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 55px;">SL</th>
                            @foreach ($columns as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>
                                    {{ $rows->firstItem() + $loop->index }}
                                </td>

                                @foreach ($columns as $column)
                                    @php
                                        $value = data_get($row, $column['key']);
                                    @endphp

                                    <td>
                                        @if (!is_null($value) && isset($column['money']) && $column['money'])
                                            ৳{{ number_format((float) $value, 2) }}
                                        @elseif (is_null($value) || $value === '')
                                            <span class="text-muted">—</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}"
                                    class="text-center text-muted py-4">
                                    No records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <div class="text-muted small">
                    Showing {{ $rows->firstItem() ?? 0 }}
                    to {{ $rows->lastItem() ?? 0 }}
                    of {{ $rows->total() }} records
                </div>

                {{ $rows->links() }}
            </div>
        </div>
    </div>

</div>

<style>
    @media print {
        nav,
        aside,
        .sidebar,
        .navbar,
        form,
        .btn,
        .pagination {
            display: none !important;
        }

        .container-fluid {
            width: 100% !important;
            padding: 0 !important;
        }

        .card {
            border: 0 !important;
            box-shadow: none !important;
        }

        body {
            font-size: 11px;
        }

        table {
            width: 100%;
        }
    }
</style>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.getElementById('report-filter-rows');
            const template = document.getElementById('report-filter-template');
            const addButton = document.getElementById('report-filter-add');
            let nextIndex = Math.max(-1, ...Array.from(rows.children, row => Number(row.dataset.index))) + 1;
            const textOperators = new Set(['eq', 'neq', 'contains', 'starts_with', 'ends_with', 'is_empty', 'is_not_empty']);
            const comparisonOperators = new Set(['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'is_empty', 'is_not_empty']);

            function updateRow(row) {
                const field = row.querySelector('.report-filter-field');
                const operator = row.querySelector('.report-filter-operator');
                const value = row.querySelector('.report-filter-value');
                const type = field.selectedOptions[0]?.dataset.type || 'text';
                const allowedOperators = type === 'text' ? textOperators : comparisonOperators;
                const currentOperator = operator.value;

                Array.from(operator.options).forEach(option => {
                    option.hidden = !allowedOperators.has(option.value);
                });
                if (!allowedOperators.has(currentOperator)) {
                    operator.value = type === 'text' ? 'contains' : 'eq';
                }

                value.type = type === 'date' ? 'date' : (type === 'number' ? 'number' : 'search');
                if (type === 'number') {
                    value.step = 'any';
                } else {
                    value.removeAttribute('step');
                }
                value.disabled = ['is_empty', 'is_not_empty'].includes(operator.value);
                if (value.disabled) {
                    value.value = '';
                }
            }

            rows.addEventListener('change', function (event) {
                if (event.target.matches('.report-filter-field, .report-filter-operator')) {
                    updateRow(event.target.closest('.report-filter-row'));
                }
            });

            rows.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.report-filter-remove');
                if (removeButton) {
                    removeButton.closest('.report-filter-row').remove();
                }
            });

            addButton.addEventListener('click', function () {
                const row = template.content.firstElementChild.cloneNode(true);
                row.dataset.index = nextIndex;
                row.innerHTML = row.innerHTML.replaceAll('__INDEX__', nextIndex);
                nextIndex += 1;
                rows.appendChild(row);
                updateRow(row);
            });

            rows.querySelectorAll('.report-filter-row').forEach(updateRow);
        });
    </script>
@endpush
@endsection

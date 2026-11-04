<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportsController extends Controller
{
    private function filterFields(string $report): array
    {
        $fields = [
            'products' => [
                'product_name' => ['label' => 'Product', 'column' => 'p.name', 'type' => 'text'],
                'product_sku' => ['label' => 'Product SKU', 'column' => 'p.sku', 'type' => 'text'],
                'variant_sku' => ['label' => 'Variant SKU', 'column' => 'pv.sku', 'type' => 'text'],
                'category_name' => ['label' => 'Category', 'column' => 'c.name', 'type' => 'text'],
                'brand_name' => ['label' => 'Brand', 'column' => 'b.name', 'type' => 'text'],
                'color_name' => ['label' => 'Color', 'column' => 'cl.name', 'type' => 'text'],
                'size_name' => ['label' => 'Size', 'column' => 'sz.name', 'type' => 'text'],
                'purchase_price' => ['label' => 'Purchase Price', 'column' => 'COALESCE(pv.purchase_price, p.purchase_price, 0)', 'type' => 'number'],
                'sale_price' => ['label' => 'Sale Price', 'column' => 'COALESCE(pv.sale_price, p.sale_price, 0)', 'type' => 'number'],
            ],
            'sales' => [
                'sale_no' => ['label' => 'Invoice', 'column' => 's.sale_no', 'type' => 'text'],
                'sale_date' => ['label' => 'Date', 'column' => 's.sale_date', 'type' => 'date'],
                'customer_name' => ['label' => 'Customer', 'column' => 's.customer_name', 'type' => 'text'],
                'customer_phone' => ['label' => 'Phone', 'column' => 's.customer_phone', 'type' => 'text'],
                'grand_total' => ['label' => 'Total', 'column' => 's.grand_total', 'type' => 'number'],
                'paid_amount' => ['label' => 'Paid', 'column' => 's.paid_amount', 'type' => 'number'],
                'due_amount' => ['label' => 'Due', 'column' => 's.due_amount', 'type' => 'number'],
                'payment_status' => ['label' => 'Payment Status', 'column' => 's.payment_status', 'type' => 'text'],
            ],
            'purchases' => [
                'purchase_no' => ['label' => 'Purchase No', 'column' => 'p.purchase_no', 'type' => 'text'],
                'purchase_date' => ['label' => 'Date', 'column' => 'p.purchase_date', 'type' => 'date'],
                'supplier_name' => ['label' => 'Supplier', 'column' => 'c.name', 'type' => 'text'],
                'grand_total' => ['label' => 'Total', 'column' => 'p.grand_total', 'type' => 'number'],
                'paid_amount' => ['label' => 'Paid', 'column' => 'p.paid_amount', 'type' => 'number'],
                'due_amount' => ['label' => 'Due', 'column' => 'p.due_amount', 'type' => 'number'],
                'payment_status' => ['label' => 'Payment Status', 'column' => 'p.payment_status', 'type' => 'text'],
            ],
            'stock' => [
                'product_name' => ['label' => 'Product', 'column' => 'p.name', 'type' => 'text'],
                'product_sku' => ['label' => 'Product SKU', 'column' => 'p.sku', 'type' => 'text'],
                'variant_sku' => ['label' => 'Variant SKU', 'column' => 'pv.sku', 'type' => 'text'],
                'color_name' => ['label' => 'Color', 'column' => 'cl.name', 'type' => 'text'],
                'size_name' => ['label' => 'Size', 'column' => 'sz.name', 'type' => 'text'],
                'current_stock' => ['label' => 'Current Stock', 'column' => 'COALESCE(pv.stock, p.stock, 0)', 'type' => 'number'],
                'minimum_stock' => ['label' => 'Minimum Stock', 'column' => 'COALESCE(pv.minimum_stock, p.minimum_stock, 0)', 'type' => 'number'],
                'purchase_price' => ['label' => 'Purchase Price', 'column' => 'COALESCE(pv.purchase_price, p.purchase_price, 0)', 'type' => 'number'],
                'sale_price' => ['label' => 'Sale Price', 'column' => 'COALESCE(pv.sale_price, p.sale_price, 0)', 'type' => 'number'],
                'stock_value' => ['label' => 'Stock Value', 'column' => '(COALESCE(pv.stock, p.stock, 0) * COALESCE(pv.purchase_price, p.purchase_price, 0))', 'type' => 'number'],
            ],
            'profit_loss' => [
                'sale_no' => ['label' => 'Invoice', 'column' => 's.sale_no', 'type' => 'text'],
                'sale_date' => ['label' => 'Date', 'column' => 's.sale_date', 'type' => 'date'],
                'customer_name' => ['label' => 'Customer', 'column' => 's.customer_name', 'type' => 'text'],
                'product_name' => ['label' => 'Product', 'column' => 'p.name', 'type' => 'text'],
                'quantity' => ['label' => 'Quantity', 'column' => 'si.quantity', 'type' => 'number'],
                'unit_price' => ['label' => 'Sale Price', 'column' => 'si.unit_price', 'type' => 'number'],
                'total' => ['label' => 'Revenue', 'column' => 'si.total', 'type' => 'number'],
                'current_cost_price' => ['label' => 'Current Cost', 'column' => 'COALESCE(pv.purchase_price, p.purchase_price, 0)', 'type' => 'number'],
                'estimated_cost' => ['label' => 'Estimated Cost', 'column' => '(si.quantity * COALESCE(pv.purchase_price, p.purchase_price, 0))', 'type' => 'number'],
                'estimated_profit' => ['label' => 'Estimated Profit', 'column' => '(si.total - (si.quantity * COALESCE(pv.purchase_price, p.purchase_price, 0)))', 'type' => 'number'],
            ],
        ];

        return $fields[$report];
    }

    private function filters(Request $request, array $filterFields, bool $hasDateRange = false): array
    {
        $conditions = $request->query('conditions', []);
        if (is_array($conditions)) {
            $conditions = array_values(array_filter($conditions, static function ($condition) {
                return ! is_array($condition)
                    || ! empty($condition['field'])
                    || ! empty($condition['value']);
            }));
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'from' => $hasDateRange ? ['nullable', 'date'] : ['nullable'],
            'to' => $hasDateRange ? ['nullable', 'date', 'after_or_equal:from'] : ['nullable'],
            'match' => ['nullable', Rule::in(['all', 'any'])],
            'conditions' => ['nullable', 'array', 'max:10'],
        ]);
        if (! is_array($conditions)) {
            throw ValidationException::withMessages([
                'conditions' => 'The filter conditions must be a list.',
            ]);
        }

        $rules = [];
        foreach ($conditions as $index => $condition) {
            $rules["conditions.{$index}.field"] = ['required', 'string', Rule::in(array_keys($filterFields))];
            $rules["conditions.{$index}.operator"] = ['required', 'string', Rule::in([
                'eq', 'neq', 'contains', 'starts_with', 'ends_with', 'gt', 'gte', 'lt', 'lte', 'is_empty', 'is_not_empty',
            ])];
            $rules["conditions.{$index}.value"] = ['nullable', 'string', 'max:100'];

            if (! is_array($condition)) {
                continue;
            }

            $field = $filterFields[$condition['field'] ?? ''] ?? null;
            $operator = $condition['operator'] ?? '';
            if ($field && ! in_array($operator, ['is_empty', 'is_not_empty'], true)) {
                $rules["conditions.{$index}.value"][] = 'required';
                $rules["conditions.{$index}.value"][] = match ($field['type']) {
                    'number' => 'numeric',
                    'date' => 'date',
                    default => 'string',
                };
            }
            if ($field && in_array($field['type'], ['number', 'date'], true)) {
                $rules["conditions.{$index}.operator"][] = Rule::in([
                    'eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'is_empty', 'is_not_empty',
                ]);
            }
        }

        $validatedConditions = Validator::make(
            ['conditions' => $conditions],
            $rules
        )->validate()['conditions'] ?? [];

        if ($hasDateRange && ! empty($validated['from']) && ! empty($validated['to'])
            && $validated['to'] < $validated['from']) {
            throw ValidationException::withMessages([
                'to' => 'The to date must be on or after the from date.',
            ]);
        }

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'from' => $validated['from'] ?? '',
            'to' => $validated['to'] ?? '',
            'match' => $validated['match'] ?? 'all',
            'conditions' => $validatedConditions,
        ];
    }

    private function applyAdvancedFilters($query, array $filters, array $filterFields)
    {
        if (empty($filters['conditions'])) {
            return $query;
        }

        return $query->where(function ($group) use ($filters, $filterFields) {
            foreach ($filters['conditions'] as $index => $condition) {
                $field = $filterFields[$condition['field']];
                $column = DB::raw($field['column']);
                $operator = $condition['operator'];
                $value = $condition['value'] ?? null;
                $method = $filters['match'] === 'any' && $index > 0 ? 'orWhere' : 'where';

                if ($operator === 'is_empty') {
                    $group->{$method}(function ($empty) use ($column) {
                        $empty->whereNull($column)->orWhere($column, '=', '');
                    });
                } elseif ($operator === 'is_not_empty') {
                    $group->{$method}(function ($notEmpty) use ($column) {
                        $notEmpty->whereNotNull($column)->where($column, '<>', '');
                    });
                } else {
                    [$sqlOperator, $sqlValue] = match ($operator) {
                        'eq' => ['=', $value],
                        'neq' => ['<>', $value],
                        'contains' => ['like', '%'.$value.'%'],
                        'starts_with' => ['like', $value.'%'],
                        'ends_with' => ['like', '%'.$value],
                        'gt' => ['>', $value],
                        'gte' => ['>=', $value],
                        'lt' => ['<', $value],
                        'lte' => ['<=', $value],
                    };
                    $group->{$method}($column, $sqlOperator, $sqlValue);
                }
            }
        });
    }

    private function applyDateFilter($query, string $column, array $filters)
    {
        if (!empty($filters['from'])) {
            $query->whereDate($column, '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate($column, '<=', $filters['to']);
        }

        return $query;
    }

    private function render(
        string $title,
        array $columns,
        $rows,
        array $filters,
        array $summary = [],
        array $filterFields = []
    ) {
        return view('reports.index', compact(
            'title',
            'columns',
            'rows',
            'filters',
            'summary',
            'filterFields'
        ));
    }

    public function products(Request $request)
    {
        $filterFields = $this->filterFields('products');
        $filters = $this->filters($request, $filterFields);

        $query = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
            ->leftJoin('units as u', 'u.id', '=', 'p.unit_id')
            ->leftJoin('product_variants as pv', 'pv.product_id', '=', 'p.id')
            ->leftJoin('colors as cl', 'cl.id', '=', 'pv.color_id')
            ->leftJoin('sizes as sz', 'sz.id', '=', 'pv.size_id')
            ->select([
                'p.id',
                'p.name as product_name',
                'p.sku as product_sku',
                'c.name as category_name',
                'b.name as brand_name',
                'u.name as unit_name',
                'pv.sku as variant_sku',
                'cl.name as color_name',
                'sz.name as size_name',
                DB::raw('COALESCE(pv.purchase_price, p.purchase_price, 0) as purchase_price'),
                DB::raw('COALESCE(pv.sale_price, p.sale_price, 0) as sale_price'),
                DB::raw('COALESCE(pv.stock, p.stock, 0) as current_stock'),
            ]);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                    ->orWhere('p.sku', 'like', "%{$search}%")
                    ->orWhere('pv.sku', 'like', "%{$search}%")
                    ->orWhere('c.name', 'like', "%{$search}%")
                    ->orWhere('b.name', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($query, $filters, $filterFields);

        $rows = $query->orderBy('p.name')
            ->orderBy('pv.id')
            ->paginate(15)
            ->withQueryString();

        return $this->render('Product Report', [
            ['key' => 'product_name', 'label' => 'Product'],
            ['key' => 'product_sku', 'label' => 'Product SKU'],
            ['key' => 'variant_sku', 'label' => 'Variant SKU'],
            ['key' => 'category_name', 'label' => 'Category'],
            ['key' => 'brand_name', 'label' => 'Brand'],
            ['key' => 'color_name', 'label' => 'Color'],
            ['key' => 'size_name', 'label' => 'Size'],
            ['key' => 'purchase_price', 'label' => 'Purchase Price', 'money' => true],
            ['key' => 'sale_price', 'label' => 'Sale Price', 'money' => true],
        ], $rows, $filters, [], $filterFields);
    }

    public function sales(Request $request)
    {
        $filterFields = $this->filterFields('sales');
        $filters = $this->filters($request, $filterFields, true);

        $query = DB::table('sales as s')
            ->select([
                's.sale_no',
                's.sale_date',
                's.customer_name',
                's.customer_phone',
                's.grand_total',
                's.paid_amount',
                's.due_amount',
                's.payment_status',
                's.status',
            ]);

        $this->applyDateFilter($query, 's.sale_date', $filters);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('s.sale_no', 'like', "%{$search}%")
                    ->orWhere('s.customer_name', 'like', "%{$search}%")
                    ->orWhere('s.customer_phone', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($query, $filters, $filterFields);

        $rows = $query->orderByDesc('s.sale_date')
            ->orderByDesc('s.sale_no')
            ->paginate(15)
            ->withQueryString();

        $totals = DB::table('sales as s')
            ->selectRaw('
                COALESCE(SUM(s.grand_total), 0) as total_amount,
                COALESCE(SUM(s.paid_amount), 0) as total_paid,
                COALESCE(SUM(s.due_amount), 0) as total_due
            ');

        $this->applyDateFilter($totals, 's.sale_date', $filters);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $totals->where(function ($q) use ($search) {
                $q->where('s.sale_no', 'like', "%{$search}%")
                    ->orWhere('s.customer_name', 'like', "%{$search}%")
                    ->orWhere('s.customer_phone', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($totals, $filters, $filterFields);

        $totals = $totals->first();

        return $this->render('Sales Report', [
            ['key' => 'sale_no', 'label' => 'Invoice'],
            ['key' => 'sale_date', 'label' => 'Date'],
            ['key' => 'customer_name', 'label' => 'Customer'],
            ['key' => 'customer_phone', 'label' => 'Phone'],
            ['key' => 'grand_total', 'label' => 'Total', 'money' => true],
            ['key' => 'paid_amount', 'label' => 'Paid', 'money' => true],
            ['key' => 'due_amount', 'label' => 'Due', 'money' => true],
            ['key' => 'payment_status', 'label' => 'Payment Status'],
        ], $rows, $filters, [
            ['label' => 'Total Sales', 'value' => $totals->total_amount],
            ['label' => 'Total Paid', 'value' => $totals->total_paid],
            ['label' => 'Total Due', 'value' => $totals->total_due],
        ], $filterFields);
    }

    public function purchases(Request $request)
    {
        $filterFields = $this->filterFields('purchases');
        $filters = $this->filters($request, $filterFields, true);

        $query = DB::table('purchases as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.supplier_id')
            ->select([
                'p.purchase_no',
                'p.purchase_date',
                'c.name as supplier_name',
                'p.grand_total',
                'p.paid_amount',
                'p.due_amount',
                'p.payment_status',
            ]);

        $this->applyDateFilter($query, 'p.purchase_date', $filters);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('p.purchase_no', 'like', "%{$search}%")
                    ->orWhere('c.name', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($query, $filters, $filterFields);

        $rows = $query->orderByDesc('p.purchase_date')
            ->orderByDesc('p.purchase_no')
            ->paginate(15)
            ->withQueryString();

        $totals = DB::table('purchases as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.supplier_id')
            ->selectRaw('
                COALESCE(SUM(p.grand_total), 0) as total_amount,
                COALESCE(SUM(p.paid_amount), 0) as total_paid,
                COALESCE(SUM(p.due_amount), 0) as total_due
            ');

        $this->applyDateFilter($totals, 'p.purchase_date', $filters);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $totals->where(function ($q) use ($search) {
                $q->where('p.purchase_no', 'like', "%{$search}%")
                    ->orWhere('c.name', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($totals, $filters, $filterFields);

        $totals = $totals->first();

        return $this->render('Purchase Report', [
            ['key' => 'purchase_no', 'label' => 'Purchase No'],
            ['key' => 'purchase_date', 'label' => 'Date'],
            ['key' => 'supplier_name', 'label' => 'Supplier'],
            ['key' => 'grand_total', 'label' => 'Total', 'money' => true],
            ['key' => 'paid_amount', 'label' => 'Paid', 'money' => true],
            ['key' => 'due_amount', 'label' => 'Due', 'money' => true],
            ['key' => 'payment_status', 'label' => 'Payment Status'],
        ], $rows, $filters, [
            ['label' => 'Total Purchases', 'value' => $totals->total_amount],
            ['label' => 'Total Paid', 'value' => $totals->total_paid],
            ['label' => 'Total Due', 'value' => $totals->total_due],
        ], $filterFields);
    }

    public function stock(Request $request)
    {
        $filterFields = $this->filterFields('stock');
        $filters = $this->filters($request, $filterFields);

        $query = DB::table('products as p')
            ->leftJoin('product_variants as pv', 'pv.product_id', '=', 'p.id')
            ->leftJoin('colors as cl', 'cl.id', '=', 'pv.color_id')
            ->leftJoin('sizes as sz', 'sz.id', '=', 'pv.size_id')
            ->select([
                'p.name as product_name',
                'p.sku as product_sku',
                'pv.sku as variant_sku',
                'cl.name as color_name',
                'sz.name as size_name',
                DB::raw('COALESCE(pv.stock, p.stock, 0) as current_stock'),
                DB::raw('COALESCE(pv.minimum_stock, p.minimum_stock, 0) as minimum_stock'),
                DB::raw('COALESCE(pv.purchase_price, p.purchase_price, 0) as purchase_price'),
                DB::raw('COALESCE(pv.sale_price, p.sale_price, 0) as sale_price'),
                DB::raw('(COALESCE(pv.stock, p.stock, 0) * COALESCE(pv.purchase_price, p.purchase_price, 0)) as stock_value'),
            ]);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                    ->orWhere('p.sku', 'like', "%{$search}%")
                    ->orWhere('pv.sku', 'like', "%{$search}%")
                    ->orWhere('cl.name', 'like', "%{$search}%")
                    ->orWhere('sz.name', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($query, $filters, $filterFields);

        $rows = $query->orderBy('p.name')
            ->orderBy('pv.id')
            ->paginate(15)
            ->withQueryString();

        return $this->render('Stock Report', [
            ['key' => 'product_name', 'label' => 'Product'],
            ['key' => 'product_sku', 'label' => 'Product SKU'],
            ['key' => 'variant_sku', 'label' => 'Variant SKU'],
            ['key' => 'color_name', 'label' => 'Color'],
            ['key' => 'size_name', 'label' => 'Size'],
            ['key' => 'current_stock', 'label' => 'Current Stock'],
            ['key' => 'minimum_stock', 'label' => 'Minimum Stock'],
            ['key' => 'purchase_price', 'label' => 'Purchase Price', 'money' => true],
            ['key' => 'sale_price', 'label' => 'Sale Price', 'money' => true],
            ['key' => 'stock_value', 'label' => 'Stock Value', 'money' => true],
        ], $rows, $filters, [], $filterFields);
    }

    public function profitLoss(Request $request)
    {
        $filterFields = $this->filterFields('profit_loss');
        $filters = $this->filters($request, $filterFields, true);

        $query = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'si.product_variant_id')
            ->select([
                's.sale_no',
                's.sale_date',
                's.customer_name',
                'p.name as product_name',
                'si.quantity',
                'si.unit_price',
                'si.discount',
                'si.total',
                DB::raw('COALESCE(pv.purchase_price, p.purchase_price, 0) as current_cost_price'),
                DB::raw('(si.quantity * COALESCE(pv.purchase_price, p.purchase_price, 0)) as estimated_cost'),
                DB::raw('(si.total - (si.quantity * COALESCE(pv.purchase_price, p.purchase_price, 0))) as estimated_profit'),
            ]);

        $this->applyDateFilter($query, 's.sale_date', $filters);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('s.sale_no', 'like', "%{$search}%")
                    ->orWhere('s.customer_name', 'like', "%{$search}%")
                    ->orWhere('p.name', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($query, $filters, $filterFields);

        $rows = $query->orderByDesc('s.sale_date')
            ->orderByDesc('s.id')
            ->paginate(15)
            ->withQueryString();

        $totals = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'si.product_variant_id')
            ->selectRaw('
                COALESCE(SUM(si.total), 0) as revenue,
                COALESCE(SUM(si.quantity * COALESCE(pv.purchase_price, p.purchase_price, 0)), 0) as estimated_cost
            ');

        $this->applyDateFilter($totals, 's.sale_date', $filters);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $totals->where(function ($q) use ($search) {
                $q->where('s.sale_no', 'like', "%{$search}%")
                    ->orWhere('s.customer_name', 'like', "%{$search}%")
                    ->orWhere('p.name', 'like', "%{$search}%");
            });
        }
        $this->applyAdvancedFilters($totals, $filters, $filterFields);

        $totals = $totals->first();
        $profit = (float) $totals->revenue - (float) $totals->estimated_cost;

        return $this->render('Profit & Loss Report', [
            ['key' => 'sale_no', 'label' => 'Invoice'],
            ['key' => 'sale_date', 'label' => 'Date'],
            ['key' => 'customer_name', 'label' => 'Customer'],
            ['key' => 'product_name', 'label' => 'Product'],
            ['key' => 'quantity', 'label' => 'Qty'],
            ['key' => 'unit_price', 'label' => 'Sale Price', 'money' => true],
            ['key' => 'total', 'label' => 'Revenue', 'money' => true],
            ['key' => 'current_cost_price', 'label' => 'Current Cost', 'money' => true],
            ['key' => 'estimated_cost', 'label' => 'Estimated Cost', 'money' => true],
            ['key' => 'estimated_profit', 'label' => 'Estimated Profit', 'money' => true],
        ], $rows, $filters, [
            ['label' => 'Sales Revenue', 'value' => $totals->revenue],
            ['label' => 'Estimated Cost', 'value' => $totals->estimated_cost],
            ['label' => 'Estimated Gross Profit', 'value' => $profit],
        ], $filterFields);
    }
}

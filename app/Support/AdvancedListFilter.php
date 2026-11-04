<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdvancedListFilter
{
    public static function validatedFilters(Request $request, array $fields): array
    {
        $validated = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:100'],
            'match' => ['nullable', Rule::in(['all', 'any'])],
            'conditions' => ['nullable', 'array', 'max:10'],
            'conditions.*' => ['array:field,operator,value'],
            'conditions.*.field' => ['nullable', 'string'],
            'conditions.*.operator' => ['nullable', 'string'],
            'conditions.*.value' => ['nullable', 'string', 'max:100'],
        ])->validate();

        $conditions = [];
        foreach ($validated['conditions'] ?? [] as $condition) {
            $fieldKey = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? '';
            $value = $condition['value'] ?? '';

            if ($fieldKey === '' && $value === '') {
                continue;
            }

            $field = $fields[$fieldKey] ?? null;
            $allowedOperators = self::operatorsFor($field['type'] ?? 'text');
            $rules = [
                'field' => ['required', 'string', Rule::in(array_keys($fields))],
                'operator' => ['required', 'string', Rule::in($allowedOperators)],
                'value' => ['nullable', 'string', 'max:100'],
            ];

            if (! in_array($operator, ['is_empty', 'is_not_empty'], true)) {
                $rules['value'][] = 'required';
                $rules['value'][] = match ($field['type'] ?? 'text') {
                    'number' => 'numeric',
                    'date' => 'date_format:Y-m-d',
                    'select' => Rule::in(array_keys($field['options'] ?? [])),
                    default => 'string',
                };
            }

            $conditions[] = Validator::make($condition, $rules)->validate();
        }

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'match' => $validated['match'] ?? 'all',
            'conditions' => $conditions,
        ];
    }

    public static function apply(Builder $query, array $filters, array $fields): Builder
    {
        if ($filters['conditions'] === []) {
            return $query;
        }

        return $query->where(function (Builder $group) use ($filters, $fields) {
            foreach ($filters['conditions'] as $index => $condition) {
                $field = $fields[$condition['field']];
                $method = $filters['match'] === 'any' && $index > 0 ? 'orWhere' : 'where';
                $applyCondition = function (Builder $builder) use ($field, $condition) {
                    self::applyCondition($builder, $field['column'], $condition);
                };

                if (! empty($field['relation'])) {
                    $relationMethod = $method.'Has';
                    $group->{$relationMethod}($field['relation'], function (Builder $related) use ($applyCondition) {
                        $applyCondition($related);
                    });
                } else {
                    self::applyCondition($group, $field['column'], $condition, $method === 'orWhere' ? 'or' : 'and');
                }
            }
        });
    }

    public static function operatorsFor(string $type): array
    {
        return match ($type) {
            'text' => ['eq', 'neq', 'contains', 'starts_with', 'ends_with', 'is_empty', 'is_not_empty'],
            'select' => ['eq', 'neq'],
            'number', 'date' => ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'],
            default => throw new \InvalidArgumentException("Unsupported filter field type [{$type}]."),
        };
    }

    private static function applyCondition(
        Builder $query,
        string $column,
        array $condition,
        string $boolean = 'and'
    ): void {
        $operator = $condition['operator'];
        $value = $condition['value'] ?? null;

        if ($operator === 'is_empty') {
            $query->where(function (Builder $empty) use ($column) {
                $empty->whereNull($column)->orWhere($column, '=', '');
            }, $boolean);

            return;
        }

        if ($operator === 'is_not_empty') {
            $query->whereNotNull($column, $boolean)->where($column, '<>', '', $boolean);

            return;
        }

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
            default => throw new \InvalidArgumentException("Unsupported filter operator [{$operator}]."),
        };

        $query->where($column, $sqlOperator, $sqlValue, $boolean);
    }
}

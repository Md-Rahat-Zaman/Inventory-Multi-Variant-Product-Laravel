<?php

use App\Models\Product;
use App\Support\AdvancedListFilter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class);

function productListFilterFields(): array
{
    return [
        'name' => ['label' => 'Product', 'column' => 'products.name', 'type' => 'text'],
        'stock' => ['label' => 'Stock', 'column' => 'products.stock', 'type' => 'number'],
        'category' => ['label' => 'Category', 'relation' => 'category', 'column' => 'name', 'type' => 'text'],
    ];
}

test('advanced list filters combine conditions with the selected OR logic', function () {
    $request = Request::create('/', 'GET', [
        'match' => 'any',
        'conditions' => [
            ['field' => 'name', 'operator' => 'contains', 'value' => 'shirt'],
            ['field' => 'stock', 'operator' => 'gt', 'value' => '5'],
        ],
    ]);
    $fields = productListFilterFields();
    $filters = AdvancedListFilter::validatedFilters($request, $fields);
    $query = AdvancedListFilter::apply(Product::query(), $filters, $fields);

    expect($filters['match'])->toBe('any')
        ->and($query->getBindings())->toBe(['%shirt%', '5'])
        ->and($query->toSql())->toContain(' or ');
});

test('advanced list filters support relation conditions', function () {
    $request = Request::create('/', 'GET', [
        'conditions' => [
            ['field' => 'category', 'operator' => 'contains', 'value' => 'apparel'],
        ],
    ]);
    $fields = productListFilterFields();
    $filters = AdvancedListFilter::validatedFilters($request, $fields);
    $query = AdvancedListFilter::apply(Product::query(), $filters, $fields);

    expect($query->toSql())->toContain('exists')
        ->and($query->getBindings())->toContain('%apparel%');
});

test('advanced list filters reject values that do not match field types', function () {
    $request = Request::create('/', 'GET', [
        'conditions' => [
            ['field' => 'stock', 'operator' => 'gt', 'value' => 'many'],
        ],
    ]);

    expect(fn () => AdvancedListFilter::validatedFilters($request, productListFilterFields()))
        ->toThrow(ValidationException::class);
});

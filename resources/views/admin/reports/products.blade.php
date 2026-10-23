@extends('admin.dashboard.dashboard')
@section('admin_title', 'Products & stock report · Nebula Mart Admin')
@section('admin_contents')

    @include('admin.reports._filters', ['title' => 'Products & stock', 'subtitle' => 'Best sellers and inventory health', 'exportKey' => 'products'])

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'box', 'label' => 'Stock value (cost)', 'value' => '৳'.number_format($stock['at_cost']), 'hint' => $stock['units'].' units · '.$stock['products'].' products'])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'dollar', 'label' => 'Stock value (retail)', 'value' => '৳'.number_format($stock['at_retail']), 'hint' => 'Potential margin ৳'.number_format($stock['at_retail'] - $stock['at_cost'])])
        @include('admin.reports._stat', ['tone' => 'is-11', 'icon' => 'alert', 'label' => 'Low stock', 'value' => number_format($stock['low']), 'hint' => 'At or below the alert level'])
        @include('admin.reports._stat', ['tone' => 'is-5', 'icon' => 'x', 'label' => 'Out of stock', 'value' => number_format($stock['out']), 'hint' => 'Active tracked products'])
    </div>

    <section class="card">
        <div class="card-h"><h3>Best selling products</h3></div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Product</th><th>Orders</th><th>Units</th><th>Revenue</th><th>Cost</th><th class="text-right">Profit</th></tr></thead>
                <tbody>
                    @forelse ($products as $row)
                        <tr>
                            <td><b>{{ $row->name }}</b></td>
                            <td>{{ $row->orders }}</td>
                            <td>{{ $row->units }}</td>
                            <td>৳{{ number_format($row->revenue) }}</td>
                            <td>৳{{ number_format($row->cogs) }}</td>
                            <td class="text-right font-bold">৳{{ number_format($row->profit) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-ink/55">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid xl:grid-cols-3 gap-6 items-start">
        <section class="card">
            <div class="card-h"><h3>Low stock</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Product</th><th class="text-right">Left</th></tr></thead>
                    <tbody>
                        @forelse ($stock['low_list'] as $product)
                            <tr>
                                <td><a href="{{ route('admin.products.edit', $product) }}" class="font-bold hover:text-primary">{{ $product->name }}</a><p class="text-xs text-ink/50">{{ $product->sku }}</p></td>
                                <td class="text-right"><span class="badge b-warn">{{ $product->stock }} / {{ $product->low_stock }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-ink/55">Nothing is running low.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Out of stock</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Product</th><th class="text-right">Left</th></tr></thead>
                    <tbody>
                        @forelse ($stock['out_list'] as $product)
                            <tr>
                                <td><a href="{{ route('admin.products.edit', $product) }}" class="font-bold hover:text-primary">{{ $product->name }}</a><p class="text-xs text-ink/50">{{ $product->sku }}</p></td>
                                <td class="text-right"><span class="badge b-danger">0</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-ink/55">Everything is in stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>No sales in this period</h3></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Product</th><th class="text-right">In stock</th></tr></thead>
                    <tbody>
                        @forelse ($slowMovers as $product)
                            <tr>
                                <td><a href="{{ route('admin.products.edit', $product) }}" class="font-bold hover:text-primary">{{ $product->name }}</a><p class="text-xs text-ink/50">{{ $product->sku }}</p></td>
                                <td class="text-right"><b>{{ $product->stock }}</b></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-ink/55">Every product in stock sold.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="card">
        <div class="card-h"><h3>Sales by category</h3></div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Category</th><th>Units</th><th class="text-right">Revenue</th></tr></thead>
                <tbody>
                    @forelse ($categories as $row)
                        <tr><td><b>{{ $row->name }}</b></td><td>{{ $row->units }}</td><td class="text-right font-bold">৳{{ number_format($row->revenue) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-ink/55">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

@endsection

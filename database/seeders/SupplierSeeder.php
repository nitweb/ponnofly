<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'EcomSeller',
                'contact_person' => null,
                'phone' => null,
                'email' => null,
                'address' => 'ecomsellerbd.com',
                'notes' => 'Products imported from ecomsellerbd.com reseller catalog.',
                'is_active' => true,
            ],
            [
                'name' => 'Xaxis',
                'contact_person' => null,
                'phone' => null,
                'email' => null,
                'address' => 'xaxisdigitalllc.com',
                'notes' => 'Products imported from xaxisdigitalllc.com.',
                'is_active' => true,
            ],
            [
                'name' => 'AngelRose',
                'contact_person' => null,
                'phone' => null,
                'email' => null,
                'address' => 'angelrosebeautycarebd.com',
                'notes' => 'Products imported from Angel Rose Beauty Care BD.',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}

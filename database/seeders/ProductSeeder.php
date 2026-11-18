<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Sample catalog seeder disabled — products come from suppliers:
 * - EcomSeller: php artisan products:import-ecomseller
 * - Xaxis: php artisan db:seed --class=XaxisProductsSeeder
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('ProductSeeder skipped. Use EcomSeller import or XaxisProductsSeeder instead.');
    }
}

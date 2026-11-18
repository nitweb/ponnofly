<?php

namespace App\Console\Commands;

use Database\Seeders\ClearProductsSeeder;
use Illuminate\Console\Command;

class ClearProducts extends Command
{
    protected $signature = 'products:clear {--force : Skip confirmation}';

    protected $description = 'Delete all products and related catalog data (images, variants, reviews, cart/wishlist lines)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Delete ALL products? This cannot be undone.')) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }

        $this->call('db:seed', ['--class' => ClearProductsSeeder::class]);

        return self::SUCCESS;
    }
}

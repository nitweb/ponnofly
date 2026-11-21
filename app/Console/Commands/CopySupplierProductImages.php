<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CopySupplierProductImages extends Command
{
    protected $signature = 'products:copy-images {supplier=5}';

    protected $description = 'Download supplier product images into thumbnail and gallery folders';

    public function handle(): int
    {
        set_time_limit(0);

        $supplierId = (int) $this->argument('supplier');

        $destination = public_path('product_toy');

        File::ensureDirectoryExists($destination);

        $totalProducts = 0;
        $totalImages = 0;
        $copied = 0;
        $duplicates = 0;
        $failed = 0;

        $failedImages = [];

        $reportPath = $destination . DIRECTORY_SEPARATOR . 'failed-images.json';

        if (File::exists($reportPath)) {
            File::delete($reportPath);
        }

        $this->info("Starting image download for supplier ID: {$supplierId}");

        Product::where('supplier_id', $supplierId)
            ->with('images')
            ->chunkById(10, function ($products) use (
                &$totalProducts,
                &$totalImages,
                &$copied,
                &$duplicates,
                &$failed,
                &$failedImages,
                $destination
            ) {

                foreach ($products as $product) {

                    $totalProducts++;

                    // Generate unique product folder name
                    $productName = Str::slug($product->name);

                    if (empty($productName)) {
                        $productName = 'product';
                    }

                    // Product ID prevents folder name conflicts
                    $productFolder = $productName . '-' . $product->id;

                    $productPath = $destination . DIRECTORY_SEPARATOR . $productFolder;

                    // Create thumbnail and gallery directories
                    $thumbnailPath = $productPath . DIRECTORY_SEPARATOR . 'thumbnail';

                    $galleryPath = $productPath . DIRECTORY_SEPARATOR . 'gallery';

                    File::ensureDirectoryExists($thumbnailPath);
                    File::ensureDirectoryExists($galleryPath);

                    // Images are already ordered by sort_order and id
                    $images = $product->images;

                    $imageNumber = 0;

                    // Prevent duplicate content within this product
                    $productHashes = [];

                    foreach ($images as $image) {

                        $totalImages++;

                        $imageNumber++;

                        $url = trim((string) ($image->path ?? ''));

                        // Validate HTTP/HTTPS URL
                        $parsedUrl = parse_url($url);

                        if (
                            !filter_var($url, FILTER_VALIDATE_URL) ||
                            empty($parsedUrl['host']) ||
                            empty($parsedUrl['scheme']) ||
                            !in_array(
                                strtolower($parsedUrl['scheme']),
                                ['http', 'https'],
                                true
                            )
                        ) {

                            $failed++;

                            $failedImages[] = [
                                'product' => $product->name,
                                'image_id' => $image->id,
                                'url' => $url,
                                'reason' => 'Invalid HTTP/HTTPS image URL',
                            ];

                            continue;
                        }

                        try {

                            // Download supplier image
                            $response = Http::timeout(60)
                                ->connectTimeout(15)
                                ->retry(2, 1000)
                                ->withOptions([
                                    'allow_redirects' => [
                                        'max' => 5,
                                    ],
                                ])
                                ->get($url);

                            if (!$response->successful()) {

                                $failed++;

                                $failedImages[] = [
                                    'product' => $product->name,
                                    'image_id' => $image->id,
                                    'url' => $url,
                                    'reason' => 'HTTP ' . $response->status(),
                                ];

                                continue;
                            }

                            // Validate image content type
                            $contentType = strtolower(
                                trim(
                                    explode(
                                        ';',
                                        $response->header('Content-Type') ?? ''
                                    )[0]
                                )
                            );

                            $allowedTypes = [
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/gif',
                                'image/avif',
                            ];

                            if (!in_array($contentType, $allowedTypes, true)) {

                                $failed++;

                                $failedImages[] = [
                                    'product' => $product->name,
                                    'image_id' => $image->id,
                                    'url' => $url,
                                    'reason' => 'Invalid image content type: ' . $contentType,
                                ];

                                continue;
                            }

                            $body = $response->body();

                            if (empty($body)) {

                                $failed++;

                                $failedImages[] = [
                                    'product' => $product->name,
                                    'image_id' => $image->id,
                                    'reason' => 'Empty image response',
                                ];

                                continue;
                            }

                            // Generate SHA256 image hash
                            $hash = hash('sha256', $body);

                            // Skip duplicate images within the same product
                            if (isset($productHashes[$hash])) {

                                $duplicates++;

                                $this->line(
                                    "Duplicate skipped: {$product->name} - Image {$imageNumber}"
                                );

                                continue;
                            }

                            $productHashes[$hash] = true;

                            // Determine file extension
                            $extension = match ($contentType) {
                                'image/jpeg' => 'jpg',
                                'image/png' => 'png',
                                'image/webp' => 'webp',
                                'image/gif' => 'gif',
                                'image/avif' => 'avif',
                            };

                            // First image goes to thumbnail
                            if ($imageNumber === 1) {

                                $targetFolder = $thumbnailPath;

                                $filename = $productName . '.' . $extension;

                            } else {

                                // Remaining images go to gallery
                                $targetFolder = $galleryPath;

                                $galleryNumber = $imageNumber - 1;

                                $filename = 'image-' . $galleryNumber . '.' . $extension;
                            }

                            $target = $targetFolder . DIRECTORY_SEPARATOR . $filename;

                            // Skip if the same image is already saved
                            if (File::exists($target)) {

                                $existingHash = hash_file('sha256', $target);

                                if ($existingHash === $hash) {

                                    $duplicates++;

                                    $this->line(
                                        "Already exists: {$productFolder}/" .
                                        basename($targetFolder) . "/{$filename}"
                                    );

                                    continue;
                                }
                            }

                            // Save image
                            $saved = File::put($target, $body);

                            if ($saved === false) {

                                $failed++;

                                $failedImages[] = [
                                    'product' => $product->name,
                                    'image_id' => $image->id,
                                    'reason' => 'Failed to save image',
                                ];

                                continue;
                            }

                            $copied++;

                            $relativePath = $productFolder . '/' .
                                basename($targetFolder) . '/' . $filename;

                            $this->info("Downloaded: {$relativePath}");

                        } catch (Throwable $e) {

                            $failed++;

                            $failedImages[] = [
                                'product' => $product->name,
                                'image_id' => $image->id,
                                'url' => $url,
                                'reason' => $e->getMessage(),
                            ];

                            $this->error(
                                "Failed: {$product->name} - {$e->getMessage()}"
                            );
                        }
                    }
                }

                // Progress report
                $this->newLine();

                $this->info(
                    "Products: {$totalProducts} | " .
                    "Copied: {$copied} | " .
                    "Duplicates: {$duplicates} | " .
                    "Failed: {$failed}"
                );

            });

        // Final summary
        $this->newLine();

        $this->info('Image download process completed!');

        $this->table(
            ['Description', 'Count'],
            [
                ['Total Products', $totalProducts],
                ['Total Images', $totalImages],
                ['Successfully Downloaded', $copied],
                ['Duplicate Images Skipped', $duplicates],
                ['Failed Images', $failed],
            ]
        );

        $this->info("Destination: {$destination}");

        // Save failed image report
        if (!empty($failedImages)) {

            File::put(
                $reportPath,
                json_encode(
                    $failedImages,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                )
            );

            $this->warn("Failed image report: {$reportPath}");

        } else {

            $this->info('All images processed successfully!');
        }

        return self::SUCCESS;
    }
}

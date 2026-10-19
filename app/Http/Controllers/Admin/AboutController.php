<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutPageRequest;
use App\Models\AboutPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AboutController extends Controller
{
    public function edit(): View
    {
        return view('admin.about.edit', ['about' => AboutPage::instance()]);
    }

    public function update(AboutPageRequest $request): RedirectResponse
    {
        $about = AboutPage::instance();

        $data = $request->safe()->except(['hero_image', 'story_image', 'gallery_alt', 'gallery_images', 'stats', 'story_points', 'values', 'steps']);

        $data['stats'] = collect($request->input('stats', []))
            ->filter(fn ($row) => filled($row['label'] ?? null) && filled($row['value'] ?? null))
            ->map(fn ($row) => [
                'value' => (float) $row['value'] == (int) $row['value'] ? (int) $row['value'] : (float) $row['value'],
                'suffix' => (string) ($row['suffix'] ?? ''),
                'label' => $row['label'],
            ])->values()->all();

        $data['story_points'] = collect($request->input('story_points', []))
            ->filter(fn ($point) => filled($point))->values()->all();

        $data['values'] = collect($request->input('values', []))
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->map(fn ($row) => [
                'icon' => $row['icon'] ?? 'shield',
                'title' => $row['title'],
                'text' => (string) ($row['text'] ?? ''),
            ])->values()->all();

        $data['steps'] = collect($request->input('steps', []))
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->map(fn ($row) => ['title' => $row['title'], 'text' => (string) ($row['text'] ?? '')])
            ->values()->all();

        if ($request->hasFile('hero_image')) {
            $this->deleteImage($about->hero_image);
            $data['hero_image'] = $this->storeImage($request->file('hero_image'), 1000, 800);
        }

        if ($request->hasFile('story_image')) {
            $this->deleteImage($about->story_image);
            $data['story_image'] = $this->storeImage($request->file('story_image'), 900, 1100);
        }

        $sizes = [[900, 600], [600, 600], [600, 600]];
        $gallery = $about->gallery ?? [];

        foreach ($sizes as $index => [$width, $height]) {
            $slot = $gallery[$index] ?? ['image' => null, 'alt' => ''];
            $slot['alt'] = (string) $request->input("gallery_alt.$index", $slot['alt'] ?? '');

            if ($file = $request->file("gallery_images.$index")) {
                $this->deleteImage($slot['image'] ?? null);
                $slot['image'] = $this->storeImage($file, $width, $height);
            }

            $gallery[$index] = $slot;
        }

        $data['gallery'] = array_values($gallery);

        $about->update($data);

        return to_route('admin.about.edit')->with('success', 'About page updated');
    }

    private function storeImage(UploadedFile $file, int $width, int $height): string
    {
        File::ensureDirectoryExists(public_path(AboutPage::IMAGE_DIRECTORY));

        $path = AboutPage::IMAGE_DIRECTORY.'/'.Str::uuid().'.webp';

        (new ImageManager(new Driver))
            ->read($file)
            ->cover($width, $height)
            ->toWebp(85)
            ->save(public_path($path));

        return $path;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, AboutPage::IMAGE_DIRECTORY.'/')) {
            File::delete(public_path($path));
        }
    }
}

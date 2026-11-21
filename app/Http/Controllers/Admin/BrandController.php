<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BrandController extends Controller
{
    private const LOGO_SIZE = 400;

    private const DIRECTORY = 'uploads/brands';

    public function index(): View
    {
        return view('admin.brands.index', [
            'brands' => Brand::withCount('products')->orderBy('sort_order')->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.brands.create');
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('logo');
        $data['slug'] = $this->resolveSlug($request->input('slug'), $data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeLogo($request->file('logo'));
        }

        Brand::create($data);

        return to_route('admin.brands.index')->with('success', 'Brand saved');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->safe()->except('logo');
        $data['slug'] = $this->resolveSlug($request->input('slug'), $data['name'], $brand->id);

        if ($request->hasFile('logo')) {
            $this->deleteFile($brand->logo);
            $data['logo'] = $this->storeLogo($request->file('logo'));
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteFile($brand->logo);
            $data['logo'] = null;
        }

        $brand->update($data);

        return to_route('admin.brands.index')->with('success', 'Brand updated');
    }

    public function toggle(Brand $brand): RedirectResponse
    {
        $brand->update(['is_active' => ! $brand->is_active]);

        return back()->with('success', $brand->is_active ? 'Brand enabled' : 'Brand disabled');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->deleteFile($brand->logo);
        $brand->delete();

        return to_route('admin.brands.index')->with('success', 'Brand deleted');
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'brand';
        $candidate = $base;
        $suffix = 2;

        while (Brand::where('slug', $candidate)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function storeLogo(UploadedFile $file): string
    {
        File::ensureDirectoryExists(public_path(self::DIRECTORY));

        if ($file->getClientOriginalExtension() === 'svg' || $file->getMimeType() === 'image/svg+xml') {
            $name = Str::uuid().'.svg';
            $file->move(public_path(self::DIRECTORY), $name);

            return self::DIRECTORY.'/'.$name;
        }

        $path = self::DIRECTORY.'/'.Str::uuid().'.webp';

        (new ImageManager(new Driver))
            ->read($file)
            ->contain(self::LOGO_SIZE, self::LOGO_SIZE, 'ffffff00')
            ->toWebp(90)
            ->save(public_path($path));

        return $path;
    }

    private function deleteFile(?string $path): void
    {
        if ($path && str_starts_with($path, self::DIRECTORY.'/')) {
            File::delete(public_path($path));
        }
    }
}

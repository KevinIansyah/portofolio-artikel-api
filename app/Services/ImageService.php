<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ImageService
{
  /**
   * Upload and optimize image.
   */
  public function upload(
    UploadedFile $file,
    string $name,
    string $folder,
    int $width = 1200,
    int $quality = 85
  ): string {
    $slug = Str::slug($name);
    $timestamp = time();
    $filename = "{$timestamp}-{$slug}.webp";

    $path = "{$folder}/{$filename}";
    $fullPath = storage_path('app/public/' . $path);

    $manager = new ImageManager(new Driver());

    $image = $manager
      ->read($file->getRealPath())
      ->scale(width: $width)
      ->toWebp(quality: $quality);

    Storage::disk('public')->put($path, (string) $image);

    OptimizerChainFactory::create()
      ->optimize($fullPath);

    return Storage::url($path);
  }

  /**
   * Delete image from storage.
   */
  public function delete(?string $url): void
  {
    if (!$url) {
      return;
    }

    $parsed = parse_url($url);
    $path = str_replace('/storage/', '', $parsed['path'] ?? '');

    if (Storage::disk('public')->exists($path)) {
      Storage::disk('public')->delete($path);
    }
  }

  /**
   * Update image (delete old and upload new).
   */
  public function update(
    ?string $oldUrl,
    UploadedFile $newFile,
    string $name,
    string $folder,
    int $width = 1200,
    int $quality = 85
  ): string {
    if ($oldUrl) {
      $this->delete($oldUrl);
    }

    return $this->upload($newFile, $name, $folder, $width, $quality);
  }
}

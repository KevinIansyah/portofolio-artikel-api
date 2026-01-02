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
   *
   * @param UploadedFile $file
   * @param string $name Name for the file (will be slugified)
   * @param string $folder Folder to store the image (e.g., 'projects', 'articles', 'products')
   * @param int $width Max width for scaling
   * @param int $quality WebP quality (1-100)
   * @return string Public URL of the uploaded image
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
   *
   * @param string|null $url
   * @return void
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
   *
   * @param string|null $oldUrl
   * @param UploadedFile $newFile
   * @param string $name
   * @param string $folder
   * @param int $width
   * @param int $quality
   * @return string
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

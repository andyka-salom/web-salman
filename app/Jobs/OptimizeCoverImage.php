<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizeCoverImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imagePath;

    /**
     * Create a new job instance.
     * @param string $imagePath Relative path in storage
     */
    public function __construct($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $disk = Storage::disk('public');

            if (!$disk->exists($this->imagePath)) return;

            $manager = new ImageManager(new Driver());
            // Read bytes from the public disk (works for local & S3)
            $image = $manager->read($disk->get($this->imagePath));

            // Gunakan scaleDown agar tidak memperbesar gambar kecil (lebih cepat)
            if ($image->width() > 1200) {
                $image->scaleDown(width: 1200);
            }

            // Simpan dengan kualitas sedikit lebih rendah untuk performa (80)
            $extension = pathinfo($this->imagePath, PATHINFO_EXTENSION) ?: 'jpg';
            $disk->put($this->imagePath, (string) $image->encodeByExtension($extension, quality: 80));

        } catch (\Exception $e) {
            Log::error('Image optimization failed for: ' . $this->imagePath, ['error' => $e->getMessage()]);
        }
    }
}

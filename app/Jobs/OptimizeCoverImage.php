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
            $fullPath = storage_path('app/public/' . $this->imagePath);

            if (!file_exists($fullPath)) return;

            $manager = new ImageManager(new Driver());
            $image = $manager->read($fullPath);

            // Gunakan scaleDown agar tidak memperbesar gambar kecil (lebih cepat)
            if ($image->width() > 1200) {
                $image->scaleDown(width: 1200);
            }

            // Simpan dengan kualitas sedikit lebih rendah untuk performa (80)
            $image->save($fullPath, quality: 80);

        } catch (\Exception $e) {
            Log::error('Image optimization failed for: ' . $this->imagePath, ['error' => $e->getMessage()]);
        }
    }
}

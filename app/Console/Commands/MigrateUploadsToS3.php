<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

/**
 * Copies existing upload files from the local filesystem into the "public" disk.
 *
 * Run this AFTER setting FILESYSTEM_PUBLIC_DRIVER=s3 so that the "public" disk
 * points at the S3 (Biznet) bucket. Source files are read straight from the
 * local filesystem (independent of the disk driver), so old files keep the same
 * relative path (and therefore the same URL) once uploaded.
 *
 *   php artisan uploads:migrate-to-s3 --dry-run
 *   php artisan uploads:migrate-to-s3
 *   php artisan uploads:migrate-to-s3 --source=storage/app/private
 *   php artisan uploads:migrate-to-s3 --overwrite
 */
class MigrateUploadsToS3 extends Command
{
    protected $signature = 'uploads:migrate-to-s3
        {--source= : Absolute or project-relative source dir (default: storage/app/public)}
        {--overwrite : Re-upload even if the object already exists on the target disk}
        {--dry-run : List what would be uploaded without writing anything}';

    protected $description = 'Copy existing local upload files into the public disk (S3/Biznet).';

    public function handle(): int
    {
        $source = $this->option('source') ?: storage_path('app/public');
        if (! str_starts_with($source, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:/', $source)) {
            $source = base_path($source);
        }

        if (! is_dir($source)) {
            $this->error("Source directory not found: {$source}");
            return self::FAILURE;
        }

        $disk      = Storage::disk('public');
        $dryRun    = (bool) $this->option('dry-run');
        $overwrite = (bool) $this->option('overwrite');

        $this->info("Source : {$source}");
        $this->info('Target : public disk (driver: ' . config('filesystems.disks.public.driver') . ')');
        $this->info($dryRun ? 'Mode   : DRY RUN (no writes)' : 'Mode   : LIVE');
        $this->newLine();

        $finder = (new Finder())->files()->in($source)->ignoreDotFiles(true);

        $copied = $skipped = $failed = 0;

        foreach ($finder as $file) {
            // Relative path with forward slashes = the object key on the disk.
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());

            // Never push the storage symlink or compiled framework junk.
            if (str_starts_with($relative, 'framework/') || $relative === 'storage') {
                continue;
            }

            try {
                if (! $overwrite && $disk->exists($relative)) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  would upload: {$relative}");
                    $copied++;
                    continue;
                }

                $disk->put($relative, file_get_contents($file->getRealPath()));
                $copied++;

                if ($copied % 50 === 0) {
                    $this->line("  uploaded {$copied} files...");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("  FAILED {$relative}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Done. uploaded={$copied} skipped(existing)={$skipped} failed={$failed}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}

<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Http\Requests\Features\CampaignRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CampaignController extends Controller
{
    use AuthorizesRequests;

    /**
     * Maximum image width for optimization
     */
    private const MAX_IMAGE_WIDTH = 1200;

    /**
     * Image compression quality (1-100)
     */
    private const IMAGE_QUALITY = 80;

    /**
     * Storage path for campaign images
     */
    private const STORAGE_PATH = 'campaigns/images';

    /**
     * Available campaign categories
     */
    private const CATEGORIES = [
        'Safety',
        'Health',
        'Environment',
        'Operations',
        'General'
    ];

    /**
     * Display campaigns listing
     */
    public function index(Request $request)
    {
        $this->authorize('manage campaigns');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaigns']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        return view('features.campaigns.index', compact('breadcrumbs'));
    }

    /**
     * Get DataTables data for campaigns
     */
    protected function getDatatablesData(Request $request)
    {
        $query = Campaign::with('creator:id,name')
            ->select([
                'id', 'title', 'category', 'published_at',
                'is_published', 'is_featured', 'view_count',
                'created_by', 'created_at'
            ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('is_published', (int) $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('title', function($row) {
                $featured = $row->is_featured
                    ? '<span class="badge badge-warning ms-1" title="Featured"><i class="feather-star"></i></span>'
                    : '';
                return e($row->title) . $featured;
            })
            ->addColumn('creator_name', function($row) {
                return $row->creator ? e($row->creator->name) : 'Unknown';
            })
            ->editColumn('published_at', function($row) {
                return $row->published_at ? $row->published_at->format('d M Y H:i') : '-';
            })
            ->addColumn('status', function($row) {
                return $row->is_published
                    ? '<span class="badge badge-success">Published</span>'
                    : '<span class="badge badge-secondary">Draft</span>';
            })
            ->addColumn('action', function($campaign) {
                return view('features.campaigns.partials.actions', compact('campaign'))->render();
            })
            ->rawColumns(['title', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show create campaign form
     */
    public function create()
    {
        $this->authorize('manage campaigns');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaigns', 'url' => route('campaigns.index')],
            ['label' => 'Create']
        ];

        $categories = self::CATEGORIES;

        return view('features.campaigns.create', compact('breadcrumbs', 'categories'));
    }

    /**
     * Store new campaign
     */
    public function store(CampaignRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $this->prepareDataForStore($request);

            // Handle media upload
            if ($request->hasFile('media')) {
                $data['media'] = $this->handleMediaUpload($request->file('media'));
                $data['media_type'] = 'image';
            }

            // Set published_at if publishing
            if ($data['is_published'] && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $campaign = Campaign::create($data);

            DB::commit();

            Log::info('Campaign created successfully', [
                'campaign_id' => $campaign->id,
                'title' => $campaign->title,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully',
                'redirect' => route('campaigns.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Campaign creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display campaign details
     */
    public function show(Campaign $campaign)
    {
        $this->authorize('manage campaigns');

        $campaign->load('creator');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaigns', 'url' => route('campaigns.index')],
            ['label' => Str::limit($campaign->title, 20)]
        ];

        return view('features.campaigns.show', compact('campaign', 'breadcrumbs'));
    }

    /**
     * Show edit campaign form
     */
    public function edit(Campaign $campaign)
    {
        $this->authorize('manage campaigns');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaigns', 'url' => route('campaigns.index')],
            ['label' => 'Edit']
        ];

        $categories = self::CATEGORIES;

        return view('features.campaigns.create', compact('campaign', 'breadcrumbs', 'categories'));
    }

    /**
     * Update existing campaign
     */
    public function update(CampaignRequest $request, Campaign $campaign): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $this->prepareDataForUpdate($request, $campaign);

            // Handle media upload
            if ($request->hasFile('media')) {
                // Delete old media
                $this->deleteOldMedia($campaign->media);

                $data['media'] = $this->handleMediaUpload($request->file('media'));
                $data['media_type'] = 'image';
            }

            // Set published_at when first publishing
            if ($data['is_published'] && !$campaign->is_published && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $campaign->update($data);

            DB::commit();

            Log::info('Campaign updated successfully', [
                'campaign_id' => $campaign->id,
                'title' => $campaign->title,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign updated successfully',
                'redirect' => route('campaigns.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Campaign update failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete campaign
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Delete associated media
            $this->deleteOldMedia($campaign->media);

            $campaign->delete();

            DB::commit();

            Log::info('Campaign deleted successfully', [
                'campaign_id' => $campaign->id,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Campaign deletion failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle campaign publish status
     */
    public function togglePublish(Campaign $campaign): JsonResponse
    {
        try {
            if ($campaign->is_published) {
                $campaign->unpublish();
                $message = 'Campaign unpublished successfully';
            } else {
                $campaign->publish();
                $message = 'Campaign published successfully';
            }

            Log::info('Campaign publish status toggled', [
                'campaign_id' => $campaign->id,
                'is_published' => $campaign->is_published,
                'toggled_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle publish status', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating publish status'
            ], 500);
        }
    }

    /**
     * Toggle campaign featured status
     */
    public function toggleFeatured(Campaign $campaign): JsonResponse
    {
        try {
            $campaign->toggleFeatured();

            $status = $campaign->is_featured ? 'featured' : 'unfeatured';

            Log::info('Campaign featured status toggled', [
                'campaign_id' => $campaign->id,
                'is_featured' => $campaign->is_featured,
                'toggled_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Campaign marked as {$status}"
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle featured status', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating featured status'
            ], 500);
        }
    }

    // ============================================================
    // PRIVATE HELPER METHODS
    // ============================================================

    /**
     * Prepare data for storing new campaign
     */
    private function prepareDataForStore(CampaignRequest $request): array
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['is_published'] = $request->has('is_published');
        $data['is_featured'] = $request->has('is_featured');
        $data['slug'] = $this->generateUniqueSlug($request->title);

        return $data;
    }

    /**
     * Prepare data for updating campaign
     */
    private function prepareDataForUpdate(CampaignRequest $request, Campaign $campaign): array
    {
        $data = $request->validated();
        $data['is_published'] = $request->has('is_published');
        $data['is_featured'] = $request->has('is_featured');

        // Regenerate slug only if title changed
        if ($request->title !== $campaign->title) {
            $data['slug'] = $this->generateUniqueSlug($request->title, $campaign->id);
        } else {
            unset($data['title']);
        }

        return $data;
    }

    /**
     * Handle media upload with optimization
     */
    private function handleMediaUpload($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = uniqid() . '_' . time() . '.' . $extension;

        // Store original to public disk first (acts as fallback if optimize fails)
        $path = $file->storeAs(self::STORAGE_PATH, $filename, 'public');

        try {
            // Initialize Image Manager with GD Driver
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            // Resize if image is too large (maintain aspect ratio)
            if ($image->width() > self::MAX_IMAGE_WIDTH) {
                $image->scaleDown(width: self::MAX_IMAGE_WIDTH);
            }

            // Encode with compression based on format
            $encoded = match($extension) {
                'jpg', 'jpeg' => $image->toJpeg(quality: self::IMAGE_QUALITY),
                'png' => $image->toPng(),
                'webp' => $image->toWebp(quality: self::IMAGE_QUALITY),
                'gif' => $image->toGif(),
                default => $image->toJpeg(quality: self::IMAGE_QUALITY)
            };

            // Save optimized image (overwrites the original on the public disk)
            Storage::disk('public')->put($path, (string) $encoded);

            Log::info('Image uploaded and optimized successfully', [
                'path' => $path,
                'original_size' => $file->getSize(),
                'extension' => $extension
            ]);

        } catch (\Exception $e) {
            Log::warning('Image optimization failed, using original file', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }

        return $path;
    }

    /**
     * Delete old media file from storage
     */
    private function deleteOldMedia(?string $mediaPath): void
    {
        if ($mediaPath && Storage::disk('public')->exists($mediaPath)) {
            try {
                Storage::disk('public')->delete($mediaPath);

                Log::info('Old media deleted successfully', [
                    'path' => $mediaPath
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to delete old media', [
                    'path' => $mediaPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Generate unique slug for campaign
     */
    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (Campaign::where('slug', $slug)
            ->where('id', '!=', $ignoreId)
            ->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}

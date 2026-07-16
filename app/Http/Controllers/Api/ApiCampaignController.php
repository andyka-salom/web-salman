<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiCampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     * Response excludes the 'content' field.
     */
    public function index(Request $request): JsonResponse
    {
        // Mulai query dengan scope 'published' agar draft tidak muncul
        $query = Campaign::published();

        // Implementasi Scope Search jika ada parameter 'search'
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Implementasi Scope ByCategory jika ada parameter 'category'
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Implementasi Popular / Recent sorting
        if ($request->get('sort') === 'popular') {
            $query->orderBy('view_count', 'desc');
        } else {
            $query->orderBy('published_at', 'desc');
        }

        // Select kolom yang dibutuhkan saja untuk efisiensi bandwidth (tanpa 'content')
        $campaigns = $query->select([
            'id',
            'title',
            'slug',
            'summary',
            'published_at',
            'media',
            'media_type',
            'category',
            'view_count',
            'created_by'
        ])
        ->with('creator:id,name')
        ->paginate(15);

        // Transformasi collection
        $campaigns->through(function ($campaign) {
            return [
                'id' => $campaign->id,
                'title' => $campaign->title,
                'slug' => $campaign->slug,
                'summary' => $campaign->summary,
                'category' => $campaign->category,
                'media_url' => $campaign->media_url,
                'media_type' => $campaign->media_type,
                'published_at' => $campaign->published_at,
                'date_formatted' => $campaign->published_at ? $campaign->published_at->format('d M Y') : null,
                'human_date' => $campaign->published_at ? $campaign->published_at->diffForHumans() : null,
                'view_count' => $campaign->view_count,
                'creator' => $campaign->creator ? $campaign->creator->name : 'Admin',
            ];
        });

        // Response konsisten dengan wrapper 'data'
        return response()->json([
            'message' => 'Campaigns retrieved successfully',
            'status' => 200,
            'data' => $campaigns->items(),
            'pagination' => [
                'current_page' => $campaigns->currentPage(),
                'last_page' => $campaigns->lastPage(),
                'per_page' => $campaigns->perPage(),
                'total' => $campaigns->total(),
                'from' => $campaigns->firstItem(),
                'to' => $campaigns->lastItem(),
            ]
        ], 200);
    }

    /**
     * Display the specified resource.
     * Includes all fields.
     */
    public function show($id): JsonResponse
    {
        // Cari data berdasarkan ID atau Slug
        $campaign = Campaign::published()
            ->with('creator:id,name')
            ->where(function($q) use ($id) {
                $q->where('id', $id)->orWhere('slug', $id);
            })
            ->firstOrFail();

        // Increment view count
        $campaign->incrementViews();

        // Siapkan respon
        $data = [
            'id' => $campaign->id,
            'title' => $campaign->title,
            'slug' => $campaign->slug,
            'summary' => $campaign->summary,
            'content' => $campaign->content,
            'category' => $campaign->category,
            'media_url' => $campaign->media_url,
            'media_type' => $campaign->media_type,
            'published_at' => $campaign->published_at,
            'date_formatted' => $campaign->published_at ? $campaign->published_at->format('d M Y') : null,
            'read_time' => $campaign->read_time,
            'view_count' => $campaign->view_count,
            'creator' => $campaign->creator ? [
                'id' => $campaign->creator->id,
                'name' => $campaign->creator->name,
            ] : null,
        ];

        return response()->json([
            'message' => 'Campaign retrieved successfully',
            'status' => 200,
            'data' => $data
        ], 200);
    }
}

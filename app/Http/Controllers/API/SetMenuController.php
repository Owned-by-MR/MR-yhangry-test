<?php
// app/Http/Controllers/API/SetMenuController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SetMenu;
use App\Models\Cuisine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SetMenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'cuisine_slug' => 'nullable|string',
                'guests' => 'nullable|integer|min:1',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
                'sort_by' => 'nullable|string|in:price_asc,price_desc,orders',
            ]);

            // Build set menus query
            $query = SetMenu::with('cuisines')
                ->where('status', true);

            // Apply cuisine filter
            if ($request->cuisine_slug) {
                $query->whereHas('cuisines', function ($q) use ($request) {
                    $q->where('slug', $request->cuisine_slug);
                });
            }

            // Apply sorting
            switch ($request->sort_by) {
                case 'price_asc':
                    $query->orderBy('price_per_person', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price_per_person', 'desc');
                    break;
                case 'orders':
                    $query->orderBy('number_of_orders', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            // Get paginated results
            $setMenus = $query->paginate($request->per_page ?? 9);

            // Get cuisines with counts
            $cuisines = Cuisine::withCount(['setMenus' => function ($query) {
                $query->where('status', true);
            }])
            ->orderBy('set_menus_count', 'desc')
            ->get()
            ->map(function ($cuisine) {
                return [
                    'name' => $cuisine->name,
                    'slug' => $cuisine->slug,
                    'number_of_orders' => $cuisine->setMenus->sum('number_of_orders'),
                    'set_menus_count' => $cuisine->set_menus_count
                ];
            });

            // Prepare filters data
            $filters = [
                'cuisines' => $cuisines,
                'sort_options' => [
                    ['value' => 'price_asc', 'label' => 'Price: Low to High'],
                    ['value' => 'price_desc', 'label' => 'Price: High to Low'],
                    ['value' => 'orders', 'label' => 'Most Popular'],
                ],
                'active_filters' => [
                    'cuisine_slug' => $request->cuisine_slug,
                    'sort_by' => $request->sort_by,
                    'guests' => $request->guests ?? 1,
                ]
            ];

            return response()->json([
                'filters' => $filters,
                'setMenus' => $setMenus,
                'meta' => [
                    'total' => $setMenus->total(),
                    'per_page' => $setMenus->perPage(),
                    'current_page' => $setMenus->currentPage(),
                    'last_page' => $setMenus->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch menus',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

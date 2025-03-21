<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // Store a new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(fn($query) => 
                    $request->parent_id ? $query->where('parent_id', $request->parent_id) 
                                        : $query->whereNull('parent_id')
                )
            ],
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id ?? null
        ]);

        return response()->json(['message' => 'Category added successfully!']);
    }

    // Fetch categories with subcategories
    public function index(Request $request)
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('categories.index', compact('categories'));
    }

    public function list(Request $request)
    {
        $query = Category::with('parentCategory');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('parentCategory', function ($parentQuery) use ($search) {
                      $parentQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $categories = $query->orderBy('id','desc')->paginate(10);

        return response()->json([
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'actions' => '-',
                    'parent_name' => $category->parentCategory ? $category->parentCategory->name : 'N/A', // Handle null parent
                ];
            }),
            'recordsTotal' => $categories->total(),
            'recordsFiltered' => $categories->total(),
        ]);
    }
}

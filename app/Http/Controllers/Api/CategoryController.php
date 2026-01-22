<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Auth::user()->categories()
            ->withCount('todos')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Auth::user()->categories()->create([
            'name' => $request->name,
            'color' => $request->color ?? '#6366f1',
        ]);

        return (new CategoryResource($category))
            ->additional(['message' => 'Categoria criada com sucesso!'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category)
    {
        $this->authorize('view', $category);

        $category->loadCount('todos');

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $category->update([
            'name' => $request->name,
            'color' => $request->color ?? $category->color,
        ]);

        $category->loadCount('todos');

        return (new CategoryResource($category))
            ->additional(['message' => 'Categoria atualizada com sucesso!']);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Categoria excluída com sucesso!',
        ]);
    }
}

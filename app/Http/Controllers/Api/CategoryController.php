<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::query()
            ->orderBy('name')
            ->paginate(20);
    }

    public function store(Request $request, AuditLogService $auditLog)
    {
        if ($request->has('code')) {
            $request->merge(['code' => Str::upper(trim($request->input('code')))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:categories,name'],
            'code' => ['nullable', 'string', 'max:50', 'unique:categories,code'],
        ]);

        $data['name'] = trim($data['name']);
        $data['code'] = isset($data['code']) ? Str::upper(trim($data['code'])) : null;
        $category = Category::create($data);

        $auditLog->log('category.create', $category, $request->user(), $request, null, $category->only(['name', 'code']));

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function update(Request $request, Category $category, AuditLogService $auditLog)
    {
        if ($request->has('code')) {
            $request->merge(['code' => Str::upper(trim($request->input('code')))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($category->id)],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('categories', 'code')->ignore($category->id),
            ],
        ]);

        $before = $category->only(['name', 'code']);
        $data['name'] = trim($data['name']);
        $data['code'] = isset($data['code']) ? Str::upper(trim($data['code'])) : null;
        $category->update($data);

        $auditLog->log('category.update', $category, $request->user(), $request, $before, $category->only(['name', 'code']));

        return $category;
    }

    public function destroy(Category $category, AuditLogService $auditLog, Request $request)
    {
        $before = $category->only(['name', 'code']);
        $category->delete();

        $auditLog->log('category.delete', $category, $request->user(), $request, $before, null);

        return response()->noContent();
    }
}

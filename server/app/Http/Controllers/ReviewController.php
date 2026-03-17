<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return $this->apiResponse(function () {
            return Review::query()
                ->with(['user:id,name'])
                ->orderByDesc('id')
                ->get();
        });
    }

    public function store(StoreReviewRequest $request)
    {
        return $this->apiResponse(function () use ($request) {
            $validated = $request->validated();
            $validated['userid'] = $request->user()->id;
            return Review::create($validated);
        });
    }

    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            return Review::with(['user:id,name'])->findOrFail($id);
        });
    }

    public function update(UpdateReviewRequest $request, int $id)
    {
        return $this->apiResponse(function () use ($request, $id) {
            $row = Review::findOrFail($id);
            $this->authorize('update', $row);
            $row->update($request->validated());
            return $row;
        });
    }

    public function destroy(Request $request, int $id)
    {
        return $this->apiResponse(function () use ($request, $id) {
            $row = Review::findOrFail($id);
            $this->authorize('delete', $row);
            $row->delete();
            return ['id' => $id];
        });
    }
}

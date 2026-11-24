<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexCourierRequest;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CourierController extends Controller
{
    public function index(IndexCourierRequest $request): AnonymousResourceCollection
    {
        $couriers = Courier::query()
            ->search($request->validated('search'))
            ->withLevels($request->levels())
            ->sortBy(
                $request->validated('sort') ?? 'name',
                $request->validated('direction') ?? 'asc',
            )
            ->paginate($request->perPage())
            ->withQueryString();

        return CourierResource::collection($couriers);
    }

    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());

        return (new CourierResource($courier->refresh()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier->refresh());
    }

    public function destroy(Courier $courier): Response
    {
        $courier->delete();

        return response()->noContent();
    }
}

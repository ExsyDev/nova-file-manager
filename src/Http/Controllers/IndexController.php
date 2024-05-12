<?php

declare(strict_types=1);

namespace Oneduo\NovaFileManager\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Oneduo\NovaFileManager\Http\Requests\IndexRequest;

class IndexController extends Controller
{
    /**
     * Get the data for the tool
     *
     * @param  \Oneduo\NovaFileManager\Http\Requests\IndexRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(IndexRequest $request)
    {
        $manager = $request->manager();

        $files = $manager->files();

        $sortOption = $request->input('sort', 'name');

        $files = match ($sortOption) {
            'extension' => $files->sortBy(function ($file) {
                return pathinfo($file, PATHINFO_EXTENSION);
            }),
            'size' => $files->sortBy(function ($file) use ($manager) {
                return Storage::size($manager->getDisk() . '/' . $file);
            }),
            default => $files->sortBy(function ($file) {
                return $file;
            }),
        };

        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $manager
            ->paginate($files)
            ->onEachSide(1);

        return response()->json([
            'disk' => $manager->getDisk(),
            'breadcrumbs' => $manager->breadcrumbs(),
            'folders' => $manager->directories(),
            'files' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'links' => $paginator->linkCollection()->toArray(),
            ],
        ]);
    }
}

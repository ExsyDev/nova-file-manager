<?php

declare(strict_types=1);

namespace BBSLab\NovaFileManager\Http\Controllers;

use BBSLab\NovaFileManager\NovaFileManager;
use Carbon\CarbonInterval;
use Closure;
use Composer\InstalledVersions;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class ToolController extends Controller
{
    public function __invoke(NovaRequest $request): Response
    {
        /** @var ?\BBSLab\NovaFileManager\NovaFileManager $tool */
        $tool = collect(Nova::registeredTools())->first(fn(Tool $tool) => $tool instanceof NovaFileManager);

        return Inertia::render('NovaFileManager', [
            'config' => array_merge(
                [
                    'upload' => config('nova-file-manager.upload'),
                    'outdated' => $this->updateChecker(),
                    'tour' => config('nova-file-manager.tour.enabled')
                ],
                $tool?->options(),
            ),
        ]);
    }

    public function updateChecker(): Closure
    {
        return function () {
            if (!config('nova-file-manager.update_checker.enabled')) {
                return false;
            }

            return Cache::remember(
                key: 'nova-file-manager.update_checker',
                ttl: (int) CarbonInterval::days(config('nova-file-manager.update_checker.ttl_in_days'))->totalSeconds,
                callback: function () {
                    $current = InstalledVersions::getPrettyVersion('bbs-lab/nova-file-manager');
                    $latest = Http::get('https://api.github.com/repos/bbs-lab/nova-file-manager/releases/latest')->json('tag_name');

                    return version_compare($current, $latest, '<');
                });
        };
    }
}

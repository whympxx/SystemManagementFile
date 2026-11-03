<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');
        Paginator::useTailwind();

        // Data sidebar: kategori, folder, dan jumlah dokumen yang segera kedaluwarsa
        View::composer('components.layouts.app', function ($view) {
            $user = auth()->user();
            $view->with([
                'sidebarCategories' => $user->categories()->withCount('documents')->orderBy('name')->get(),
                'sidebarFolders' => $user->folders()->withCount('documents')->orderBy('name')->get(),
                'sidebarExpiring' => $user->documents()->whereNotNull('expires_at')
                    ->whereDate('expires_at', '<=', now()->addDays(30))->count(),
            ]);
        });
    }
}

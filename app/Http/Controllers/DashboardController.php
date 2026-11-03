<?php

namespace App\Http\Controllers;

use App\Services\AiClassifier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $docs = $user->documents();

        return view('dashboard', [
            'total' => (clone $docs)->count(),
            'totalSize' => (int) (clone $docs)->sum('size'),
            'thisMonth' => (clone $docs)->where('created_at', '>=', now()->startOfMonth())->count(),
            'starred' => (clone $docs)->where('is_starred', true)->count(),
            'categories' => $user->categories()->withCount('documents')->orderByDesc('documents_count')->get(),
            'recent' => (clone $docs)->with('category')->latest()->take(8)->get(),
            'expiring' => (clone $docs)->with('category')->whereNotNull('expires_at')
                ->whereDate('expires_at', '<=', now()->addDays(30))->orderBy('expires_at')->take(6)->get(),
            'quotaBytes' => config('archive.quota_mb') * 1024 * 1024,
            'aiEnabled' => AiClassifier::enabled(),
        ]);
    }
}

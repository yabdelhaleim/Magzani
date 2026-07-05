<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPostingFailure;
use App\Services\Accounting\PostingFailureRetryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostingFailureController extends Controller
{
    public function __construct(
        private PostingFailureRetryService $retryService,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('accounting.posting-failures.read');

        $query = AccountingPostingFailure::query()->latest('failed_at');

        if ($request->get('status') === 'resolved') {
            $query->where('resolved', true);
        } elseif ($request->get('status') !== 'all') {
            $query->where('resolved', false);
        }

        $failures = $query->paginate(25);

        $pendingCount  = AccountingPostingFailure::where('resolved', false)->count();
        $resolvedCount = AccountingPostingFailure::where('resolved', true)->count();

        return view('accounting.posting-failures.index', compact('failures', 'pendingCount', 'resolvedCount'));
    }

    public function retry(AccountingPostingFailure $failure): RedirectResponse
    {
        Gate::authorize('accounting.posting-failures.manage');

        $result = $this->retryService->retry($failure);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function resolve(AccountingPostingFailure $failure): RedirectResponse
    {
        Gate::authorize('accounting.posting-failures.manage');

        $failure->update([
            'resolved'    => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'تم وضع علامة محلول.');
    }
}

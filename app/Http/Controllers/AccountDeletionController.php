<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountDeletionRequest;
use App\Jobs\ProcessDeletionRequest;
use App\Models\DeletionRequest;
use Inertia\Inertia;

class AccountDeletionController extends Controller
{
    public function index()
    {
        return Inertia::render('AccountDeletion');
    }

    public function store(AccountDeletionRequest $request)
    {
        $existing = DeletionRequest::where('email', $request->email)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($existing) {
            return back()->withErrors([
                'email' => 'A deletion request for this email is already being processed.',
            ]);
        }

        $deletionRequest = DeletionRequest::create([
            'email' => $request->email,
            'reason' => $request->reason,
            'user_id' => optional(auth()->user())->id,
        ]);

        ProcessDeletionRequest::dispatch($deletionRequest);

        return redirect()
            ->route('account-deletion')
            ->with('success', 'Your deletion request has been submitted. You will receive a confirmation shortly.');
    }
}

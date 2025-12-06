<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use App\Models\Borrower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupportMessageController extends Controller
{
    /**
     * Display a listing of support messages
     */
    public function index(Request $request)
    {
        $query = SupportMessage::with(['borrower', 'respondedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by borrower
        if ($request->filled('borrower_id')) {
            $query->where('borrower_id', $request->borrower_id);
        }

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('borrower', function($b) use ($search) {
                      $b->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $messages = $query->orderBy('created_at', 'desc')
                          ->paginate(20);

        // Statistics
        $stats = [
            'total' => SupportMessage::count(),
            'pending' => SupportMessage::where('status', 'pending')->count(),
            'in_progress' => SupportMessage::where('status', 'in_progress')->count(),
            'resolved' => SupportMessage::where('status', 'resolved')->count(),
            'closed' => SupportMessage::where('status', 'closed')->count(),
        ];

        // Get borrowers for filter dropdown
        $borrowers = Borrower::orderBy('full_name')->get();

        $status = $request->query('status', 'all');

        return view('support-messages.index', compact('messages', 'stats', 'borrowers', 'status'));
    }

    /**
     * Show a specific support message
     */
    public function show(SupportMessage $supportMessage)
    {
        $supportMessage->load(['borrower', 'respondedBy']);
        return view('support-messages.show', compact('supportMessage'));
    }

    /**
     * Update support message status and add response
     */
    public function update(Request $request, SupportMessage $supportMessage)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,resolved,closed'],
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::beginTransaction();
        try {
            $hadResponse = !empty($supportMessage->admin_response);
            $newResponse = !empty($validated['admin_response']);
            $responseChanged = $newResponse && ($validated['admin_response'] !== $supportMessage->admin_response);

            $supportMessage->update([
                'status' => $validated['status'],
                'admin_response' => $validated['admin_response'] ?? $supportMessage->admin_response,
                'responded_by_user_id' => $newResponse ? Auth::id() : $supportMessage->responded_by_user_id,
                'responded_at' => $newResponse ? now() : $supportMessage->responded_at,
            ]);

            // Create notification for borrower if a new response was added or updated
            if ($responseChanged) {
                \App\Models\Notification::createForBorrower(
                    $supportMessage->borrower_id,
                    \App\Models\Notification::TYPE_INFO,
                    'Support Response',
                    "We have responded to your support message: {$supportMessage->subject}\n\nResponse: {$validated['admin_response']}",
                    null
                );
                
                // Emit Socket.io event for real-time update (if Socket.io is available)
                // This will be handled by the Node.js backend when it receives the update
            }

            DB::commit();

            return back()->with('success', 'Support message updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Failed to update support message: ' . $e->getMessage());
        }
    }
}


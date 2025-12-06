<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Borrower;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $query = Notification::with(['borrower', 'loan']);

        // Filter by borrower
        if ($request->filled('borrower_id')) {
            $query->where('borrower_id', $request->borrower_id);
        }

        // Filter by loan
        if ($request->filled('loan_id')) {
            $query->where('loan_id', $request->loan_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('borrower', function($b) use ($search) {
                      $b->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $notifications = $query->orderBy('created_at', 'desc')
                               ->paginate(20);

        // Statistics
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::where('is_read', false)->count(),
            'read' => Notification::where('is_read', true)->count(),
        ];

        // Get borrowers for filter dropdown
        $borrowers = Borrower::orderBy('full_name')->get();

        return view('notifications.index', compact('notifications', 'stats', 'borrowers'));
    }

    /**
     * Show form to create a new notification
     */
    public function create()
    {
        $borrowers = Borrower::orderBy('full_name')->get();
        $loans = Loan::where('status', '!=', 'closed')
                     ->where('status', '!=', 'rejected')
                     ->where('status', '!=', 'cancelled')
                     ->orderBy('created_at', 'desc')
                     ->get();

        $types = [
            Notification::TYPE_INFO => 'Info',
            Notification::TYPE_REMINDER => 'Reminder',
            Notification::TYPE_APPROVAL => 'Approval',
            Notification::TYPE_PAYMENT_RECEIVED => 'Payment Received',
            Notification::TYPE_PAYMENT_DUE => 'Payment Due',
            Notification::TYPE_LOAN_STATUS_CHANGE => 'Loan Status Change',
        ];

        return view('notifications.create', compact('borrowers', 'loans', 'types'));
    }

    /**
     * Store a new notification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_id' => ['required', 'exists:borrowers,id'],
            'loan_id' => ['nullable', 'exists:loans,id'],
            'type' => ['required', 'in:' . implode(',', [
                Notification::TYPE_INFO,
                Notification::TYPE_REMINDER,
                Notification::TYPE_APPROVAL,
                Notification::TYPE_PAYMENT_RECEIVED,
                Notification::TYPE_PAYMENT_DUE,
                Notification::TYPE_LOAN_STATUS_CHANGE,
            ])],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        // Verify loan belongs to borrower if provided
        if ($validated['loan_id']) {
            $loan = Loan::findOrFail($validated['loan_id']);
            if ($loan->borrower_id != $validated['borrower_id']) {
                return back()->withErrors(['loan_id' => 'The selected loan does not belong to this borrower.'])->withInput();
            }
        }

        Notification::create([
            'borrower_id' => $validated['borrower_id'],
            'loan_id' => $validated['loan_id'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification sent successfully.');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification)
    {
        $notification->update([
            'is_read' => false,
            'read_at' => null,
        ]);

        return back()->with('success', 'Notification marked as unread.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $query = Notification::query();

        if ($request->filled('borrower_id')) {
            $query->where('borrower_id', $request->borrower_id);
        }

        $query->where('is_read', false)
              ->update([
                  'is_read' => true,
                  'read_at' => now(),
              ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }
}


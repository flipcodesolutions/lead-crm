<?php

namespace App\Http\Controllers;

use App\Mail\CallSummaryMailable;
use App\Models\Email;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Services\LeadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FollowUpController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $tab = $request->get('tab', 'today'); // 'today', 'overdue', 'upcoming', 'completed', 'all'

        $query = FollowUp::with(['lead.assignedUser', 'user']);

        if ($user->isTelecaller() || $user->isSalesperson()) {
            $query->where('user_id', $user->id);
        }

        if ($tab === 'today') {
            $query->whereDate('follow_up_date', $today)->where('status', 'Pending');
        } elseif ($tab === 'overdue') {
            $query->whereDate('follow_up_date', '<', $today)->where('status', 'Pending');
        } elseif ($tab === 'upcoming') {
            $query->whereDate('follow_up_date', '>', $today)->where('status', 'Pending');
        } elseif ($tab === 'completed') {
            $query->where('status', 'Completed');
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $followUps = $query->orderBy('follow_up_date', 'asc')->orderBy('follow_up_time', 'asc')->paginate($perPage)->withQueryString();

        // Counts for tabs
        $baseCounts = FollowUp::query();
        if ($user->isTelecaller() || $user->isSalesperson()) {
            $baseCounts->where('user_id', $user->id);
        }
        $todayCount = (clone $baseCounts)->whereDate('follow_up_date', $today)->where('status', 'Pending')->count();
        $overdueCount = (clone $baseCounts)->whereDate('follow_up_date', '<', $today)->where('status', 'Pending')->count();
        $upcomingCount = (clone $baseCounts)->whereDate('follow_up_date', '>', $today)->where('status', 'Pending')->count();
        $completedCount = (clone $baseCounts)->where('status', 'Completed')->count();
        $allCount = (clone $baseCounts)->count();

        return view('followups.index', compact(
            'followUps',
            'tab',
            'todayCount',
            'overdueCount',
            'upcomingCount',
            'completedCount',
            'allCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'type' => 'required|string|in:Call,Meeting,Email,WhatsApp,Other',
            'follow_up_date' => 'required|date',
            'follow_up_time' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);
        $this->leadService->addFollowUp($lead, $validated, Auth::id());

        return back()->with('success', 'Follow-up scheduled successfully.');
    }

    public function complete(Request $request, FollowUp $followUp)
    {
        $validated = $request->validate([
            'result' => 'required|string|max:1000',
            'next_follow_up_date' => 'nullable|date',
            'next_follow_up_time' => 'nullable|string',
            'send_email' => 'nullable|boolean',
            'recipient_email' => 'nullable|email|max:255',
            'email_subject' => 'nullable|string|max:255',
        ]);

        $nextDateFormatted = null;
        if (!empty($validated['next_follow_up_date'])) {
            $nextDateFormatted = Carbon::parse($validated['next_follow_up_date'])->format('M d, Y');
            if (!empty($validated['next_follow_up_time'])) {
                $nextDateFormatted .= ' at ' . $validated['next_follow_up_time'];
            }
        }

        $this->leadService->completeFollowUp(
            $followUp,
            $validated['result'],
            $validated['next_follow_up_date'] ?? null,
            $validated['next_follow_up_time'] ?? null
        );

        $emailSentMessage = '';
        $shouldSendEmail = $request->boolean('send_email') || $request->filled('recipient_email');

        if ($shouldSendEmail) {
            $recipient = $validated['recipient_email'] ?? $followUp->lead->email;
            if ($recipient) {
                $subject = $validated['email_subject'] ?? ("Call Summary & Discussion Notes - " . ($followUp->lead->company_name ?: $followUp->lead->name));
                try {
                    Mail::to($recipient)->send(new CallSummaryMailable(
                        $followUp->fresh(),
                        $subject,
                        $validated['result'],
                        $nextDateFormatted
                    ));

                    Email::create([
                        'lead_id' => $followUp->lead_id,
                        'user_id' => Auth::id(),
                        'to_email' => $recipient,
                        'subject' => $subject,
                        'message' => $validated['result'],
                        'sent_at' => now(),
                        'status' => 'Sent',
                    ]);

                    $emailSentMessage = " and call summary email was sent to {$recipient}";
                } catch (\Throwable $e) {
                    Log::error("Failed to send call summary email: " . $e->getMessage());
                    $emailSentMessage = ", but email delivery encountered an issue.";
                }
            }
        }

        return back()->with('success', 'Follow-up marked as Completed' . $emailSentMessage . '.');
    }

    /**
     * Send or resend call summary discussion notes via email.
     */
    public function emailSummary(Request $request, FollowUp $followUp)
    {
        $validated = $request->validate([
            'to_email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'next_action_date' => 'nullable|string|max:100',
        ]);

        $subject = ($validated['subject'] ?? null) ?: ("Call Summary & Discussion Notes - " . ($followUp->lead->company_name ?: $followUp->lead->name));
        $discussionNotes = ($validated['notes'] ?? null) ?: ($followUp->result ?: $followUp->description ?: 'Thank you for discussing your requirements with our team.');
        $nextAction = $validated['next_action_date'] ?? null;

        try {
            Mail::to($validated['to_email'])->send(new CallSummaryMailable(
                $followUp,
                $subject,
                $discussionNotes,
                $nextAction
            ));

            Email::create([
                'lead_id' => $followUp->lead_id,
                'user_id' => Auth::id(),
                'to_email' => $validated['to_email'],
                'subject' => $subject,
                'message' => $discussionNotes,
                'sent_at' => now(),
                'status' => 'Sent',
            ]);

            return back()->with('success', "Call discussion summary successfully emailed to {$validated['to_email']}.");
        } catch (\Throwable $e) {
            Log::error("Failed to email call summary: " . $e->getMessage());
            return back()->with('error', "Could not send email: " . $e->getMessage());
        }
    }

    public function destroy(FollowUp $followUp)
    {
        $followUp->delete();
        return back()->with('success', 'Follow-up removed.');
    }
}

<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\LeadNote;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadService
{
    /**
     * Create a new lead.
     */
    public function createLead(array $data, ?int $userId = null): Lead
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['lead_number'] = Lead::generateLeadNumber();
            $data['created_by'] = $userId ?? auth()->id();

            // Default stage and status if not provided
            if (empty($data['stage_id'])) {
                $defaultStage = LeadStage::orderBy('sort_order')->first();
                if ($defaultStage) {
                    $data['stage_id'] = $defaultStage->id;
                }
            }

            if (empty($data['status_id'])) {
                $defaultStatus = LeadStatus::first();
                if ($defaultStatus) {
                    $data['status_id'] = $defaultStatus->id;
                }
            }

            $lead = Lead::create($data);

            // Record assignment if assigned immediately
            if (!empty($data['assigned_to'])) {
                LeadAssignment::create([
                    'lead_id' => $lead->id,
                    'assigned_by' => $data['created_by'],
                    'assigned_to' => $data['assigned_to'],
                    'assigned_at' => now(),
                    'remarks' => 'Initial lead assignment upon creation.',
                ]);

                // Create notification
                Notification::create([
                    'user_id' => $data['assigned_to'],
                    'title' => 'New Lead Assigned',
                    'message' => "You have been assigned lead: {$lead->name} ({$lead->lead_number})",
                    'type' => 'info',
                    'link' => route('leads.show', $lead->id),
                ]);
            }

            return $lead;
        });
    }

    /**
     * Update an existing lead.
     */
    public function updateLead(Lead $lead, array $data): Lead
    {
        return DB::transaction(function () use ($lead, $data) {
            $oldAssignedTo = $lead->assigned_to;
            $lead->update($data);

            // If assignment changed, record history
            if (isset($data['assigned_to']) && $data['assigned_to'] != $oldAssignedTo && !empty($data['assigned_to'])) {
                LeadAssignment::create([
                    'lead_id' => $lead->id,
                    'assigned_by' => auth()->id(),
                    'assigned_to' => $data['assigned_to'],
                    'assigned_at' => now(),
                    'remarks' => $data['assignment_remarks'] ?? 'Lead reassigned.',
                ]);

                Notification::create([
                    'user_id' => $data['assigned_to'],
                    'title' => 'Lead Reassigned to You',
                    'message' => "Lead {$lead->name} ({$lead->lead_number}) has been assigned to you.",
                    'type' => 'info',
                    'link' => route('leads.show', $lead->id),
                ]);
            }

            return $lead;
        });
    }

    /**
     * Assign / Reassign a lead.
     */
    public function assignLead(Lead $lead, int $assignedTo, int $assignedBy, ?string $remarks = null): LeadAssignment
    {
        return DB::transaction(function () use ($lead, $assignedTo, $assignedBy, $remarks) {
            $lead->update(['assigned_to' => $assignedTo]);

            $assignment = LeadAssignment::create([
                'lead_id' => $lead->id,
                'assigned_by' => $assignedBy,
                'assigned_to' => $assignedTo,
                'assigned_at' => now(),
                'remarks' => $remarks ?? 'Lead assigned.',
            ]);

            $assignee = User::find($assignedTo);
            if ($assignee) {
                Notification::create([
                    'user_id' => $assignedTo,
                    'title' => 'Lead Assigned',
                    'message' => "Lead {$lead->name} ({$lead->lead_number}) was assigned to you.",
                    'type' => 'info',
                    'link' => route('leads.show', $lead->id),
                ]);
            }

            return $assignment;
        });
    }

    /**
     * Schedule a follow up.
     */
    public function addFollowUp(Lead $lead, array $data, int $userId): FollowUp
    {
        return DB::transaction(function () use ($lead, $data, $userId) {
            $data['lead_id'] = $lead->id;
            $data['user_id'] = $userId;
            $data['status'] = 'Pending';

            $followUp = FollowUp::create($data);

            // Update next follow-up on lead
            if (!empty($data['follow_up_date'])) {
                $time = $data['follow_up_time'] ?? '09:00:00';
                $lead->update([
                    'next_follow_up_at' => $data['follow_up_date'] . ' ' . $time,
                ]);
            }

            return $followUp;
        });
    }

    /**
     * Complete a follow up.
     */
    public function completeFollowUp(FollowUp $followUp, ?string $result = null, ?string $nextDate = null, ?string $nextTime = null): FollowUp
    {
        return DB::transaction(function () use ($followUp, $result, $nextDate, $nextTime) {
            $followUp->update([
                'status' => 'Completed',
                'result' => $result,
                'next_follow_up_date' => $nextDate,
                'next_follow_up_time' => $nextTime,
            ]);

            $lead = $followUp->lead;
            if ($lead) {
                if (!empty($nextDate)) {
                    $time = $nextTime ?? '09:00:00';
                    $lead->update(['next_follow_up_at' => $nextDate . ' ' . $time]);

                    // Automatically create the next pending follow-up
                    FollowUp::create([
                        'lead_id' => $lead->id,
                        'user_id' => auth()->id() ?? $followUp->user_id,
                        'type' => $followUp->type,
                        'follow_up_date' => $nextDate,
                        'follow_up_time' => $nextTime,
                        'subject' => 'Next Follow-up for ' . $lead->name,
                        'description' => 'Follow-up scheduled after result: ' . ($result ?? 'N/A'),
                        'status' => 'Pending',
                    ]);
                }
            }

            return $followUp;
        });
    }

    /**
     * Add activity.
     */
    public function addActivity(Lead $lead, array $data, int $userId): Activity
    {
        $data['lead_id'] = $lead->id;
        $data['user_id'] = $userId;
        $data['status'] = $data['status'] ?? 'Pending';

        return Activity::create($data);
    }

    /**
     * Add note.
     */
    public function addNote(Lead $lead, string $note, int $userId): LeadNote
    {
        return LeadNote::create([
            'lead_id' => $lead->id,
            'user_id' => $userId,
            'note' => $note,
        ]);
    }

    /**
     * Qualify a lead.
     */
    public function qualifyLead(Lead $lead, ?int $stageId = null): Lead
    {
        $qualifiedStatus = LeadStatus::where('name', 'LIKE', '%Qualified%')->first();
        $qualificationStage = LeadStage::where('name', 'LIKE', '%Qualification%')->first();

        $updateData = [];
        if ($qualifiedStatus) {
            $updateData['status_id'] = $qualifiedStatus->id;
        }
        if ($stageId) {
            $updateData['stage_id'] = $stageId;
        } elseif ($qualificationStage) {
            $updateData['stage_id'] = $qualificationStage->id;
        }

        $lead->update($updateData);
        return $lead;
    }

    /**
     * Disqualify a lead / Mark Lost.
     */
    public function markLost(Lead $lead, string $reason): Lead
    {
        $lostStatus = LeadStatus::where('name', 'LIKE', '%Lost%')->orWhere('name', 'LIKE', '%Unqualified%')->first();
        $lostStage = LeadStage::where('name', 'LIKE', '%Lost%')->first();

        $updateData = [
            'lost_reason' => $reason,
        ];
        if ($lostStatus) {
            $updateData['status_id'] = $lostStatus->id;
        }
        if ($lostStage) {
            $updateData['stage_id'] = $lostStage->id;
        }

        $lead->update($updateData);
        return $lead;
    }

    /**
     * Convert lead to Opportunity.
     */
    public function convertToOpportunity(Lead $lead, array $opportunityData): Opportunity
    {
        return DB::transaction(function () use ($lead, $opportunityData) {
            $convertedStatus = LeadStatus::where('name', 'LIKE', '%Converted%')->first();
            $opportunityStage = !empty($opportunityData['stage_id']) 
                ? $opportunityData['stage_id'] 
                : ($lead->stage_id ?? LeadStage::orderBy('sort_order')->first()?->id);

            $opp = Opportunity::create([
                'lead_id' => $lead->id,
                'opportunity_number' => Opportunity::generateOpportunityNumber(),
                'name' => $opportunityData['name'] ?? ('Opportunity: ' . $lead->name),
                'customer_name' => $opportunityData['customer_name'] ?? $lead->name,
                'company_name' => $opportunityData['company_name'] ?? $lead->company_name,
                'expected_revenue' => $opportunityData['expected_revenue'] ?? $lead->expected_value ?? 0,
                'probability' => $opportunityData['probability'] ?? 50,
                'expected_closing_date' => $opportunityData['expected_closing_date'] ?? now()->addDays(14),
                'stage_id' => $opportunityStage,
                'assigned_to' => $opportunityData['assigned_to'] ?? $lead->assigned_to ?? auth()->id(),
                'description' => $opportunityData['description'] ?? $lead->description,
                'status' => 'Open',
            ]);

            $lead->update([
                'converted_at' => now(),
                'status_id' => $convertedStatus ? $convertedStatus->id : $lead->status_id,
            ]);

            return $opp;
        });
    }
}

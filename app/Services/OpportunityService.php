<?php

namespace App\Services;

use App\Models\LeadStage;
use App\Models\Opportunity;
use Illuminate\Support\Facades\DB;

class OpportunityService
{
    /**
     * Create an opportunity.
     */
    public function createOpportunity(array $data): Opportunity
    {
        $data['opportunity_number'] = Opportunity::generateOpportunityNumber();
        if (empty($data['stage_id'])) {
            $firstStage = LeadStage::orderBy('sort_order')->first();
            if ($firstStage) {
                $data['stage_id'] = $firstStage->id;
            }
        }
        $data['status'] = $data['status'] ?? 'Open';

        return Opportunity::create($data);
    }

    /**
     * Update opportunity details.
     */
    public function updateOpportunity(Opportunity $opportunity, array $data): Opportunity
    {
        $opportunity->update($data);
        return $opportunity;
    }

    /**
     * Update opportunity stage (supports Kanban board stage movement).
     */
    public function updateStage(Opportunity $opportunity, int $stageId): Opportunity
    {
        $stage = LeadStage::find($stageId);
        $updateData = ['stage_id' => $stageId];

        if ($stage) {
            $stageName = strtolower($stage->name);
            if (str_contains($stageName, 'won')) {
                $updateData['status'] = 'Won';
                $updateData['won_at'] = now();
                $updateData['probability'] = 100;
            } elseif (str_contains($stageName, 'lost')) {
                $updateData['status'] = 'Lost';
                $updateData['lost_at'] = now();
                $updateData['probability'] = 0;
            } else {
                $updateData['status'] = 'Open';
            }
        }

        $opportunity->update($updateData);
        return $opportunity;
    }

    /**
     * Mark opportunity as Won.
     */
    public function markWon(Opportunity $opportunity): Opportunity
    {
        $wonStage = LeadStage::where('name', 'LIKE', '%Won%')->first();
        $updateData = [
            'status' => 'Won',
            'won_at' => now(),
            'probability' => 100,
        ];
        if ($wonStage) {
            $updateData['stage_id'] = $wonStage->id;
        }

        $opportunity->update($updateData);
        return $opportunity;
    }

    /**
     * Mark opportunity as Lost.
     */
    public function markLost(Opportunity $opportunity, string $reason): Opportunity
    {
        $lostStage = LeadStage::where('name', 'LIKE', '%Lost%')->first();
        $updateData = [
            'status' => 'Lost',
            'lost_at' => now(),
            'lost_reason' => $reason,
            'probability' => 0,
        ];
        if ($lostStage) {
            $updateData['stage_id'] = $lostStage->id;
        }

        $opportunity->update($updateData);
        return $opportunity;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeadStatus;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Base queries scoped by role if Telecaller or Salesperson
        $leadsQuery = Lead::query();
        $oppsQuery = Opportunity::query();
        $followUpsQuery = FollowUp::with('lead', 'user');
        $activitiesQuery = Activity::with('lead', 'user');

        if ($user->isTelecaller() || $user->isSalesperson()) {
            $leadsQuery->where('assigned_to', $user->id);
            $oppsQuery->where('assigned_to', $user->id);
            $followUpsQuery->where('user_id', $user->id);
            $activitiesQuery->where('user_id', $user->id);
        }

        // Summary KPIs
        $totalLeads = (clone $leadsQuery)->count();
        $newLeads = (clone $leadsQuery)->whereHas('stage', function ($q) {
            $q->where('name', 'LIKE', '%New%');
        })->count();

        $qualifiedLeads = (clone $leadsQuery)->whereHas('status', function ($q) {
            $q->where('name', 'LIKE', '%Qualified%');
        })->count();

        $totalOpportunities = (clone $oppsQuery)->count();
        $wonOpportunities = (clone $oppsQuery)->where('status', 'Won')->count();
        $lostOpportunities = (clone $oppsQuery)->where('status', 'Lost')->count();

        $pipelineValue = (clone $oppsQuery)->where('status', 'Open')->sum('expected_revenue');
        $wonRevenue = (clone $oppsQuery)->where('status', 'Won')->sum('expected_revenue');

        $todayFollowUpsCount = (clone $followUpsQuery)
            ->whereDate('follow_up_date', $today)
            ->where('status', 'Pending')
            ->count();

        $overdueFollowUpsCount = (clone $followUpsQuery)
            ->whereDate('follow_up_date', '<', $today)
            ->where('status', 'Pending')
            ->count();

        $conversionRate = $totalLeads > 0 ? round(($wonOpportunities / $totalLeads) * 100, 1) : 0;

        // Today's Follow-ups list
        $todaysFollowUps = (clone $followUpsQuery)
            ->whereDate('follow_up_date', $today)
            ->orderBy('follow_up_time')
            ->take(5)
            ->get();

        // Recent Activities
        $recentActivities = (clone $activitiesQuery)
            ->latest()
            ->take(6)
            ->get();

        // Recent Leads
        $recentLeads = (clone $leadsQuery)
            ->with(['source', 'status', 'stage', 'assignedUser'])
            ->latest()
            ->take(5)
            ->get();

        // Charts Data: Leads by Stage
        $stages = LeadStage::orderBy('sort_order')->get();
        $stageLabels = [];
        $stageCounts = [];
        foreach ($stages as $stg) {
            $stageLabels[] = $stg->name;
            $cnt = (clone $leadsQuery)->where('stage_id', $stg->id)->count();
            $stageCounts[] = $cnt;
        }

        // Charts Data: Leads by Source
        $sources = LeadSource::all();
        $sourceLabels = [];
        $sourceCounts = [];
        foreach ($sources as $src) {
            $count = (clone $leadsQuery)->where('source_id', $src->id)->count();
            if ($count > 0) {
                $sourceLabels[] = $src->name;
                $sourceCounts[] = $count;
            }
        }

        // Monthly Lead Trend (Last 6 Months)
        $monthlyTrendLabels = [];
        $monthlyTrendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyTrendLabels[] = $month->format('M Y');
            $cnt = (clone $leadsQuery)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthlyTrendData[] = $cnt;
        }

        // Sales Leaderboard (for Admin/Manager)
        $salesLeaderboard = [];
        if ($user->isAdmin() || $user->isManager()) {
            $salesLeaderboard = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['Sales', 'Salesperson', 'Telecaller']);
            })
            ->withCount(['leads as total_leads', 'opportunities as won_deals' => function ($q) {
                $q->where('status', 'Won');
            }])
            ->withSum(['opportunities as won_revenue' => function ($q) {
                $q->where('status', 'Won');
            }], 'expected_revenue')
            ->orderByDesc('won_deals')
            ->take(5)
            ->get();
        }

        return view('dashboard.index', compact(
            'totalLeads',
            'newLeads',
            'qualifiedLeads',
            'totalOpportunities',
            'wonOpportunities',
            'lostOpportunities',
            'pipelineValue',
            'wonRevenue',
            'todayFollowUpsCount',
            'overdueFollowUpsCount',
            'conversionRate',
            'todaysFollowUps',
            'recentActivities',
            'recentLeads',
            'stageLabels',
            'stageCounts',
            'sourceLabels',
            'sourceCounts',
            'monthlyTrendLabels',
            'monthlyTrendData',
            'salesLeaderboard'
        ));
    }
}

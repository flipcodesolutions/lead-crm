<?php

namespace App\Http\Controllers;

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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());

        // Overview metrics in date range
        $leadsCount = Lead::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();
        $oppsCount = Opportunity::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();
        $wonDeals = Opportunity::where('status', 'Won')
            ->whereBetween('won_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->count();
        $wonRevenue = Opportunity::where('status', 'Won')
            ->whereBetween('won_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('expected_revenue');

        $quotationsTotal = Quotation::whereBetween('quotation_date', [$dateFrom, $dateTo])->sum('total_amount');

        // Source Performance Data
        $sources = LeadSource::withCount(['leads' => function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        }])->get();

        // Salesperson Performance Data
        $salesUsers = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Sales', 'Salesperson', 'Telecaller', 'Manager']);
        })->withCount([
            'leads as assigned_leads' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            },
            'opportunities as total_opportunities' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            },
            'opportunities as won_deals' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'Won')
                  ->whereBetween('won_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            },
            'followUps as completed_followups' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'Completed')
                  ->whereBetween('follow_up_date', [$dateFrom, $dateTo]);
            },
        ])->withSum([
            'opportunities as won_revenue' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'Won')
                  ->whereBetween('won_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            }
        ], 'expected_revenue')->get();

        // Stage Distribution
        $stages = LeadStage::withCount(['leads' => function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        }, 'opportunities' => function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        }])->orderBy('sort_order')->get();

        return view('reports.index', compact(
            'dateFrom',
            'dateTo',
            'leadsCount',
            'oppsCount',
            'wonDeals',
            'wonRevenue',
            'quotationsTotal',
            'sources',
            'salesUsers',
            'stages'
        ));
    }

    public function exportLeads(Request $request): StreamedResponse
    {
        $fileName = 'leads_report_' . date('Y_m_d_His') . '.csv';

        $leads = Lead::with(['source', 'status', 'stage', 'assignedUser', 'creator'])->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Lead #', 'Name', 'Company', 'Email', 'Phone', 'Source', 'Status', 'Stage', 'Assigned To', 'Expected Value', 'Created At'];

        $callback = function () use ($leads, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->lead_number,
                    $lead->name,
                    $lead->company_name ?? '',
                    $lead->email ?? '',
                    $lead->phone,
                    $lead->source?->name ?? 'N/A',
                    $lead->status?->name ?? 'N/A',
                    $lead->stage?->name ?? 'N/A',
                    $lead->assignedUser?->name ?? 'Unassigned',
                    $lead->expected_value,
                    $lead->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

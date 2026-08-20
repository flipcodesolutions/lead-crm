<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\Service;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    protected QuotationService $quotationService;

    public function __construct(QuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Quotation::with(['opportunity', 'lead', 'creator']);

        if ($user->isSalesperson()) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('quotation_number', 'LIKE', "%{$s}%")
                  ->orWhere('customer_name', 'LIKE', "%{$s}%")
                  ->orWhere('customer_email', 'LIKE', "%{$s}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$s}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $quotations = $query->latest()->paginate($perPage)->withQueryString();

        return view('quotations.index', compact('quotations', 'perPage'));
    }

    public function create(Request $request)
    {
        $services = Service::where('status', 1)->get();
        $opportunity = null;
        $lead = null;

        if ($request->filled('opportunity_id')) {
            $opportunity = Opportunity::find($request->opportunity_id);
            if ($opportunity && $opportunity->lead_id) {
                $lead = $opportunity->lead;
            }
        } elseif ($request->filled('lead_id')) {
            $lead = Lead::find($request->lead_id);
        }

        return view('quotations.create', compact('services', 'opportunity', 'lead'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'lead_id' => 'nullable|exists:leads,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'nullable|in:Draft,Sent,Accepted,Rejected,Expired',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $quotation = $this->quotationService->createQuotation($validated, $items, Auth::id());

        return redirect()->route('quotations.show', $quotation->id)
            ->with('success', "Quotation #{$quotation->quotation_number} generated successfully.");
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.service', 'opportunity', 'lead', 'creator']);
        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items.service');
        $services = Service::where('status', 1)->get();
        return view('quotations.edit', compact('quotation', 'services'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'lead_id' => 'nullable|exists:leads,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'required|in:Draft,Sent,Accepted,Rejected,Expired',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $this->quotationService->updateQuotation($quotation, $validated, $items);

        return redirect()->route('quotations.show', $quotation->id)
            ->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403, 'Unauthorized to delete quotations.');
        }

        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully.');
    }

    public function downloadPdf(Quotation $quotation)
    {
        $pdf = $this->quotationService->generatePdf($quotation);
        return $pdf->download("Quotation_{$quotation->quotation_number}.pdf");
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['items.service', 'opportunity', 'lead', 'creator']);
        return view('quotations.print', compact('quotation'));
    }

    public function sendEmail(Request $request, Quotation $quotation)
    {
        $request->validate([
            'to_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $this->quotationService->sendEmail(
            $quotation,
            $request->to_email,
            $request->subject,
            $request->message
        );

        return back()->with('success', "Quotation sent via email to {$request->to_email}.");
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate([
            'status' => 'required|in:Draft,Sent,Accepted,Rejected,Expired',
        ]);

        $this->quotationService->updateStatus($quotation, $request->status);

        return back()->with('success', "Quotation status updated to {$request->status}.");
    }
}

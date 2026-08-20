<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTax;
use App\Models\TaxSlab;
use Illuminate\Http\Request;

class TaxSlabController extends Controller
{
    public function index(Request $request)
    {
        $slabs = TaxSlab::orderBy('min_income', 'asc')->get();
        return view('hr.tax_slabs.index', compact('slabs'));
    }

    public function create()
    {
        return view('hr.tax_slabs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|gt:min_income',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'fixed_tax' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);

        $validated['fixed_tax'] = $validated['fixed_tax'] ?? 0;

        TaxSlab::create($validated);

        return redirect()->route('hr.tax-slabs.index')->with('success', 'Tax slab created successfully.');
    }

    public function edit(TaxSlab $taxSlab)
    {
        return view('hr.tax_slabs.edit', compact('taxSlab'));
    }

    public function update(Request $request, TaxSlab $taxSlab)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|gt:min_income',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'fixed_tax' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
        ]);

        $validated['fixed_tax'] = $validated['fixed_tax'] ?? 0;

        $taxSlab->update($validated);

        return redirect()->route('hr.tax-slabs.index')->with('success', 'Tax slab updated successfully.');
    }

    public function destroy(TaxSlab $taxSlab)
    {
        $taxSlab->delete();
        return redirect()->route('hr.tax-slabs.index')->with('success', 'Tax slab removed.');
    }

    /**
     * Interactive Tax Calculator Tool
     */
    public function calculator(Request $request)
    {
        $annualGross = (float) $request->get('annual_income', 0);
        $deductions = (float) $request->get('deductions', 0); // e.g. 80C, Standard Deduction (₹75,000 / ₹50,000)

        $taxableIncome = max(0, $annualGross - $deductions);
        $annualTax = TaxSlab::calculateAnnualTax($taxableIncome);
        $monthlyTax = round($annualTax / 12, 2);

        $slabs = TaxSlab::where('status', 1)->orderBy('min_income', 'asc')->get();

        return view('hr.tax_slabs.calculator', compact('annualGross', 'deductions', 'taxableIncome', 'annualTax', 'monthlyTax', 'slabs'));
    }
}

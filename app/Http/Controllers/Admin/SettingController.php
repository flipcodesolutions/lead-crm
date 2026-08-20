<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => Setting::get('company_name', 'CRM Enterprises'),
            'company_email' => Setting::get('company_email', 'contact@company.com'),
            'company_phone' => Setting::get('company_phone', '+1 (555) 019-2834'),
            'company_address' => Setting::get('company_address', '123 Business Avenue, Suite 500, Tech Park'),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'tax_number' => Setting::get('tax_number', 'TAX-987654321'),
            'quotation_prefix' => Setting::get('quotation_prefix', 'QT-'),
            'lead_prefix' => Setting::get('lead_prefix', 'LD-'),
            'terms_and_conditions' => Setting::get('terms_and_conditions', "1. Validity: Quotation is valid for 30 days.\n2. Payment: 50% advance, 50% on completion.\n3. Taxes as applicable."),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $fields = [
            'company_name',
            'company_email',
            'company_phone',
            'company_address',
            'currency_symbol',
            'tax_number',
            'quotation_prefix',
            'lead_prefix',
            'terms_and_conditions',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field));
            }
        }

        return back()->with('success', 'System settings saved successfully.');
    }
}

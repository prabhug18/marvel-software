<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Show invoice template edit form
     */
    public function editInvoiceTemplate()
    {
        // Only admin
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $current = Setting::get('invoice_template', 'default');
        $currentHead = Setting::get('head_office_warehouse_id', null);
        $warehouses = \App\Models\Warehouse::all();
        $available = [
            'default' => 'Format One',
            'gst' => 'Format Two',            
            'three' => 'Format Three',
            'four' => 'Format Four',
        ];

        return view('backend.admin.settings.invoice_template', [
            'current' => $current,
            'available' => $available,
            'heading' => 'Select Invoice Template',
            'warehouses' => $warehouses,
            'currentHead' => $currentHead,
        ]);
    }

    /**
     * Update invoice template
     */
    public function updateInvoiceTemplate(Request $request)
    {
        if (!auth()->user() || !auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $data = $request->validate([
            'invoice_template' => 'required|string|max:50',
            'head_office_warehouse_id' => 'nullable|integer|exists:warehouses,id',
        ]);

        Setting::set('invoice_template', $data['invoice_template']);
        // Save head office warehouse id if provided
        Setting::set('head_office_warehouse_id', $data['head_office_warehouse_id'] ?? null);

        return redirect()->route('admin.settings.invoice_template.edit')
            ->with('success', 'Invoice template updated.');
    }
}

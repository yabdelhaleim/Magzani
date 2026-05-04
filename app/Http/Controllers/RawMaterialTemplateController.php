<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RawMaterialTemplateController extends Controller
{
    public function index()
    {
        $templates = RawMaterialTemplate::latest()->paginate(20);

        return view('manufacturing-orders.raw-materials.index', compact('templates'));
    }

    public function create()
    {
        return view('manufacturing-orders.raw-materials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'buy_price' => 'required|numeric|min:0',
        ]);

        RawMaterialTemplate::create([
            'name' => $validated['name'],
            'quantity' => $validated['quantity'],
            'sale_price' => $validated['sale_price'],
            'buy_price' => $validated['buy_price'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('manufacturing-orders.raw-materials.index')
            ->with('success', 'تم إنشاء الخامة بنجاح');
    }

    public function show(string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);

        return view('manufacturing-orders.raw-materials.show', compact('template'));
    }

    public function edit(string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);

        return view('manufacturing-orders.raw-materials.edit', compact('template'));
    }

    public function update(Request $request, string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'buy_price' => 'required|numeric|min:0',
        ]);

        $template->update([
            'name' => $validated['name'],
            'quantity' => $validated['quantity'],
            'sale_price' => $validated['sale_price'],
            'buy_price' => $validated['buy_price'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('manufacturing-orders.raw-materials.index')
            ->with('success', 'تم تحديث الخامة بنجاح');
    }

    public function destroy(string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('manufacturing-orders.raw-materials.index')
            ->with('success', 'تم حذف الخامة بنجاح');
    }
}

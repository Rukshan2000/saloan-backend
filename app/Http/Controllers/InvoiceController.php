<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceController extends Controller
{
    use SoftDeletes;

    public function index()
    {
        return Invoice::all();
    }

    public function store(Request $request)
    {
        $invoice = Invoice::create($request->all());
        return response()->json($invoice, 201);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($request->all());
        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(['message' => 'Invoice soft deleted']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    // GET /api/payments?day=YYYY-MM-DD | ?month=YYYY-MM | ?year=YYYY
    public function index(Request $request)
    {
        $userId = auth('api')->id();

        $q = Payment::where('user_id', $userId)->orderByDesc('created_at');

        if ($day = $request->query('day')) {
            $q->whereDate('created_at', $day);
        } elseif ($month = $request->query('month')) {
            $q->whereYear('created_at', substr($month, 0, 4))
              ->whereMonth('created_at', substr($month, 5, 2));
        } elseif ($year = $request->query('year')) {
            $q->whereYear('created_at', $year);
        }

        return response()->json(['success' => true, 'data' => $q->get()]);
    }

    // POST /api/payments (multipart si 'receipt')
    public function store(Request $request)
    {
        $userId = auth('api')->id();

        $v = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'category'    => 'required|in:electricity,internet,water,rent,other',
            'receipt'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:4096',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $path = $request->hasFile('receipt')
            ? $request->file('receipt')->store('receipts', 'public')
            : null;

        $status = ((float)$request->amount <= 500000)
            ? 'SUCCESS'
            : (rand(0, 1) ? 'SUCCESS' : 'FAILED');

        $payment = Payment::create([
            'user_id'      => $userId,
            'description'  => $request->description,
            'amount'       => $request->amount,
            'category'     => $request->category,
            'status'       => $status,
            'paid_at'      => $status === 'SUCCESS' ? now() : null,
            'receipt_path' => $path,
        ]);

        return response()->json(['success' => true, 'payment' => $payment->fresh()], 201);
    }

    public function show(Payment $payment)
    {
        $this->authorizeOwner($payment);
        return response()->json(['success' => true, 'data' => $payment]);
    }

    public function downloadReceipt(Payment $payment)
    {
        $this->authorizeOwner($payment);

        if (!$payment->receipt_path || !Storage::disk('public')->exists($payment->receipt_path)) {
            return response()->json(['success' => false, 'message' => 'Aucun justificatif disponible'], 404);
        }

        return response()->download(
            Storage::disk('public')->path($payment->receipt_path),
            basename($payment->receipt_path)
        );
    }

    public function dashboard()
    {
        $u = auth('api')->user();

        $recent = $u->payments()->orderByDesc('created_at')->limit(5)->get();

        $monthlyTotal = (float) $u->payments()
            ->where('status', 'SUCCESS')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $initial = 1000000.0;
        $spent   = (float) $u->payments()->where('status', 'SUCCESS')->sum('amount');
        $balance = max(0.0, $initial - $spent);

        return response()->json([
            'success' => true,
            'data' => [
                'user'            => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email],
                'balance'         => round($balance, 2),
                'recent_payments' => $recent,
                'monthly_total'   => $monthlyTotal,
            ],
        ]);
    }

    public function statistics()
    {
        $u = auth('api')->user();

        $byStatus = $u->payments()
            ->selectRaw('status, COALESCE(SUM(amount),0) total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byCategory = $u->payments()
            ->where('status','SUCCESS')
            ->selectRaw('category, COUNT(*) count, COALESCE(SUM(amount),0) total')
            ->groupBy('category')
            ->get();

        $monthlyTotal = (float) $u->payments()
            ->where('status','SUCCESS')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => [
                    'PENDING' => (float) ($byStatus['PENDING'] ?? 0),
                    'SUCCESS' => (float) ($byStatus['SUCCESS'] ?? 0),
                    'FAILED'  => (float) ($byStatus['FAILED']  ?? 0),
                ],
                'by_category'   => $byCategory,
                'monthly_total' => $monthlyTotal,
            ],
        ]);
    }

    private function authorizeOwner(Payment $payment): void
    {
        if ($payment->user_id !== auth('api')->id()) {
            abort(response()->json(['success' => false, 'message' => 'Accès refusé'], 403));
        }
    }
}

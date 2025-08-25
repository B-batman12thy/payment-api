<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function dashboard()
    {
        $user = auth()->user();

        // Paiements récents (5 derniers)
        $recentPayments = $user->payments()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Total des paiements du mois en cours
        $monthlyTotal = $user->payments()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'balance' => $user->balance,
                'recent_payments' => $recentPayments,
                'monthly_total' => $monthlyTotal,
            ]
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $payments = $user->payments()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|in:electricity,internet,water,rent,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = auth()->user();
        $amount = floatval($request->amount);

        // Vérifier le solde
        if ($user->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant'
            ], 400);
        }

        // Simulation (80% de succès)
        $status = rand(1, 10) <= 8 ? 'completed' : 'failed';

        // Créer le paiement
        $payment = Payment::create([
            'user_id' => $user->id,
            'description' => $request->description,
            'amount' => $amount,
            'status' => $status,
            'category' => $request->category,
            'processed_at' => $status === 'completed' ? now() : null,
        ]);

        // Décrémenter le solde si succès
        if ($status === 'completed') {
            $user->decrement('balance', $amount);
            $user->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => $status === 'completed' 
                ? 'Paiement effectué avec succès' 
                : 'Paiement échoué',
            'payment' => $payment,
            'new_balance' => $user->balance,
        ], 201);
    }

    public function show(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success' => true,
            'message' => 'Statistiques disponibles'
        ]);
    }

    public function downloadReceipt(Payment $payment)
    {
        return response()->json([
            'success' => true,
            'message' => 'Téléchargement de justificatif'
        ]);
    }
}
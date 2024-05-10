<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    public function mockResponse(Request $request)
    {
        $mockStatus = $request->header('x-mock-status');

        if ($mockStatus === 'success') {
            $transactionId = Str::uuid();
            return response()->json([
                'status' => 'success',
                'message' => 'Mock payment transaction successful',
                'transaction_id' => $transactionId,
                'amount' => 100.00,
                'currency' => 'USD'
            ], Response::HTTP_OK);
        } elseif ($mockStatus === 'failure') {
            return response()->json([
                'status' => 'failure',
                'message' => 'Mock payment transaction failed',
                'error_code' => 'PAYMENT_FAILED'
            ], Response::HTTP_BAD_REQUEST);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid x-mock-status header value'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function storeTransaction(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'user_id' => 'required|string',
        ]);
        $firstApiDomain = Config::get('app.api_domain');
        // Call the first API to simulate payment
        $request = Request::create('$firstApiDomain/mock-response', 'GET');
        $request->headers->add(['x-mock-status' => 'success']);
        $response = Route::dispatch($request);

        // Generate a unique transaction ID
        $transactionId = Str::uuid();

        // Store transaction data
        Transaction::create([
            'transaction_id' => $transactionId,
            'amount' => $validatedData['amount'],
            'user_id' => $validatedData['user_id'],
            'status' => $response['status'] === 'success' ? 'success' : 'failure',
            'payment_details' => $response->json(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment processed successfully',
            'transaction_id' => $transactionId,
        ])->header('Cache-Control', 'no-store');
    }

    public function updateTransaction(Request $request)
    {
        $validatedData = $request->validate([
            'transaction_id' => 'required|string',
            'status' => 'required|string',
        ]);

        $transaction = Transaction::where('transaction_id', $validatedData['transaction_id'])->first();

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $transaction->status = $validatedData['status'];
        $transaction->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated successfully'
        ],Response::HTTP_OK);
    }
}

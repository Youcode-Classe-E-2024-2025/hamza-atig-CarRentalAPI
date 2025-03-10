<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Payment::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Payment $payment)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $request->validate([
            'user_id' => 'required',
            'amount' => 'required',
            'rental_id' => 'required',
            'status' => 'required'
        ]);

        $payment = Payment::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'rental_id' => $request->rental_id,
            'status' => $request->status
        ]);

        $amountInCents = intval($request->amount * 100);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'description' => 'Payment for rental ID ' . $request->rental_id,
            ]);

            $payment = Payment::create([
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'rental_id' => $request->rental_id,
                'status' => $request->status,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            return response()->json([
                'message' => 'Payment created successfully',
                'payment' => $payment,
                'client_secret' => $paymentIntent->client_secret
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return $payment;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $payment->update($request->all());
        return $payment;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json([
            'message' => 'Payment deleted successfully',
            'payment' => $payment
        ]);
    }
}

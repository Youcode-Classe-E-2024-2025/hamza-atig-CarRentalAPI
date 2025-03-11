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
     * @OA\Get(
     *     path="/api/payments",
     *     summary="Display a listing of payments",
     *     tags={"payments"},
     *     @OA\Response(response="200", description="Display a listing of payments")
     * )
     */
    public function index()
    {
        return Payment::all();
    }

    /**
     * @OA\Post(
     *     path="/api/payments",
     *     summary="Store a newly created payment",
     *     tags={"payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "amount", "rental_id", "status"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="rental_id", type="integer"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Payment created successfully"),
     *     @OA\Response(response="500", description="Stripe API error")
     * )
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
     * @OA\Get(
     *     path="/api/payments/{id}",
     *     summary="Display the specified payment",
     *     tags={"payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Display the specified payment")
     * )
     */
    public function show(Payment $payment)
    {
        return $payment;
    }

    /**
     * @OA\Put(
     *     path="/api/payments/{id}",
     *     summary="Update the specified payment",
     *     tags={"payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="rental_id", type="integer"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Payment updated successfully")
     * )
     */
    public function update(Request $request, Payment $payment)
    {
        $payment->update($request->all());
        return $payment;
    }

    /**
     * @OA\Delete(
     *     path="/api/payments/{id}",
     *     summary="Remove the specified payment",
     *     tags={"payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Payment deleted successfully")
     * )
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


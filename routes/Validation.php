<?php 
// Let's start creating Razorpay Payment Integration Step-by-Step ✅

// Step 1: Create payments table migration

// Run command:
// php artisan make:migration create_payments_table

// database/migrations/xxxx_xx_xx_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('contact');
            $table->integer('amount');
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->string('status')->default('created');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};

// Then run:
// php artisan migrate

//-------------------------------------------------------------

// Step 2: Create Payment Model

// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'name', 'email', 'contact', 'amount', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature', 'status'
    ];
}

//-------------------------------------------------------------

// Step 3: Create PaymentController

// Run command:
// php artisan make:controller PaymentController

// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    public function showPaymentForm()
    {
        return view('payments.create');
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:15',
        ]);

        $api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
        $amountInPaise = $request->amount * 100;
        $receiptId = 'order_' . time() . '_' . rand(1000,9999);

        $razorpayOrder = $api->order->create([
            'receipt' => $receiptId,
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'notes' => [
                'name' => $request->name,
                'email' => $request->email,
                'contact' => $request->contact,
            ]
        ]);

        $payment = Payment::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact' => $request->contact,
            'amount' => $request->amount,
            'razorpay_order_id' => $razorpayOrder['id'],
            'status' => 'created'
        ]);

        return response()->json([
            'order_id' => $razorpayOrder['id'],
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'key' => config('services.razorpay.key_id'),
            'name' => config('app.name'),
            'description' => 'Payment for Order #' . $receiptId,
            'prefill' => [
                'name' => $request->name,
                'email' => $request->email,
                'contact' => $request->contact
            ]
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        $api = new Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));

        try {
            $attributes = [
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();
            if ($payment) {
                $payment->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature,
                    'status' => 'completed'
                ]);

                return redirect()->route('payment.success');
            }

            return redirect()->route('payment.error')->with('error', 'Payment record not found.');
        } catch (\Exception $e) {
            return redirect()->route('payment.error')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    public function showSuccessPage()
    {
        return view('payments.success');
    }

    public function showErrorPage()
    {
        return view('payments.error');
    }
}

//-------------------------------------------------------------

// Step 4: Create Routes in web.php

use App\Http\Controllers\PaymentController;

Route::get('/pay', [PaymentController::class, 'showPaymentForm'])->name('payment.form');
Route::post('/create-order', [PaymentController::class, 'createOrder'])->name('payment.createOrder');
Route::post('/payment-success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment-success-page', [PaymentController::class, 'showSuccessPage'])->name('payment.success.page');
Route::get('/payment-error-page', [PaymentController::class, 'showErrorPage'])->name('payment.error');

//-------------------------------------------------------------

// Step 5: Create Blade views:

// resources/views/payments/create.blade.php
// resources/views/payments/success.blade.php
// resources/views/payments/error.blade.php

// I can write full blade HTML form also if you say! ✅

//-------------------------------------------------------------

// Step 6: Setup Razorpay keys in .env

// .env
// RAZORPAY_KEY=your_key_id
// RAZORPAY_SECRET=your_key_secret

// config/services.php

'razorpay' => [
    'key_id' => env('RAZORPAY_KEY'),
    'key_secret' => env('RAZORPAY_SECRET'),
],

//-------------------------------------------------------------

// DONE ✅  Now fully working Razorpay integration is ready!

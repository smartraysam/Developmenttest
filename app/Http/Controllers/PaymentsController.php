<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Paystack;
use App\Models\Payments;
use Carbon\Carbon;
use KingFlamez\Rave\Facades\Rave as Flutterwave;

class PaymentsController extends Controller
{

    public function paynow()
    {

        $split = [
            "type" => "percentage",
            "currency" => "NGN",
            "subaccounts" => [
                ["subaccount" => "ACCT_g2obdni3dl6hem6", "share" => 10],
            ],
            "bearer_type" => "all",
            "main_account_share" => 90,
        ];
        return view('payment', compact('split'));
    }

    public function redirectToGateway(Request $request)
    {

        $customer_email = $request->email;

        $amount = $request->amount; //converting to kobo - paystack rule
        $package = "basic";
        $reference = Paystack::genTranxRef();
        $kobo = ($amount) * 100; //add the user inputted amount and the outstanding fees
        $metadata = ['customer_id' => 1, 'client_id' => 12, 'package' => $package]; //metadata for the data i need
        $request->request->add(['reference' => $reference, 'email' => $customer_email, 'amount' => $kobo, 'currency' => 'NGN', 'channels' => ['card', 'bank_transfer'], 'metadata' => $metadata, 'callback_url' => env('APP_URL') . 'payment/callback']);
        try { //to ensure the page return back to the user when the session has expired
            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Exception $e) {
            // dd(($e));
            \Log::info($e);
            return response()->json(["status" => "error", "msg" =>$e]);

        }
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();
        \Log::info($paymentDetails);

        if($paymentDetails['status'] ==true){
            $newPayment = new Payments();
            $newPayment->reference = $paymentDetails['data']['reference'];
            $newPayment->amount = ($paymentDetails['data']['amount'])/100;
            $newPayment->customerID = $paymentDetails['data']['metadata']['customer_id'];
            $newPayment->status = $paymentDetails['data']['status'];
            $newPayment->transactionDate = Carbon::now()->toDateString();
            $newPayment->paymentGateway = "PayStack";
            $newPayment->save();

            // dd($paymentDetails['data']['metadata']['customer_id']);

        } else{
            $newPayment = new Payments();
            $newPayment->reference = $paymentDetails['data']['reference'];
            $newPayment->amount = ($paymentDetails['data']['amount'])/100;
            $newPayment->customerID = $paymentDetails['data']['metadata']['customer_id'];
            $newPayment->status = $paymentDetails['data']['status'];
            $newPayment->transactionDate = Carbon::now()->toDateString();
            $newPayment->paymentGateway = "PayStack";
            $newPayment->save();

            // dd($paymentDetails['data']['metadata']['customer_id']);
        }

        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }

    public function initialize()
    {
        //This generates a payment reference
        $reference = Flutterwave::generateReference();
        // dd(request()->phone);
        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => 500,
            'email' => request()->email,
            'tx_ref' => $reference,
            'currency' => "NGN",
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => request()->email,
                "phone_number" => request()->phone,
                "name" => request()->name
            ],

            "customizations" => [
                "title" => 'Movie Ticket',
                "description" => "20th October"
            ]
        ];

        $payment = Flutterwave::initializePayment($data);
        // dd($payment);

        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return;
        }

        return redirect($payment['data']['link']);
    }

    /**
     * Obtain Rave callback information
     * @return void
     */
    public function callback()
    {

        $status = request()->status;

        //if payment is successful
        if ($status ==  'successful') {

        $transactionID = Flutterwave::getTransactionIDFromCallback();
        $data = Flutterwave::verifyTransaction($transactionID);

        \Log::info($data);
        // dd( $data['data']['tx_ref']);

        //database
        $newPayment = new Payments();
        $newPayment->reference = $data['data']['tx_ref'];
        $newPayment->amount = ($data['data']['amount']);
        $newPayment->customerID = $data['data']['customer']['id'];
        $newPayment->status = $data['status'];
        $newPayment->transactionDate = Carbon::now()->toDateString();
        $newPayment->paymentGateway = "Flutterwave";
        $newPayment->save();
        }
        elseif ($status ==  'cancelled'){
            //Put desired action/code after transaction has been cancelled here
            //posible go back to payment page
            return redirect('/payment');
        }
        else{
            //Put desired action/code after transaction has failed here
            \Log::info($data);
        // dd( $data['data']['tx_ref']);

        //database
        $newPayment = new Payments();
        $newPayment->reference = $data['data']['tx_ref'];
        $newPayment->amount = ($data['data']['amount']);
        $newPayment->customerID = $data['data']['customer']['id'];
        $newPayment->status = $data['status'];
        $newPayment->transactionDate = Carbon::now()->toDateString();
        $newPayment->paymentGateway = "Flutterwave";
        $newPayment->save();
        }
        // Get the transaction from your DB using the transaction reference (txref)
        // Check if you have previously given value for the transaction. If you have, redirect to your successpage else, continue
        // Confirm that the currency on your db transaction is equal to the returned currency
        // Confirm that the db transaction amount is equal to the returned amount
        // Update the db transaction record (including parameters that didn't exist before the transaction is completed. for audit purpose)
        // Give value for the transaction
        // Update the transaction to note that you have given value for the transaction
        // You can also redirect to your success page from here

    }


}

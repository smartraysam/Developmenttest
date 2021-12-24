<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Paystack;
use App\Models\Payments;
use Carbon\Carbon;

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
            \Log::info($e);
            return \response()->json(["status" => "error", "msg" => "Error occur while access payment gateway, please try again!!!"]);

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
            $newPayment->status = $paymentDetails['status'];
            $newPayment->transactionDate = Carbon::now()->toDateString();
            $newPayment->paymentGateway = "PayStack";
            $newPayment->save();

            // dd($paymentDetails['data']['metadata']['customer_id']);

        } else{
            $newPayment = new Payments();
            $newPayment->reference = $paymentDetails['data']['reference'];
            $newPayment->amount = ($paymentDetails['data']['amount'])/100;
            $newPayment->customerID = $paymentDetails['data']['metadata']['customer_id'];
            $newPayment->status = $paymentDetails['status'];
            $newPayment->transactionDate = Carbon::now()->toDateString();
            $newPayment->paymentGateway = "PayStack";
            $newPayment->save();

            // dd($paymentDetails['data']['metadata']['customer_id']);
        }

        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }
}

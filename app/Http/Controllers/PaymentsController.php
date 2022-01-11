<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Paystack;
use App\Models\Payments;
use Carbon\Carbon;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use DataTables;
use Khill\Lavacharts\Lavacharts;

class PaymentsController extends Controller{
    private function getDataTable()
    {
        $combinedChart = \Lava::DataTable();
        $combinedChart->addDateColumn('Year')
                      ->addNumberColumn('Paystack')
                      ->addNumberColumn('Flutterwave')
                      ->addNumberColumn('Total');

        return $combinedChart;
    }

    public function updateData(){
        $combinedChart = $this->getDataTable();

        $dateArray = Payments::distinct()->where('status', 'success')->get(['transactionDate']);

        foreach ($dateArray as $date){
            $amountX = 0.00;
            $paystackX = 0.00;
            $flutterwaveX = 0.00;

            $amountArray = Payments::where('transactionDate', $date->transactionDate)->get(['amount']);
            $paystackArray = Payments::where('transactionDate', $date->transactionDate)->where('paymentGateway', 'paystack')->get(['amount']);
            $flutterwaveArray = Payments::where('transactionDate', $date->transactionDate)->where('paymentGateway', 'flutterwave')->get(['amount']);

            //Total amount
            foreach ($amountArray as $amount) {
                $amountX += $amount->amount;
             }

            //Paystack amount
            foreach ($paystackArray as $paystack) {
            $paystackX += $paystack->amount;
            }

            //flutterwave amount
            foreach ($flutterwaveArray as $flutterwave) {
            $flutterwaveX += $flutterwave->amount;
            }

            $combinedChart->addRow([$date->transactionDate, $paystackX, $flutterwaveX, $amountX]);
        }
        return $combinedChart->toJson();
    }

    public function getData(){

        $combinedChart = $this->getDataTable();

        $dateArray = Payments::distinct()->where('status', 'success')->get(['transactionDate']);

        foreach ($dateArray as $date){
            $amountX = 0.00;
            $paystackX = 0.00;
            $flutterwaveX = 0.00;

            $amountArray = Payments::where('transactionDate', $date->transactionDate)->get(['amount']);
            $paystackArray = Payments::where('transactionDate', $date->transactionDate)->where('paymentGateway', 'paystack')->get(['amount']);
            $flutterwaveArray = Payments::where('transactionDate', $date->transactionDate)->where('paymentGateway', 'flutterwave')->get(['amount']);

            //Total amount
            foreach ($amountArray as $amount) {
                $amountX += $amount->amount;
             }

            //Paystack amount
            foreach ($paystackArray as $paystack) {
            $paystackX += $paystack->amount;
            }

            //flutterwave amount
            foreach ($flutterwaveArray as $flutterwave) {
            $flutterwaveX += $flutterwave->amount;
            }

            $combinedChart->addRow([$date->transactionDate, $paystackX, $flutterwaveX, $amountX]);
        }

        return $combinedChart;
    }

    public function getChart(){
        //Paystack & Flutterwave Payments Breakdown
        $combinedChart = $this->getData();
        // dd($combinedChart);
        \Lava::ColumnChart('CombinedChart', $combinedChart, [
            'title' => 'Payments Breakdown',
            'legend' => ['position' => 'out'],
            'chartArea' => ['width' => '70%'],
            'hAxis' => ['title' => 'Day', 'format' => 'MMM dd'],
            'vAxis' => ['title' => 'Amount(₦)', 'scaleType'=> 'log'] //VerticalAxis Options
        ]);

        return view('welcome');
    }


    public function paynow()
    {

        // $split = [
        //     "type" => "percentage",
        //     "currency" => "NGN",
        //     "subaccounts" => [
        //         ["subaccount" => "ACCT_g2obdni3dl6hem6", "share" => 10],
        //     ],
        //     "bearer_type" => "all",
        //     "main_account_share" => 90,
        // ];
        // return view('payment', compact('split'));
        return view('payment');
    }

    //DataTable
    public function getPayments(Request $request)
    {
        if ($request->ajax()) {
            $data = Payments::latest()->get();
            return Datatables::of($data)->addIndexColumn()->make(true);
        }
    }

    //Paystacck
    public function redirectToGateway(Request $request)
    {
        $customer_email = $request->email;
        $amount = $request->amount; //converting to kobo - paystack rule
        $package = "basic";
        $reference = Paystack::genTranxRef();
        $kobo = ($amount) * 100; //add the user inputted amount and the outstanding fees
        $metadata = ['customer_id' =>  mt_rand(1000000,9999999), 'client_id' => 12, 'package' => $package]; //metadata for the data i need
        $request->request->add(['reference' => $reference, 'email' => $customer_email, 'amount' => $kobo, 'currency' => 'NGN', 'channels' => ['card', 'bank_transfer'], 'metadata' => $metadata, 'callback_url' => env('APP_URL') . 'payment/callback']);
        try { //to ensure the page return back to the user when the session has expired
            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Exception $e) {
            \Log::info($e);
            return response()->json(["status" => "error", "msg" => "Error occur while access payment gateway, please try again!!!"]);

        }
    }

    //Obtain Paystack payment information
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
            return redirect('/payment');
        }

        else{
            $newPayment = new Payments();
            $newPayment->reference = $paymentDetails['data']['reference'];
            $newPayment->amount = ($paymentDetails['data']['amount'])/100;
            $newPayment->customerID = $paymentDetails['data']['metadata']['customer_id'];
            $newPayment->status = $paymentDetails['data']['status'];
            $newPayment->transactionDate = Carbon::now()->toDateString();
            $newPayment->paymentGateway = "PayStack";
            $newPayment->save();
            return redirect('/payment');
        }
    }


    //Flutterwave
    public function initialize()
    {
        //This generates a payment reference
        $reference = Flutterwave::generateReference();
        // dd(request()->phone);
        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => request()->amount,
            'email' => request()->email,
            'tx_ref' => $reference,
            'currency' => "NGN",
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => request()->email,
                "phone_number" => request()->phone,
                "name" => request()->name
            ],

            "meta" => [
                "id" => mt_rand(1000000,9999999)
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

    // Obtain Rave callback information
    public function callback()
    {
        $status = request()->status;

        //if payment is successful
        if ($status ==  'successful') {

        $transactionID = Flutterwave::getTransactionIDFromCallback();
        $data = Flutterwave::verifyTransaction($transactionID);

        \Log::info($data);

        //database
        $newPayment = new Payments();
        $newPayment->reference = $data['data']['tx_ref'];
        $newPayment->amount = ($data['data']['amount']);
        $newPayment->customerID = $data['data']['meta']['id'];
        $newPayment->status = $data['status'];
        $newPayment->transactionDate = Carbon::now()->toDateString();
        $newPayment->paymentGateway = "Flutterwave";
        $newPayment->save();
        return redirect('/payment');
        }
        elseif ($status ==  'cancelled'){
            //Put desired action/code after transaction has been cancelled here
            //posible go back to payment page
            return redirect('/payment');
        }
        else{
            //Put desired action/code after transaction has failed here
            \Log::info($data);


        //database
        $newPayment = new Payments();
        $newPayment->reference = $data['data']['tx_ref'];
        $newPayment->amount = ($data['data']['amount']);
        $newPayment->customerID = $data['data']['meta']['id'];
        $newPayment->status = $data['status'];
        $newPayment->transactionDate = Carbon::now()->toDateString();
        $newPayment->paymentGateway = "Flutterwave";
        $newPayment->save();
        return redirect('/payment');
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

       // filter data with date range
    //this function is called in your api

    public function RevenueTransactions(Request $request)
    {
        try {
            $from = $request->start_date; // date view
            $to = $request->end_date; // date from view

            if(empty($from) || empty($to)){
                return response()->json([
                    "status" => "info",
                    "data" => [],
                    "message"=>"start_date or end_date parameter not set",
                   ]);
            }
            $transactions = array();
            $today = date("Y-m-d");
            $yesterday = Carbon::yesterday()->format("Y-m-d");

            if (($from == $today && $to == $today) ||($from == $yesterday && $to == $yesterday)) {
                $transactions = Payments::where('status', '=', "success")
                ->whereDate('created_at', $from)  // there is date filter for a single date
                ->orderBy('id', 'desc')
                ->get();
            } else {
                $start = Carbon::parse($from);
                $end = Carbon::parse($to)->addDay();
                $this_month = [$start, $end];
                $transactions = Payments::where('status', '=', "success")
                ->whereBetween('created_at', $this_month) // date filter  for date range
                   ->orderBy('id', 'desc')
                   ->get();
            }

            return response()->json([
                "status" => "ok",
                "data" => $transactions
               ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "data" => [],
                "message"=>"Error occur",
               ]);
        }

    }


    // the function populate your datatable directly
    public function Transactions(Request $request)
    {

            $from = $request->start_date; // date view
            $to = $request->end_date; // date from view
            $transactions = array();
            $today = date("Y-m-d");
            $yesterday = Carbon::yesterday()->format("Y-m-d");

            if (($from == $today && $to == $today) ||($from == $yesterday && $to == $yesterday)) {
                $transactions = Payments::where('status', '=', "success")
                ->whereDate('created_at', $from)  // there is date filter for a single date
                   ->orderBy('id', 'desc')->get();
            } else {
                $start = Carbon::parse($from);
                $end = Carbon::parse($to)->addDay();
                $this_month = [$start, $end];
                $transactions = Payments::where('status', '=', "success")
                ->whereBetween('created_at', $this_month) // date filter  for date range
                ->orderBy('id', 'desc')
                ->get();
            }


            if (request()->ajax()) {
                return DataTables::of($transactions)  // return data to datatable
                ->addIndexColumn()
                ->make(true);
            }
    }
}

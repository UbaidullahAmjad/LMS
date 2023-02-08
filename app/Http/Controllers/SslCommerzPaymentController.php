<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use Auth;
use Crypt;
use Session;
use Redirect;
use App\Cart;
use App\Wishlist;
use App\Order;
use App\Currency;
use Mail;
use App\Mail\SendOrderMail;
use App\Notifications\UserEnroll;
use App\Course;
use App\User;
use Notification;
use Carbon\Carbon;
use App\InstructorSetting;
use App\PendingPayout;
use App\Mail\AdminMailOnOrder;
use TwilioMsg;
use App\Setting;

class SslCommerzPaymentController extends Controller
{

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    public function index(Request $request)
    {

        $fname = Auth::user()->fname;
        $address = Auth::user()->address;
        $email = Auth::user()->address;
        $city = isset(Auth::user()->city['name']);
        $state = isset(Auth::user()->state['name']);
        $country = isset(Auth::user()->country['name']);
        $mobile = Auth::user()->mobile;
        $pin_code = Auth::user()->pin_code;

        # Here you have to receive all the order data to initate the payment.
        # Let's say, your oder transaction informations are saving in a table called "orders"
        # In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = $amount = round(Crypt::decrypt($request->amount), 2); # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $fname;
        $post_data['cus_email'] = $email;
        $post_data['cus_add1'] = $address;
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = $city;
        $post_data['cus_state'] = $state;
        $post_data['cus_postcode'] = $pin_code;
        $post_data['cus_country'] = $country;
        $post_data['cus_phone'] = $mobile;
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = $fname;
        $post_data['ship_add1'] = $address;
        $post_data['ship_add2'] = $address;
        $post_data['ship_city'] = $city;
        $post_data['ship_state'] = $state;
        $post_data['ship_postcode'] = $pin_code;
        $post_data['ship_phone'] = $mobile;
        $post_data['ship_country'] = $country;

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";



        

        $sslc = new SslCommerzNotification();


        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function payViaAjax(Request $request)
    {

        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";


        #Before  going to initiate the payment order status need to update as Pending.
        $update_product = DB::table('orders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function success(Request $request)
    {
        // echo "Transaction is Successful";

        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();

        #Check order status in order tabel against the transaction id or order id.
        

        
        $validation = $sslc->orderValidate($tran_id, $amount, $currency, $request->all());

        if ($validation == TRUE) {
            /*
            That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
            in order table as Processing or Complete.
            Here you can also sent sms or email for successfull transaction to customer
            */
            $current_date = Carbon::now();

            $currency = Currency::first();

            $gsettings = Setting::first();
            
            $carts = Cart::where('user_id',Auth::User()->id)->get();
           
            foreach($carts as $cart)
            { 
                if ($cart->offer_price != 0)
                {
                    $pay_amount =  $cart->offer_price;
                }
                else
                {
                    $pay_amount =  $cart->price;
                }

                if ($cart->disamount != 0 || $cart->disamount != NULL)
                {
                    $cpn_discount =  $cart->disamount;
                }
                else
                {
                    $cpn_discount =  '';
                }


                $lastOrder = Order::orderBy('created_at', 'desc')->first();

                if ( ! $lastOrder )
                {
                    // We get here if there is no order at all
                    // If there is no number set it to 0, which will be 1 at the end.
                    $number = 0;
                }
                else
                { 
                    $number = substr($lastOrder->order_id, 3);
                }

                if($cart->type == 1)
                {
                    $bundle_id = $cart->bundle_id;
                    $bundle_course_id = $cart->bundle->course_id;
                    $course_id = NULL;
                    $duration = NULL;
                    $instructor_payout = 0;
                    $instructor_id = $cart->bundle->user_id;

                    if($cart->bundle->duration_type == "m")
                    {
                        
                        if($cart->bundle->duration != NULL && $cart->bundle->duration !='')
                        {
                            $days = $cart->bundle->duration * 30;
                            $todayDate = date('Y-m-d');
                            $expireDate = date("Y-m-d", strtotime("$todayDate +$days days"));
                        }
                        else{
                            $todayDate = NULL;
                            $expireDate = NULL;
                        }
                    }
                    else
                    {

                        if($cart->bundle->duration != NULL && $cart->bundle->duration !='')
                        {
                            $days = $cart->bundle->duration;
                            $todayDate = date('Y-m-d');
                            $expireDate = date("Y-m-d", strtotime("$todayDate +$days days"));
                        }
                        else{
                            $todayDate = NULL;
                            $expireDate = NULL;
                        }

                    }
                }
                else{

                    if($cart->courses->duration_type == "m")
                    {
                        
                        if($cart->courses->duration != NULL && $cart->courses->duration !='')
                        {
                            $days = $cart->courses->duration * 30;
                            $todayDate = date('Y-m-d');
                            $expireDate = date("Y-m-d", strtotime("$todayDate +$days days"));
                        }
                        else{
                            $todayDate = NULL;
                            $expireDate = NULL;
                        }
                    }
                    else
                    {

                        if($cart->courses->duration != NULL && $cart->courses->duration !='')
                        {
                            $days = $cart->courses->duration;
                            $todayDate = date('Y-m-d');
                            $expireDate = date("Y-m-d", strtotime("$todayDate +$days days"));
                        }
                        else{
                            $todayDate = NULL;
                            $expireDate = NULL;
                        }

                    }


                    $setting = InstructorSetting::first();


                    if($cart->courses->instructor_revenue != NULL)
                    {
                        $x_amount = $pay_amount * $cart->courses->instructor_revenue;
                        $instructor_payout = $x_amount / 100;
                    }
                    else
                    {

                        if(isset($setting))
                        {
                            if($cart->courses->user->role == "instructor")
                            {
                                $x_amount = $pay_amount * $setting->instructor_revenue;
                                $instructor_payout = $x_amount / 100;
                            }
                            else
                            {
                                $instructor_payout = 0;
                            }
                            
                        }
                        else
                        {
                            $instructor_payout = 0;
                        }  
                    }

                    

                    $bundle_id = NULL;
                    $course_id = $cart->course_id;
                    $bundle_course_id = NULL;
                    $duration = $cart->courses->duration;
                    $instructor_id = $cart->courses->user_id;
                }

               
                       
                $created_order = Order::create([
                    'course_id' => $course_id,
                    'user_id' => Auth::User()->id,
                    'instructor_id' => $instructor_id,
                    'order_id' => '#' . sprintf("%08d", intval($number) + 1),
                    'transaction_id' => $tran_id,
                    'payment_method' => 'SSLCOMMERZ',
                    'total_amount' => $pay_amount,
                    'coupon_discount' => $cpn_discount,
                    'currency' => $response['CURRENCY'],
                    'currency_icon' => $currency->icon,
                    'duration' => $duration,
                    'enroll_start' => $todayDate,
                    'enroll_expire' => $expireDate,
                    'instructor_revenue' => $instructor_payout,
                    'bundle_id' => $bundle_id,
                    'bundle_course_id' => $bundle_course_id,
                    'created_at'  => \Carbon\Carbon::now()->toDateTimeString(),
                    ]
                );
                
                Wishlist::where('user_id',Auth::User()->id)->where('course_id', $cart->course_id)->delete();

                Cart::where('user_id',Auth::User()->id)->delete();


                if($instructor_payout != 0)
                {
                    if($created_order)
                    {
                        if($cart->type == 0)
                        {
                            if($cart->courses->user->role == "instructor")
                            {
                                $created_payout = PendingPayout::create([
                                    'user_id' => $cart->courses->user_id,
                                    'course_id' => $cart->course_id,
                                    'order_id' => $created_order->id,
                                    'transaction_id' => $tran_id,
                                    'total_amount' => $pay_amount,
                                    'currency' => $response['CURRENCY'],
                                    'currency_icon' => $currency->icon,
                                    'instructor_revenue' => $instructor_payout,
                                    'created_at'  => \Carbon\Carbon::now()->toDateTimeString(),
                                    'updated_at'  => \Carbon\Carbon::now()->toDateTimeString(),
                                    ]
                                );
                            }
                        }
                    }
                }

                if($created_order){
                    if ($gsettings->twilio_enable == '1') {

                        try{
                            $recipients = Auth::user()->mobile;
                            

                            $msg = 'Hey'. ' ' .Auth::user()->fname . ' '.
                                    'You\'r successfully enrolled in '. $cart->courses->title .
                                    'Thanks'. ' ' . config('app.name');
                        
                            TwilioMsg::sendMessage($msg, $recipients);

                        }catch(\Exception $e){
                            
                        }

                    }
                }

                if($created_order){
                    if (env('MAIL_USERNAME')!=null) {
                        try{
                            
                            /*sending email*/
                            $x = 'You are successfully enrolled in a course';
                            $order = $created_order;
                            Mail::to(Auth::User()->email)->send(new SendOrderMail($x, $order));

                            /*sending admin email*/
                            $x = 'User Enrolled in course '. $cart->courses->title;
                            $order = $created_order;
                            Mail::to($cart->courses->user->email)->send(new AdminMailOnOrder($x, $order));


                        }catch(\Swift_TransportException $e){
                            Session::flash('deleted',trans('flash.PaymentMailError'));
                            return redirect('confirmation');
                        }
                    }
                }

                if($cart->type == 0)
                {

                    if($created_order){
                        // Notification when user enroll
                        $cor = Course::where('id', $cart->course_id)->first();

                        $course = [
                          'title' => $cor->title,
                          'image' => $cor->preview_image,
                        ];

                        $enroll = Order::where('course_id', $cart->course_id)->get();

                        if(!$enroll->isEmpty())
                        {
                            foreach($enroll as $enrol)
                            {
                                $user = User::where('id', $enrol->user_id)->get();
                                Notification::send($user,new UserEnroll($course));
                            }
                        }
                    }

                }
               
            }

            return redirect('confirmation');


        } else {
            /*
            That means IPN did not work or IPN URL was not set in your merchant panel and Transation validation failed.
            Here you need to update order status as Failed in order table.
            */
             \Session::flash('delete', trans('flash.PaymentFailed'));
            return redirect('/');
        }
       


    }

    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        \Session::flash('delete', trans('flash.PaymentFailed'));
            return redirect('/');

    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        \Session::flash('delete', 'Payment Canceled');
            return redirect('/');


    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($tran_id, $order_details->amount, $order_details->currency, $request->all());
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Processing']);

                    echo "Transaction is successfully Completed";
                } else {
                    /*
                    That means IPN worked, but Transation validation failed.
                    Here you need to update order status as Failed in order table.
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Failed']);

                    echo "validation Fail";
                }

            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }

}

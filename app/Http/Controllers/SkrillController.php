<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Course;
use App\Currency;
use App\InstructorSetting;
use App\Mail\SendOrderMail;
use App\Notifications\UserEnroll;
use App\Order;
use App\PendingPayout;
use App\User;
use App\Wishlist;
use Auth;
use Carbon\Carbon;
use Crypt;
use Illuminate\Http\Request;
use Mail;
use Notification;
use Obydul\LaraSkrill\SkrillClient;
use Obydul\LaraSkrill\SkrillRequest;
use Redirect;
use Session;
use App\Mail\AdminMailOnOrder;
use TwilioMsg;
use App\Setting;

class SkrillController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Skrill Add on For eclass v2.2 and above
    |--------------------------------------------------------------------------
    |
    | © 2020 - AddOn Developer @nkit
    | - Mediacity
    |
     */

    public function pay(Request $request)
    {

        $pay = Crypt::decrypt($request->amount);
        Session::put('payment', $pay);

        $currency = Currency::first();

        $request = new SkrillRequest();
        $request->transaction_id = 'SKRILL_' . uniqid(); // generate transaction id
        $request->amount = $pay;
        $request->currency = $currency->currency;
        $request->language = Session::get('changed_language');
        $request->prepare_only = '1';
        $request->merchant_fields = 'site_name, customer_email';
        $request->site_name = config('app.url');
        $request->customer_email = Auth::user()->email;
        $request->detail1_description = 'Payment for digital content';
        $request->detail1_text = '';
        $request->pay_to_email = env('SKRILL_MERCHANT_EMAIL');

        // Create object instance of SkrillClient
        $client = new SkrillClient($request);
        $sid = $client->generateSID(); //return SESSION ID

        // handle error
        $jsonSID = json_decode($sid);
        if ($jsonSID != null && $jsonSID->code == "BAD_REQUEST") {
            return $jsonSID->message;
        }

        // do the payment
        $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url
        return Redirect::to($redirectUrl); // redirect user to Skrill payment page

    }

    public function success(Request $request)
    {

        $currency = Currency::first();

        $gsettings = Setting::first();

        $carts = Cart::where('user_id', Auth::User()->id)->get();

        foreach ($carts as $cart) {
            if ($cart->offer_price != 0) {
                $pay_amount = $cart->offer_price;
            } else {
                $pay_amount = $cart->price;
            }

            if ($cart->disamount != 0 || $cart->disamount != null) {
                $cpn_discount = $cart->disamount;
            } else {
                $cpn_discount = '';
            }

            $lastOrder = Order::orderBy('created_at', 'desc')->first();

            if (!$lastOrder) {
                // We get here if there is no order at all
                // If there is no number set it to 0, which will be 1 at the end.
                $number = 0;
            } else {
                $number = substr($lastOrder->order_id, 3);
            }

            if ($cart->type == 1) {
                $bundle_id = $cart->bundle_id;
                $bundle_course_id = $cart->bundle->course_id;
                $course_id = null;
                $duration = null;
                $instructor_payout = null;
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

            } else {

                if ($cart->courses->duration_type == "m") {

                    if ($cart->courses->duration != null && $cart->courses->duration != '') {
                        $days = $cart->courses->duration * 30;
                        $todayDate = date('Y-m-d');
                        $expireDate = date("Y-m-d", strtotime("$todayDate +$days days"));
                    } else {
                        $todayDate = null;
                        $expireDate = null;
                    }
                } else {

                    if ($cart->courses->duration != null && $cart->courses->duration != '') {
                        $days = $cart->courses->duration;
                        $todayDate = date('Y-m-d');
                        $expireDate = date("Y-m-d", strtotime("$todayDate +$days days"));
                    } else {
                        $todayDate = null;
                        $expireDate = null;
                    }

                }

                $setting = InstructorSetting::first();

                if ($cart->courses->instructor_revenue != null) {
                    $x_amount = $pay_amount * $cart->courses->instructor_revenue;
                    $instructor_payout = $x_amount / 100;
                } else {

                    if (isset($setting)) {
                        if ($cart->courses->user->role == "instructor") {
                            $x_amount = $pay_amount * $setting->instructor_revenue;
                            $instructor_payout = $x_amount / 100;
                        } else {
                            $instructor_payout = 0;
                        }

                    } else {
                        $instructor_payout = 0;
                    }
                }

                $bundle_id = null;
                $course_id = $cart->course_id;
                $bundle_course_id = null;
                $duration = $cart->courses->duration;
                $instructor_id = $cart->courses->user_id;
            }

            $created_order = Order::create([
                'course_id' => $course_id,
                'user_id' => Auth::User()->id,
                'instructor_id' => $instructor_id,
                'order_id' => '#' . sprintf("%08d", intval($number) + 1),
                'transaction_id' => $request->transaction_id,
                'payment_method' => strtoupper('SKRILL'),
                'total_amount' => $pay_amount,
                'coupon_discount' => $cpn_discount,
                'currency' => $currency->currency,
                'currency_icon' => $currency->icon,
                'duration' => $duration,
                'enroll_start' => $todayDate,
                'enroll_expire' => $expireDate,
                'bundle_id' => $bundle_id,
                'bundle_course_id' => $bundle_course_id,
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            ]
            );

            Wishlist::where('user_id', Auth::User()->id)->where('course_id', $cart->course_id)->delete();

            Cart::where('user_id', Auth::User()->id)->where('course_id', $cart->course_id)->delete();

            if ($instructor_payout != 0) {
                if ($created_order) {

                    if ($cart->type == 0) {

                        if ($cart->courses->user->role == "instructor") {

                            $created_payout = PendingPayout::create([
                                'user_id' => $cart->courses->user_id,
                                'course_id' => $cart->course_id,
                                'order_id' => $created_order->id,
                                'transaction_id' => $request->transaction_id,
                                'total_amount' => $pay_amount,
                                'currency' => $currency->currency,
                                'currency_icon' => $currency->icon,
                                'instructor_revenue' => $instructor_payout,
                                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
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

            if ($created_order) {
                try {

                    /*sending email*/
                    $x = 'You are successfully enrolled in a course';
                    $order = $created_order;
                    Mail::to(Auth::User()->email)->send(new SendOrderMail($x, $order));

                    /*sending admin email*/
                    $x = 'User Enrolled in course '. $cart->courses->title;
                    $order = $created_order;
                    Mail::to($cart->courses->user->email)->send(new AdminMailOnOrder($x, $order));

                } catch (\Swift_TransportException $e) {
                    Session::flash('deleted', trans('flash.PaymentMailError'));
                    return redirect('confirmation');

                }
            }

            if ($cart->type == 0) {

                if ($created_order) {
                    // Notification when user enroll
                    $cor = Course::where('id', $cart->course_id)->first();

                    $course = [
                        'title' => $cor->title,
                        'image' => $cor->preview_image,
                    ];

                    $enroll = Order::where('course_id', $cart->course_id)->get();

                    if (!$enroll->isEmpty()) {
                        foreach ($enroll as $enrol) {
                            $user = User::where('id', $enrol->user_id)->get();
                            Notification::send($user, new UserEnroll($course));
                        }
                    }
                }
            }
        }

        // \Session::flash('success', trans('flash.PaymentSuccess'));
        return redirect('confirmation');

        \Session::flash('delete', trans('flash.PaymentFailed'));
        return redirect('/');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Config;
use DB;
use Auth;
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
use Session;
use Crypt;
use Cookie;
use App;
use App\Mail\AdminMailOnOrder;
use TwilioMsg;
use App\Setting;

class IyzController extends Controller
{
    public function pay(Request $request)
    {
    	// return $request;
    	$amount = Crypt::decrypt($request->amount);
    	$conversation_id = $request->conversation_id;
    	$basket_id = 'B'.substr(str_shuffle("0123456789"), 0, 5);
    	$user_id = 'BY'.Auth::user()->id;
    	$fname = Auth::user()->fname;
    	$lname = Auth::user()->lname;
    	$address = Auth::user()->address;
    	$city = $request->city;
    	$state = $request->state;
    	$country = $request->country;
    	$item_id = 'BI'.substr(str_shuffle("0123456789"), 0, 3);
    	$pincode = $request->pincode;
    	$now = \Carbon\Carbon::now()->toDateTimeString();
    	$ip = $request->ip();
    	$currency = $request->currency;

    	$language = strtoupper(App::getLocale());



    	$identity = $request->identity_number;
    	$email = $request->email;
    	$mobile = $request->mobile;

    	Cookie::queue('user_selection', Auth::user()->id, 100);



        $options = new \Iyzipay\Options();
		$options->setApiKey(env('IYZIPAY_API_KEY'));
		$options->setSecretKey(env('IYZIPAY_SECRET_KEY'));
		$options->setBaseUrl(env('IYZIPAY_BASE_URL'));
		        
		$request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
		$request->setLocale($language);
		$request->setConversationId($conversation_id);
		$request->setPrice($amount);
		$request->setPaidPrice($amount);
		$request->setCurrency(\Iyzipay\Model\Currency::TL);
		$request->setBasketId($basket_id);
		$request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
		$request->setCallbackUrl(url('return/izy/success'));
		$request->setEnabledInstallments(array(1));
		$buyer = new \Iyzipay\Model\Buyer();
		$buyer->setId($user_id);
		$buyer->setName($fname);
		$buyer->setSurname($lname);
		$buyer->setGsmNumber($mobile);
		$buyer->setEmail($email);
		$buyer->setIdentityNumber($identity);
		$buyer->setLastLoginDate($now);
		$buyer->setRegistrationDate($now);
		$buyer->setRegistrationAddress($address);
		$buyer->setIp($ip);
		$buyer->setCity($city);
		$buyer->setCountry($country);
		$buyer->setZipCode($pincode);
		$request->setBuyer($buyer);
		$shippingAddress = new \Iyzipay\Model\Address();
		$shippingAddress->setContactName($fname);
		$shippingAddress->setCity($city);
		$shippingAddress->setCountry($country);
		$shippingAddress->setAddress($address);
		$shippingAddress->setZipCode($pincode);
		$request->setShippingAddress($shippingAddress);
		$billingAddress = new \Iyzipay\Model\Address();
		$billingAddress->setContactName($fname);
		$billingAddress->setCity($city);
		$billingAddress->setCountry($country);
		$billingAddress->setAddress($address);
		$billingAddress->setZipCode($pincode);
		$request->setBillingAddress($billingAddress);
		$basketItems = array();
		$firstBasketItem = new \Iyzipay\Model\BasketItem();
		$firstBasketItem->setId($item_id);
		$firstBasketItem->setName(config('app.name'));
		$firstBasketItem->setCategory1(config('app.name'));
		$firstBasketItem->setCategory2(config('app.name'));
		$firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
		$firstBasketItem->setPrice($amount);
		$basketItems[0] = $firstBasketItem;
		
		$request->setBasketItems($basketItems);
		# make request
		$payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($request, $options);


		// dd($payWithIyzicoInitialize);


		if($payWithIyzicoInitialize->getstatus() == 'success')
		{
			$url = $payWithIyzicoInitialize->getpayWithIyzicoPageUrl();


			return redirect($url); 
		}

		$error_msg = $payWithIyzicoInitialize->getErrorMessage();


		\Session::flash('delete', $error_msg);
		return redirect('all/cart');




    }



    public function callback(Request $request)
    {

    	$token = $request->token;

    	$options = new \Iyzipay\Options();
		$options->setApiKey(env('IYZIPAY_API_KEY'));
		$options->setSecretKey(env('IYZIPAY_SECRET_KEY'));
		$options->setBaseUrl(env('IYZIPAY_BASE_URL'));
    	

    	$request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();

    	// dd($request);

		$request->setLocale(\Iyzipay\Model\Locale::EN);
		$request->setConversationId("123456789");
		$request->setToken($token);

		$checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, $options);

		// dd($checkoutForm);

		$status = $checkoutForm->getstatus();

		$payment_id = $checkoutForm->getpaymentId();

		$iyz_currency = $checkoutForm->getcurrency();


		if($status == 'success') {

			$currency = Currency::first();

            $gsettings = Setting::first();

			$userid = Cookie::get('user_selection');
            $user = User::find($userid);

            if(isset($user))
            {
               $auth_user_id = $user->id;
            }
            else{
                $auth_user_id = Auth::user()->id;
            }

			$carts = Cart::where('user_id', $auth_user_id)->get();

		

            $pay_amount = 0;
		   
		   	foreach($carts as $cart)
		   	{  
		   		if ($cart->offer_price != 0 || $cart->offer_price != NULL)
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
		            'transaction_id' => $payment_id,
		            'payment_method' => 'Iyzipay',
		            'total_amount' => $pay_amount,
		            'coupon_discount' => $cpn_discount,
		            'currency' => $iyz_currency,
		            'currency_icon' => $currency->icon,
		            'duration' => $duration,
		            'enroll_start' => $todayDate,
                    'enroll_expire' => $expireDate,
                    'bundle_id' => $bundle_id,
                    'bundle_course_id' => $bundle_course_id,
		            'created_at'  => \Carbon\Carbon::now()->toDateTimeString(),
		            ]
		        );
		        
		        Wishlist::where('user_id',Auth::User()->id)->where('course_id', $cart->course_id)->delete();

		        Cart::where('user_id',Auth::User()->id)->where('course_id', $cart->course_id)->delete();


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
    	                            'transaction_id' => $payment_id,
    	                            'total_amount' => $pay_amount,
    	                            'currency' => $currency->currency,
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
							\Session::flash('deleted',trans('flash.PaymentMailError'));
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
        	

        	// \Session::flash('success', trans('flash.PaymentSuccess'));
		    return redirect('confirmation');

		}


		\Session::flash('delete', trans('flash.PaymentFailed'));
		    return redirect('/');

    }
}

<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Validator;
use URL;
use Session;
use Redirect;

/** All Paypal Details class **/
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use Crypt;
use Illuminate\Support\Facades\Input;
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
use App\Mail\AdminMailOnOrder;
use TwilioMsg;
use App\Setting;

class PaypalController extends Controller
{
    public function __construct()
    {
		/** PayPal api context **/
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
	}

	public function payWithpaypal(Request $request)
    {

    	$currency = Currency::first();
    	$gsettings = Setting::first();
    	$currency_code = strtoupper($currency->currency);

    	$pay = Crypt::decrypt($request->amount);
    	Session::put('payment',$pay);
		$payer = new Payer();
		        $payer->setPaymentMethod('paypal');
		$item_1 = new Item();
		$item_1->setName('Item 1') /** item name **/
		            ->setCurrency($currency_code)
		            ->setQuantity(1)
		            ->setPrice($pay); /** unit price **/
		$item_list = new ItemList();
		        $item_list->setItems(array($item_1));
		$amount = new Amount();
		        $amount->setCurrency($currency_code)
		            ->setTotal($pay);
		$transaction = new Transaction();
		        $transaction->setAmount($amount)
		            ->setItemList($item_list)
		            ->setDescription('Your transaction description');
		$redirect_urls = new RedirectUrls();
		        $redirect_urls->setReturnUrl(URL::route('status')) /** Specify return URL **/
		            ->setCancelUrl(URL::route('status'));
		$payment = new Payment();
		        $payment->setIntent('Sale')
		            ->setPayer($payer)
		            ->setRedirectUrls($redirect_urls)
		            ->setTransactions(array($transaction));
		        
		try {
			$payment->create($this->_api_context);
		} 
		catch (\PayPal\Exception\PayPalConnectionException $ex) {
			if (\Config::get('app.debug')) {
				\Session::flash('delete', $ex->getMessage());
				return redirect('/');
			} else {
				\Session::flash('delete', $ex->getMessage());
				return redirect('/');
			}
		}

		foreach ($payment->getLinks() as $link) {
			if ($link->getRel() == 'approval_url') {
				$redirect_url = $link->getHref();
			    break;
			}
		}
		/** add payment ID to session **/
		Session::put('paypal_payment_id', $payment->getId());
		if (isset($redirect_url)) {
		/** redirect to paypal **/
		    return Redirect::away($redirect_url);
		}

		\Session::put('error', 'Unknown error occurred');
		        return redirect('/');
	}

	public function getPaymentStatus(Request $request)
    {
        /** Get the payment ID before session clear **/
        $payment_id = Session::get('paypal_payment_id');
        $amount = Session::get('payment');
		/** clear the session payment ID **/
		        Session::forget('paypal_payment_id');
		        if (empty($request->get('PayerID')) || empty($request->get('token')))

		         {
		\Session::flash('delete', trans('flash.PaymentFailed'));
		            return Redirect('/');
		}
		 $payment = Payment::get($payment_id, $this->_api_context);
		    	$execution = new PaymentExecution();
		        $execution->setPayerId($request->get('PayerID'));
		/**Execute the payment **/
		    $result = $payment->execute($execution, $this->_api_context);


		    $gsettings = Setting::first();



		if ($result->getState() == 'approved') {

			$transactions = $payment->getTransactions();
		    $relatedResources = $transactions[0]->getRelatedResources();
		    $sale = $relatedResources[0]->getSale();
		    $saleId = $sale->getId();

			$currency = Currency::first();
			
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
		            'transaction_id' => $payment_id,
		            'payment_method' => 'PayPal',
		            'total_amount' => $pay_amount,
		            'coupon_discount' => $cpn_discount,
		            'currency' => $currency->currency,
		            'currency_icon' => $currency->icon,
		            'duration' => $duration,
		            'enroll_start' => $todayDate,
                    'enroll_expire' => $expireDate,
                    'bundle_id' => $bundle_id,
                    'bundle_course_id' => $bundle_course_id,
                    'sale_id' => $saleId,
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

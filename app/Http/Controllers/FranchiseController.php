<?php

namespace App\Http\Controllers;

use App\Models\Franchise;
use App\Models\FranchiseRequest;
use App\Models\CoursePayment;
use Illuminate\Support\Facades\Hash;

use App\Models\Payment;
use App\User;
use Illuminate\Http\Request;
use Cartalyst\Stripe\Stripe;


class FranchiseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function requests()
    {
        
        $franchises = FranchiseRequest::all();
        return view('admin.franchise.index', compact('franchises'));
    }
    public function index()
    {
        
        $franchises = FranchiseRequest::all();
        return view('admin.franchise.index', compact('franchises'));
    }


    public function franchiseForm()
    {

        return view('admin.franchise.franchiseform');
    }


    public function postPaymentStripe(Request $request)
    {
        // dd($request->all());
        $validator = \Validator::make($request->all(), [
            'card_no' => 'required',
            'ccExpiryMonth' => 'required',
            'ccExpiryYear' => 'required',
            'cvvNumber' => 'required',
            //'amount' => 'required',
        ]);


        $amount1 = explode('$', $request->amount);
        $amount = $amount1[0];
        ///////////////////////////////
        if ($validator->passes()) {
            $input = $request->except('_token');

            $stripe = new Stripe();


            $stripe->setApiKey(env('STRIPE_SECRET'));

            try {
                $token = $stripe->tokens()->create([
                    'card' => [
                        'number' => $request->get('card_no'),
                        'exp_month' => $request->get('ccExpiryMonth'),
                        'exp_year' => $request->get('ccExpiryYear'),
                        'cvc' => $request->get('cvvNumber'),
                    ],
                ]);
                $charge = $stripe->charges()->create([
                    'card' => $token['id'],
                    'currency' => 'USD',
                    'amount' => $amount,
                    'description' => 'wallet',
                ]);

                $data = [

                    'charge' => $charge,
                    'token' => $token
                ];
                \Session::put('data', $data);
                // dd('sds');
                if (!isset($token['id'])) {

                    return redirect()->route('franchiserequest');
                }


                if ($charge['status'] == 'succeeded') {


                    // return redirect()->route('franchiserequest');
                } else {

                    \Session::put('error', 'Money not add in wallet!!');
                    return redirect()->route('franchiserequest');
                }
            } catch (Exception $e) {

                \Session::put('error', $e->getMessage());
                return redirect()->route('franchiserequest');
            } catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {

                \Session::put('error', $e->getMessage());
                //  dd($e);
                return redirect()->route('franchiserequest')->with('success', $e->getMessage());
            } catch (\Cartalyst\Stripe\Exception\MissingParameterException $e) {

                \Session::put('error', $e->getMessage());

                return redirect()->route('franchiserequest');
            }
        }


        ///////////////////////////////////

        $req = new FranchiseRequest();

        $req->name = $request->f_name;
        $req->owner_name = $request->o_name;
        $req->description = $request->description;
        $req->email = $request->email;
        $req->students = $request->students;
        $req->amount = $amount;




        if (!empty($request->file)) {
            $f = $request->file;
            $name = $f->getClientOriginalName();

            $fileName = time() . $name;
            $attachment = $f->move(storage_path() . '/app/public/', $fileName);
            $req->file = $fileName;
        }

        $req->save();
        $data = \Session::get('data');

        $charge = $data['charge'];
        // dd($charge);
        $token = $data['token'];
        $card_expiry = $charge['payment_method_details']['card']['exp_month'] . "-" . $charge['payment_method_details']['card']['exp_year'];
        $payment_id = $charge['id'];
        $card_brand = $charge['payment_method_details']['card']['brand'];
        $funcding = $charge['payment_method_details']['card']['funding'];

        $type = $charge['payment_method_details']['type'];
        $payment_method = $charge['payment_method'];


        $payment = new Payment();
        $payment->email = $req->email;

        $payment->payment_id = $payment_id;
        $payment->type = $type;
        $payment->card_brand = $card_brand;
        $payment->amount = $amount;
        $payment->exp_date = $card_expiry;
        $payment->funding = $funcding;
        $payment->payment_method = $payment_method;



        $payment->save();

        return redirect()->route('franchiserequest')->with('success', "Request Submitted");
    }


    public function reject($id)
    {

        $franchise = FranchiseRequest::find($id);
        $franchise->status = 2;
        $franchise->save();

        return back()->with('success', 'Request Rejected');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // dd($_SERVER['DOCUMENT_ROOT']);
        $franchises = FranchiseRequest::all();
        $franchizes = Franchise::all();

        return view('admin.franchise.create_franchise', compact('franchises', 'franchizes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	
////////////////////////-----------franchise creation code start-------------------/////////////////
	
	
	
    public function store(Request $request)
    {
		ini_set('max_execution_time', '0');
        $fran = FranchiseRequest::find($request->franchise);
		
		$this->subdomain($fran->name);
		
		 $zip = new \ZipArchive;
        if ($zip->open($_SERVER['DOCUMENT_ROOT'] .'/projects/LMS/FranchiseLMS.zip') === TRUE) {
            
            $zip->extractTo($_SERVER['DOCUMENT_ROOT']. "/" . $fran->name);
            $zip->close();
        }
        $franchise = new Franchise();
        $franchise->name = $fran->name;
        $franchise->students = $request->students;
        $franchise->save();
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $fran->name.'/config/franchisedata.php',
			'<?php
				return [
						"franchise_id" => "'.$franchise->id.'",
				];');
	
		$fran_user = new User();
		$fran_user->first_name = $franchise->name;
		$fran_user->email = $fran->email;
		$fran_user->password = Hash::make('123456789');
		$fran_user->role_id = 1;
		$fran_user->save();
        return back()->with('success', 'Franchise Created Successfully');
    }
	
	
// 	public function subdomain($name){
// 		$host = '143.198.207.123';
// 		$login = 'root';
// 		$password = 'd5F^JQ4ufd';

// 		//echo "<br/>";
// 		$curl7 = $this->curlInit($host, $login, $password);
// 		$contenu = $this->createSubDomain($name)->saveXML();
// 		//echo htmlspecialchars($contenu);
// 		$reponse = $this->sendRequest($curl7, $contenu);
// 		//echo "<br/>";
// 		echo htmlspecialchars($reponse);
// 		//echo "<br/>";
// 		//echo "check";
		
// 	}


// for cpanel

private function subdomain($name)
    {
        // Will be used if not passed via parameter and not set in subdomains file
        $buildRequest = "/frontend/paper_lantern/subdomain/doadddomain.html?rootdomain=cplusoft.com&domain=" . $name . "&dir=public_html/" . $name . "/public";
        $openSocket = fsockopen('localhost', 2082);
        if (!$openSocket) {
            return "Socket error";
            exit();
        }
        $authString = "wdnglbxe1zoj:]2?<Js[k";
        $authPass = base64_encode($authString);
        $buildHeaders  = "GET " . $buildRequest . "\r\n";
        $buildHeaders .= "HTTP/1.0\r\n";
        $buildHeaders .= "Host:localhost\r\n";
        $buildHeaders .= "Authorization: Basic " . $authPass . "\r\n";
        $buildHeaders .= "\r\n";
        fputs($openSocket, $buildHeaders);
        while (!feof($openSocket)) {
            fgets($openSocket, 128);
        }
        fclose($openSocket);
        // $newDomain = "http://" . $subDomain . "." . $this->domain . "/";
    }
	
	function curlInit($host, $login, $password)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://{$host}:8443/enterprise/control/agent.php");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
		array("HTTP_AUTH_LOGIN: {$login}",
		"HTTP_AUTH_PASSWD: {$password}",
		"HTTP_PRETTY_PRINT: TRUE",
		"Content-Type: text/xml")
		);

		return $curl;
	}
/**
* Performs a Plesk API request, returns raw API response text
*
* @return string
* @throws ApiRequestException
*/
	function sendRequest($curl, $packet)
	{
		curl_setopt($curl, CURLOPT_POSTFIELDS, $packet);

		$result = curl_exec($curl);

		if (curl_errno($curl)) {
		$errmsg = curl_error($curl);
		$errcode = curl_errno($curl);
		curl_close($curl);
		throw new ApiRequestException($errmsg, $errcode);
		}
		curl_close($curl);
		return $result;
	}
	
	
	function createSubdomain($nom)
	{
		$xmldoc = new \DOMDocument('1.0', 'UTF-8');
		$xmldoc->formatOutput = true;
		$packet = $xmldoc->createElement('packet');
		$packet->setAttribute('version', '1.6.9.1');
		$xmldoc->appendChild($packet);
		$subdomain = $xmldoc->createElement('subdomain');
		$packet->appendChild($subdomain);
		$add = $xmldoc->createElement('add');
		$subdomain->appendChild($add);
		$parent = $xmldoc->createElement('parent', 'edgdevwork.com');
		$add->appendChild($parent);
		$name = $xmldoc->createElement('name', $nom);
		$add->appendChild($name);
		$add->appendChild($xmldoc->createElement('home', '/'.$nom.''));
		$property = $xmldoc->createElement('property');
		$property->appendChild($xmldoc->createElement('name', 'ssi'));
		$property->appendChild($xmldoc->createElement('value', 'true'));
		$add->appendChild($property);
		
		return $xmldoc;

	}
	
	
	
	////////////////////////-----------franchise creation code end-------------------/////////////////


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $franchise = Franchise::find($id);
        $franchise->name = $request->name;
        $franchise->save();


        return back()->with('success', 'Franchise Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $users = User::where('franchise_id', $id)->get();
        foreach ($users as $user) {
            $user->franchise_id = NULL;
            $user->save();
        }

        Franchise::find($id)->delete();
        return back()->with('success', 'Franchise Deleted Successfully');
    }



    function increment(Request $request)
    {
        // dd($request->all());

        $franchise = Franchise::find($request->id);
        $franchise->students = $request->val;
        $franchise->save();

        $response = [
            'message' => 'success',
            'data' => 'incremented'
        ];
    }

    function decrement(Request $request)
    {
        // dd($request->all());

        $franchise = Franchise::find($request->id);
        $franchise->students = $request->val;
        $franchise->save();

        $response = [
            'message' => 'success',
            'data' => 'decremented'
        ];
    }
	
	
	public function coursePaymentHistory()
    {

        $course_payments = CoursePayment::all();

        return view('admin.payments.course_payment', compact('course_payments'));
    }


    public function studentPaymentHistory()
    {

        $student_payments = Payment::all();

        return view('admin.payments.student_payment', compact('student_payments'));
    }
}

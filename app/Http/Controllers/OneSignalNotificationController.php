<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OfferPushNotifications;
use Session;

class OneSignalNotificationController extends Controller
{
    public function index()
    {
        return view('admin.push_notification.index');
    }

    public function push(Request $request)
    {

        ini_set('max_excecution_time', -1);

        ini_set('memory_limit', -1);

        $request->validate([
            'subject' => 'required|string',
            'message' => 'required'
        ]);

        if(env('ONESIGNAL_APP_ID') =='' && env('ONESIGNAL_REST_API_KEY') == ''){

            \Session::flash('success', 'Please update onesignal keys in settings !');
            return back()->withInput();
        }

        try {

            $usergroup = User::query();

            $data = [
                'subject' => $request->subject,
                'body' => $request->message,
                'target_url' => $request->target_url ?? null,
                'icon' => $request->icon ?? null,
                'image' => $request->image ?? null,
                'buttonChecked' => $request->show_button ? "yes" : "no",
                'button_text' => $request->btn_text ?? null,
                'button_url' => $request->btn_url ?? null,
            ];

            if ($request->user_group == 'all_users') {

                $users = $usergroup->select('id')->where('role', '=', 'user')->get();

            } elseif ($request->user_group == 'all_instructors') {

                $users = $usergroup->select('id')->where('role', '=', 'instructors')->get();

            } elseif ($request->user_group == 'all_admins') {

                $users = $usergroup->select('id')->where('role', '=', 'admin')->get();

            } else {
                // all users
                $users = $usergroup->select('id')->get();
            }

            $users = $usergroup->select('id')->get();

            Notification::send($users, new OfferPushNotifications($data));

            \Session::flash('success', 'Notification pushed successfully !');
            return back();

        } catch (\Exception $e) {


             \Session::flash('delete', $e->getMessage());
            return back()->withInput();

        }

    }

    public function updateKeys(Request $request){

        $request->validate([
            'ONESIGNAL_APP_ID' => 'required|string',
            'ONESIGNAL_REST_API_KEY' => 'required|string'
        ],[
            'ONESIGNAL_APP_ID.required' => 'OneSignal app id is required',
            'ONESIGNAL_REST_API_KEY.required' => 'Onesignal rest api key is required'
        ]);


        $env_update = $this->changeEnv([
            'ONESIGNAL_APP_ID' => $request->ONESIGNAL_APP_ID,
            'ONESIGNAL_REST_API_KEY' => $request->ONESIGNAL_REST_API_KEY
            
        ]);
        

        \Session::flash('success', 'Keys updated successfully !');
        return back();
    }


    protected function changeEnv($data = array()){
    {
        if( count($data) > 0 ){

            // Read .env-file
            $env = file_get_contents(base_path() . '/.env');

            // Split string on every " " and write into array
            $env = preg_split('/\s+/', $env);;

            // Loop through given data
            foreach((array)$data as $key => $value){
              // Loop through .env-data
              foreach($env as $env_key => $env_value){
                // Turn the value into an array and stop after the first split
                // So it's not possible to split e.g. the App-Key by accident
                $entry = explode("=", $env_value, 2);

                // Check, if new key fits the actual .env-key
                if($entry[0] == $key){
                    // If yes, overwrite it with the new one
                    $env[$env_key] = $key . "=" . $value;
                } else {
                    // If not, keep the old one
                    $env[$env_key] = $env_value;
                }
              }
            }

            // Turn the array back to an String
            $env = implode("\n\n", $env);

            // And overwrite the .env with the new data
            file_put_contents(base_path() . '/.env', $env);

            return true;

        } else{

          return false;
        }
    }
    }


}

<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Setting;

class IpBlock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $ip = $request->ip();

        $setting = Setting::first();


        $ip_address = array();

        if($setting->ipblock_enable == 1)
        {
            if(is_array($setting['ipblock']) || is_object($setting['ipblock'])) 
            {
                foreach($setting->ipblock as $b)
                {
                    array_push($ip_address, $b);
                }
            }

            $ip_address = array_values(array_filter($ip_address));

            $ip_address = array_flatten($ip_address);


            if($setting->ipblock_enable == 1)
            {
                if(isset($ip_address) && in_array($ip, $ip_address))
                {
                    return redirect()->route('ip.block');
                }
                else
                {
                    return $next($request);
                }
                
            }
            else
            {
                return $next($request);
            }
        }
        else{
            return $next($request);
        }



            
    }
}

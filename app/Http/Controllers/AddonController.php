<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Module;
use ZipArchive;
use Artisan;
use Session;

class AddonController extends Controller
{
    public function addon()
    {
        $modules = Module::all();

        return view('admin.addon.index', compact('modules'));
    }

    public function addaddon()
    {

        return view('admin.addon.add');
    }


    public function installaddon(Request $request)
    {

        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }

        ini_set('max_execution_time', 300);
         $filename = $request->addon_file;
        $modulename = str_replace('.'.$filename->getClientOriginalExtension(),'',$filename->getClientOriginalName());
        $zip = new ZipArchive;
        $zipped = $zip->open($filename,ZipArchive::CREATE);
        if($zipped){
            // $zip->getFromName($modulename.'/module.json');
            $extract = $zip->extractTo(base_path().'/Modules/');
            if($extract){
                $module = Module::find($modulename);
                $module->enable();
                Artisan::call('module:publish');

                Artisan::call('module:migrate', ['module' => $modulename]);

                Session::flash('success', $modulename.'Module Installed Successfully');
                return redirect('/admin/addon');
            }
        }
        $zip->close();



    }


    public function status(Request $request, $addon)
    {

        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }
        //return $addon;

        $module = Module::find($addon);

        if( $module->isStatus(1))
        {
          $module->disable();
        }
        else{

            $module->enable();

        }

        return back();



    }

    public function delete(Request $request, $addon)
    {

        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }

         $module = Module::find($addon);

         $module->delete();
         return back();
    }
}

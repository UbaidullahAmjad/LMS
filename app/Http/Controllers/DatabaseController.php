<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Auth;
use File;
use Illuminate\Support\Facades\Storage;
use DirectoryIterator;

class DatabaseController extends Controller
{
    
    public function demoimport()
    {
        return view('admin.database.demo');
    }

    public function importdatabase()
    {
        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }

        \Artisan::call('import:demo');
        \Session::flash('delete','Demo Imported successfully !');

        return redirect('/');
    }

    public function resetdatabase()
    {

        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }

        \Artisan::call('demo:reset');
        
        \Session::flash('delete','Demo reset successfully  !');
        return redirect('/');
    }

    public function index()
    {

        $dump = env('DUMP_BINARY_PATH');
        return view('admin.database.backup', compact('dump'));
    }

    public function genrate(Request $request)
    {
        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }

        \Artisan::call('backup:run', ['--only-db' => true]);

        return back()->with('success',trans('flash.CreatedSuccessfully'));
    }

    public function download(Request $request, $filename)
    {

        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }

        if (! $request->hasValidSignature()) {
            return back()->with('delete','Download Link is invalid or expired !');
        }

        $filePath = storage_path().'/app/'.config('app.name').'/'.$filename;

        $fileContent = file_get_contents($filePath);

        $response = response($fileContent, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);

        return $response;
    }

    public function update(Request $request)
    {

        $input = $request->all();

        $env_update = $this->changeEnv([
            'DUMP_BINARY_PATH' => $input['DUMP_BINARY_PATH']
        ]);

        return back()->with('success',trans('flash.UpdatedSuccessfully'));
        
    }


    public function deletebackup()
    {

        if(config('app.demolock') == 1){
            return back()->with('delete','Disabled in demo');
        }

        $leave_files = array('.gitignore');

         $dir1 = storage_path() . '/app/eClass-LearningManagementSystem';

            foreach (glob("$dir1/*") as $file) {
                if (!in_array(basename($file), $leave_files)) {
                    try {
                        unlink($file);
                    } catch (\Exception $e) {

                    }
                }

            }

        return back()->with('success',trans('flash.DeletedSuccessfully'));
    }



    protected function changeEnv($data = array())
    {
        {
            if (count($data) > 0) {

                // Read .env-file
                $env = file_get_contents(base_path() . '/.env');

                // Split string on every " " and write into array
                $env = preg_split('/\s+/', $env);

                // Loop through given data
                foreach ((array) $data as $key => $value) {
                    // Loop through .env-data
                    foreach ($env as $env_key => $env_value) {
                        // Turn the value into an array and stop after the first split
                        // So it's not possible to split e.g. the App-Key by accident
                        $entry = explode("=", $env_value, 2);

                        // Check, if new key fits the actual .env-key
                        if ($entry[0] == $key) {
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

            } else {
                return false;
            }
        }
    }

}

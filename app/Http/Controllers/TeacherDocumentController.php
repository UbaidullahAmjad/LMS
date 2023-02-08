<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\TeacherDocument;
use Validator;
class TeacherDocumentController extends Controller
{
    // begin store
    public function store(Request $request)
    {       
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,png,jpg,gif,doc,docx,pdf,txt',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        if($request->hasFile('file')) 
        {
            $image                      =   $request->file('file');
            $path                       =   storage_path(). '/app/public/';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            
            // if($image->getClientOriginalExtension() ==  'jpeg' || $image->getClientOriginalExtension() ==  'png' || $image->getClientOriginalExtension() ==  'jpg' || $image->getClientOriginalExtension() ==  'gif' || $image->getClientOriginalExtension() ==  'doc'  || $image->getClientOriginalExtension() ==  'docx'  || $image->getClientOriginalExtension() ==  'pdf'  || $image->getClientOriginalExtension() ==  'txt')
            // {
                if($image->move($path, $filename))
                {
                    $addUser                        =   new TeacherDocument;       
                   
                    $addUser->file                 =   $filename;
                    $addUser->user_id               =   $request->teacher;
                    
                    if($addUser->save())
                    {
                        return redirect()->back()->with('success','File uploaded successfully');
                    }
                    else
                    {
                        return redirect()->back()->with('error','File could not be uploaded');
                    }
                }
                else
                {
                    return redirect()->back()->with('error','File could not be uploaded');                    
                }
            // }
            // else
            // {
            //     return redirect()->back()->with('error','Invalid file format');                                    
            // }
            

        }
        else
        {
            return redirect()->back()->with('error','File could not be uploaded');                    
        }
        
        
    }
    // end store
}

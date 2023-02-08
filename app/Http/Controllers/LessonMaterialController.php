<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LessonMaterial;
use Validator;
class LessonMaterialController extends Controller
{
    // begin store
    public function store(Request $request)
    {       
        $validator = Validator::make($request->all(), [
             'file.*' => 'required|mimes:jpeg,png,jpg,gif,pdf,txt,docx',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        if($request->hasFile('file')) 
        {
            $image                      =   $request->file('file');
            $path                       =   public_path(). '/lesson_material_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $addUser                            =   new LessonMaterial;       
                $addUser->file_title                =   $request->file_name;
                $addUser->file                      =   $filename;
                $addUser->lesson_id                 =   $request->lesson;
                if($image->getClientOriginalExtension() ==  'jpeg' || $image->getClientOriginalExtension() ==  'png' || $image->getClientOriginalExtension() ==  'jpg' ||$image->getClientOriginalExtension() ==  'gif' || $image->getClientOriginalExtension() ==  'JPEG' || $image->getClientOriginalExtension() ==  'PNG' || $image->getClientOriginalExtension() ==  'JPG' ||$image->getClientOriginalExtension() ==  'GIF')
                {
                    $addUser->type                  =   'Image';                    
                }
                else
                {    
                    $addUser->type                  =   'Document';
                }
                if($addUser->save())
                {
                    return redirect()->back()->with('success','Lesson material added successfully');
                }
                else
                {
                    return redirect()->back()->with('error','Lesson material could not be added');
                }
            }
            else
            {
                return redirect()->back()->with('error','Lesson material could not be added');                    
            }
        }
        else
        {
            return redirect()->back()->with('error','Lesson material could not be added');                    
        }
    }
    // end store

    // begin delete
    public function delete($id)
    {
        $id     =   base64_decode($id);
        if(LessonMaterial::where('id',$id)->delete())
        {
            return redirect()->back()->with('success','Lesson material deleted successfully');
        }
        else
        {
            return redirect()->back()->with('error','Lesson material cannot be deleted');
        }
    }
    // end delete
}

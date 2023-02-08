<?php

namespace App\Http\Controllers;

use App\LearningGoal;
use App\Level;
use Illuminate\Http\Request;

class LevelsController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLevel(Request $request)
    {

        //validation
 try{
        $level = new Level();

                $level->name= $request->level ;
                $level->domain_id=$request->domain_id;

                $level->save();
            return back()->with('success','Level inserted successfully!');
        }
        catch(Exception $e)
        {
            return back()->with('fail','Something went wrong!');
        }
    }


    public function updateLevel(Request $request)
    {

        //validation
 try{
        $level = Level::find($request->level_id);

                $level->name= $request->level ;
                $level->domain_id=$request->domain_id;

                $level->save();
            return back()->with('success','Level Updated successfully!');
        }
        catch(Exception $e)
        {
            return back()->with('fail','Something went wrong!');
        }
    }



    public function viewLevel($id)
    {
        $level = Level::find($id);
        $lgs = LearningGoal::where('level_id',$id)->get();
        // $levels = Level::where('domain_id',$id)->get();
        return view('admin.level.view',[
            'level' => $level,
            'lgs' => $lgs
        ]);

    }

    public function deleteLevel($id)
    {
        LearningGoal::where('level_id',$id)->delete();
        if(Level::find($id)->delete())
        {
            return back()->with('success','Level deleted successfully');
        }
        else
        {
            return back()->with('error','Level cannot be deleted');
        }
    }
}

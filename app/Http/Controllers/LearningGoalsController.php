<?php

namespace App\Http\Controllers;

use App\Domain;
use App\LearningGoal;
use App\Level;
use App\Models\LessonLearningGoal;
use App\Models\QuizLearningGoal;
use Illuminate\Http\Request;

class LearningGoalsController extends Controller
{
    public function domains()
    {
        $domains= Domain::get();
        return view('admin.domain.index' , compact('domains'));
    }


    public function addDomain()
    {
        return view('admin.domain.add');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDomain(Request $request)
    {

        //validation
 try{
        $domain = new Domain();

                $domain->name= $request->name ;
                $domain->focus_area=$request->focus_area;
                $domain->sub_focus_area=$request->sub_focus_area;

                $domain->save();
            return back()->with('success','Data inserted successfully!');
        }
        catch(Exception $e)
        {
            return back()->with('fail','Something went wrong!');
        }
    }


    public function viewDomain($id)
    {
        $domain = Domain::find($id);
        $levels = Level::where('domain_id',$id)->get();
        return view('admin.domain.view',[
            'domain' => $domain,
            'levels' => $levels
        ]);

    }

    public function editDomain($id)
    {
        $domain = Domain::find($id);
        return view('admin.domain.edit',[
            'domain' => $domain
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateDomain(Request $request)
    {

        //validation
 try{
     $id = $request->domain_id;
        $domain = Domain::find($id);

                $domain->name= $request->name ;
                $domain->focus_area=$request->focus_area;
                $domain->sub_focus_area=$request->sub_focus_area;

                $domain->save();
            return back()->with('success','Data Updated successfully!');
        }
        catch(Exception $e)
        {
            return back()->with('fail','Something went wrong!');
        }
    }

    public function deleteDomain($id)
    {
        Level::where('domain_id',$id)->delete();
        if(Domain::find($id)->delete())
        {
            return back()->with('success','Record deleted successfully');
        }
        else
        {
            return back()->with('error','Record cannot be deleted');
        }
    }


    // Learning Goals

    public function storeLG(Request $request)
    {

        //validation
 try{
        $lg = new LearningGoal();

                $lg->goal= $request->goal ;
                $lg->level_id=$request->level_id;

                $lg->save();
            return back()->with('success','Learning Goal inserted successfully!');
        }
        catch(Exception $e)
        {
            return back()->with('fail','Something went wrong!');
        }
    }

    public function updateLG(Request $request)
    {

        //validation
 try{
        $lg = LearningGoal::find($request->lg_id);

                $lg->goal= $request->goal ;
                $lg->level_id=$request->level_id;

                $lg->save();
            return back()->with('success','Learning Goal Updated successfully!');
        }
        catch(Exception $e)
        {
            return back()->with('fail','Something went wrong!');
        }
    }

    public function deleteLG($id)
    {
        if(LearningGoal::find($id)->delete())
        {
            return back()->with('success','Record deleted successfully');
        }
        else
        {
            return back()->with('error','Record cannot be deleted');
        }
    }

    public function getLevels(Request $request)
    {
        $levels = Level::where('domain_id',$request->id)->get();

        $output = "";

        $id = $request->quiz_id;

        $output.= "<div id='level11".$id."'><label>Levels</label><select name='level' id='level' class='form-control' onchange='levelChange(this,".$id.",".$request->id.")'>";
        foreach($levels as $level){
            $output.= "<option value='".$level->id."'>".$level->name ."</option>";
        }
        $output.= "</select></div>";

        // dd($output);
        $response = array(
            'status' => 'success',
            'msg'    => 'success',
            'data' => $output,
            'id' => $id
        );

        return response()->json($response);
    }

    public function getLGs(Request $request)
    {


        $lgs = LearningGoal::where('level_id',$request->id)->get();
        // dd($lgs);

        $output = "";

        $id = $request->quiz_id;
        $output.="<div id='remlg".$id."'><label>Learning Goals</label><br>";
        $output.="<input type='hidden' value='". $request->id ."' name='level_idd'><input type='hidden' value='". $request->domain_id ."' name='domain_idd'>";

        foreach($lgs as $lg){
            $quiz_goal = QuizLearningGoal::where('lg_id',$lg->id)->where('quiz_id',$id)->first();
            if(empty($quiz_goal)){
                $output.= "<input type='checkbox' value='". $lg->id ."' name='lgs[]'> ".$lg->goal."<br>" ;
            }else{
                $output.= "<input type='checkbox' onclick=removeQuizLGs(".$request->id.",".$id.",".$request->domain_id.",".$lg->id.") value='". $lg->id ."' name='lgs[]' checked> ".$lg->goal."<br>" ;
            }
        }
        $output.="</div>";

        // dd($output);
        $response = array(
            'status' => 'success',
            'msg'    => 'success',
            'data' => $output
        );

        return response()->json($response);
    }




    public function getLessonLevels(Request $request)
    {
        $levels = Level::where('domain_id',$request->id)->get();

        $output = "";

        $id = $request->lesson_id;
        $domain_id = $request->id;
        // dd($domain_id);
        $output.= "<div id='level1".$id."'><label>Levels</label><select name='level' id='level1' class='form-control' onchange='levelChangee(this,".$id.",".$domain_id.")'>";
        foreach($levels as $level){
            $output.= "<option value='".$level->id."'>".$level->name ."</option>";
        }
        $output.= "</select></div>";

        // dd($output);
        $response = array(
            'status' => 'success',
            'msg'    => 'success',
            'data' => $output
        );

        return response()->json($response);
    }

    public function getLessonLGs(Request $request)
    {


        $lgs = LearningGoal::where('level_id',$request->id)->get();

        $output = "";

        $id = $request->lesson_id;

        $output.="<div id='remlg1".$id."'><label>Learning Goals</label><br>";
        $output.="<input type='hidden' value='". $request->id ."' name='level_idd'><input type='hidden' value='". $request->domain_id ."' name='domain_idd'>";
        foreach($lgs as $lg){
            $lesson_goal = LessonLearningGoal::where('lg_id',$lg->id)
            ->where('lesson_id',$id)
            ->where('domain_id',$request->domain_id)
            ->where('level_id',$request->id)->first();
            if(empty($lesson_goal)){
                $output.= "<input type='checkbox' value='". $lg->id ."' name='lgs[]'> ".$lg->goal."<br>" ;
            }else{
                $output.= "<input type='checkbox' onclick=removeLessonLGs(".$request->id.",".$id.",".$request->domain_id.",".$lg->id.") value='". $lg->id ."' name='lgs[]' checked> ".$lg->goal."<br>" ;
            }
        }
        $output.="</div>";



        // dd($output);
        $response = array(
            'status' => 'success',
            'msg'    => 'success',
            'data' => $output
        );

        return response()->json($response);
    }




}

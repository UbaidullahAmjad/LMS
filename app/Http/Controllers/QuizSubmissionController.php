<?php

namespace App\Http\Controllers;

use App\Quiz;
use App\QuizQuestion;
use App\CurriculumQuiz;
use App\QuizSubmission;
use Illuminate\Http\Request;

class QuizSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // return $request->has('file');
        $req=$request->all();
        dd($req);

        // $count = $request->question_id->count();


        $array_questions_id = explode(",",$request->question_id);


        $total_questions_in_quiz = count($array_questions_id);
        $total_correct_answer = 0;
        foreach($array_questions_id as $question_id)
        {
            $question_correct_answer = QuizQuestion::findorfail($question_id);

            if($req['answer'.$question_id] == $question_correct_answer->answer)
            {
                $total_correct_answer =  $total_correct_answer + 1;
            }
        }
        $calculate_percentage = ($total_correct_answer/$total_questions_in_quiz)*100;

        $percentage = round($calculate_percentage,2);



        $req['marks'] = $percentage;

        QuizSubmission::updateOrcreate(['quiz_id'=>$request->quiz_id,'course_id'=>$request->course_id,
        'section_id'=>$request->section_id,'student_id'=>$request->student_id],$req);
        $quiz = Quiz::findorfail($request->quiz_id);
        if($percentage > $quiz->passing_grade)
        {
            return "PASS";
        }
        else{
            return "FAIL";
        }
        // return $req;
    }

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

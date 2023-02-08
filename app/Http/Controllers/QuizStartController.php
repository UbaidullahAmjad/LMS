<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QuizTopic;
use App\QuizAnswer;
use Auth;
use App\Quiz;

class QuizStartController extends Controller
{
    public function quizstart($id)
    {
    	$topic = QuizTopic::findOrFail($id);
		$answers = QuizAnswer::where('topic_id','=',$topic->topic_id)->first();
		return view('front.quiz.main_quiz', compact('topic','answers'));
    }

    public function store(Request $request, $id)
    {

    	$topics=QuizTopic::where('id',$id)->first();

        $unique_question = array_unique($request->question_id);
      
        for ($i = 1; $i <= count($request->answer); $i++) {
    
                
            $answers[] = [
                'user_id' => Auth::user()->id,
                'user_answer' => $request->answer[$i],
                'question_id' => $unique_question[$i],
                'course_id'=>$topics->course_id,
                'topic_id'=>$topics->id,
                'answer'=>$request->canswer[$i],
                'created_at'  => \Carbon\Carbon::now()->toDateTimeString(),
            ];
         
        }
        
        QuizAnswer::insert($answers);
        
        
        return redirect()->route('start.quiz.show', $id);
           
    }

    public function show($id)
	{
        $auth = Auth::user();
        $topic = QuizTopic::where('id',$id)->get();
        $questions = Quiz::where('topic_id', $id)->get();
        $count_questions = $questions->count();
        $topics=QuizTopic::where('id',$id)->first();
        $ans = QuizAnswer::where('user_id',$auth->id)
              ->where('topic_id',$id)->get(); 

        return view('front.quiz.finish', compact('auth','topic','questions','count_questions','ans','topics'));

	}

    public function tryagain($id)
    {
        QuizAnswer::where('topic_id',$id)->where('user_id', Auth::User()->id)->delete();

        return redirect()->route('start_quiz', $id);
    }


    public function subquizstart($id)
    {
        $topic = QuizTopic::findOrFail($id);
        $answers = QuizAnswer::where('topic_id','=',$topic->topic_id)->first();
        return view('front.quiz.sub_main_quiz', compact('topic','answers'));
    }

    public function sub_store(Request $request, $id)
    {

        // return $request;

        $topics=QuizTopic::where('id',$id)->first();

        $unique_question = array_unique($request->question_id);
      
        for ($i = 1; $i <= count($request->txt_answer); $i++) {
    
                
            $answers[] = [
                'user_id' => Auth::user()->id,
                // 'user_answer' => $request->answer[$i],
                'question_id' => $unique_question[$i],
                'course_id'=>$topics->course_id,
                'topic_id'=>$topics->id,
                'txt_answer' => $request->txt_answer[$i],
                'type' => '1',
                'txt_approved' => '0',
                'created_at'  => \Carbon\Carbon::now()->toDateTimeString(),
            ];
         
        }
        
        QuizAnswer::insert($answers);
        
        
        return redirect()->route('sub.start.quiz.show', $id);
           
    }

    public function sub_show($id)
    {
        $auth = Auth::user();
        $topic = QuizTopic::where('id',$id)->get();
        $questions = Quiz::where('topic_id', $id)->get();
        $count_questions = $questions->count();
        $topics=QuizTopic::where('id',$id)->first();
        $ans = QuizAnswer::where('user_id',$auth->id)
              ->where('topic_id',$id)->get(); 

        return view('front.quiz.sub_finish', compact('auth','topic','questions','count_questions','ans','topics'));

    }


    public function subtryagain($id)
    {
        QuizAnswer::where('topic_id',$id)->where('user_id', Auth::User()->id)->delete();

        return redirect()->route('sub_start_quiz', $id);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Quiz;
use App\Category;
use App\Course;
use App\User;
use App\Lesson;
use App\Models\QuizLearningGoal;
use App\QuizQuestion;
use App\QuizzQuestion;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class QuizController extends Controller
{
    // begin listing view
    public function index()
    {
        return view('admin.quizzes.listing');
    }
    // end listing view


    // begin add view
    public function add()
    {
        $getTeachers        =   User::where('role_id',2)->get();
        $getCategories      =   Category::where('parent_id',0)->get();
        $getLessons         =   Lesson::get();

        return view('admin.quizzes.add',compact('getTeachers','getCategories','getLessons'));
    }
    // end add view




    // begin store
    public function store(Request $request)
    {
        if(!empty($request->lesson))
        {
            $lessonId   =   $request->lesson;
        }
        else
        {
            $lessonId   =   0;
        }
        $addQuiz                        =   new Quiz;

        $addQuiz->title                          =   $request->title;
        $addQuiz->short_description              =   $request->short_description;
        $addQuiz->long_description               =   $request->long_description;
        $addQuiz->passing_grade                  =   $request->passing_grade;
        $addQuiz->points_cut_after_re_take       =   $request->points_cut_after_re_take;
        $addQuiz->lesson_id                      =   $lessonId;
        $addQuiz->lessons_id                     =   implode(',',$request->lessons_id);

        if($addQuiz->save())
        {
            return redirect()->back()->with('success','Quiz Added successfully');
        }
        else
        {
            return redirect()->back()->with('error','Quiz cannot be added');
        }
    }
    // end store


    // begin listing
    public function listing(Request $request)
    {
        $columns = array(
                            0 =>'id',
                            1 =>'id',
                            2 =>'title',
                            3 =>'short_description',
                            4 =>'long_description',
                            5 =>'passing_grade',
                            6 =>'points_cut_after_re_take',
                            7 =>'lesson_id',
                            8 =>'created_at',
                        );

        $totalData  =   Quiz::orderBy('id','desc');
        // if(!empty($request->parent_id))
        // {
        //     $totalData  =   $totalData->where('parent_id',$request->parent_id);
        // }
        // else
        // {
        //     $totalData  =   $totalData->where('parent_id',0);
        // }
        $totalData  =   $totalData->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $posts = Quiz::offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);

            $posts    =   $posts->get();
        }
        else
        {
            $search = $request->input('search.value');

            $posts =  Quiz::Where(function($query) use ($search)
                            {
                                $query->where('title','LIKE',"%{$search}%");
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
                            // if(!empty($request->parent_id))
                            // {
                            //     $posts  =   $posts->where('parent_id',$request->parent_id);
                            // }
                            // else
                            // {
                            //     $posts  =   $posts->where('parent_id',0);
                            // }
            $posts  =   $posts->get();

            $totalFiltered = Quiz::Where(function($query) use ($search)
                                    {
                                        $query->where('title','LIKE',"%{$search}%");
                                    });
            $totalFiltered  =   $totalFiltered->count();
        }
        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $key => $post)
            {
                $getSubCategory         =   Category::where('id',$post->sub_category_id)->first();
                if(!empty($getSubCategory))
                {
                    $subCategoryName    =   $getSubCategory['name'];
                }
                else
                {
                    $subCategoryName    =   '';
                }

                $lessons   =   array();
                $getLessons         =   Lesson::whereIn('id',explode(',',$post->lessons_id))->get();
                if(!empty($getLessons))
                {
                    foreach($getLessons    as  $lesson)
                    {
                        $lessonsName    =   $lesson->title;
                        array_push($lessons,$lessonsName);
                    }
                }
                else
                {
                    $lessonsName    =   '';
                }

                $lessonsNames   =   implode(',',$lessons);

                $totalSubCategories     =   Quiz::count();
                if(Auth::user()->role_id    ==  2)
                {

                    $leson = Lesson::find($post->lesson_id);
                    $course = null;
                    if(!empty($leson)){
                        $course = Course::where('id',$leson->course_id)->where('teacher_id', 'LIKE', '%' . Auth::user()->id . '%')->first();
                    }


                    if($course != null){
                        $edit                   =   url('/teacher/quiz/edit/'.base64_encode($post->id));
                        $view                   =   url('/teacher/quiz/view/'.base64_encode($post->id));

                        $srNo                                    =  $key+1;
                        $businessCreatedAt                       =  explode(' ',$post->created_at);
                        $nestedData['id']                        =  $srNo;
                        $nestedData['title']                     =  $post->title;
                        $nestedData['passing_grade']             =  $post->passing_grade;
                        $nestedData['points_cut_after_re_take']  =  $post->points_cut_after_re_take;
                        $nestedData['lesson_id']                 =  $lessonsNames;

                        $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                        <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                        <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>

                        </div>';
                        $data[] = $nestedData;
                    }


                }
                else
                {
                    $edit                   =   url('/admin/quiz/edit/'.base64_encode($post->id));
                    $delete                 =   url('/admin/quiz/delete/'.base64_encode($post->id));
                    $view                   =   url('/admin/quiz/view/'.base64_encode($post->id));

                    $srNo                                    =  $key+1;
                    $businessCreatedAt                       =  explode(' ',$post->created_at);
                    $nestedData['id']                        =  $srNo;
                    $nestedData['title']                     =  $post->title;
                    $nestedData['passing_grade']             =  $post->passing_grade;
                    $nestedData['points_cut_after_re_take']  =  $post->points_cut_after_re_take;
                    $nestedData['lesson_id']                 =  $lessonsNames;

                    $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
                    <a type="button" title="Edit" onclick="return myFunction()" class="btn btn-transparent btn-xs" href='.$delete.' ><i class="fa fa-times fa fa-white"></i></a>
                    </div>';
                    $data[] = $nestedData;
                }


            }
        }

        $json_data = array(
                    'dir' => $dir,
                    "draw"            => intval($request->input('draw')),
                    "recordsTotal"    => intval($totalData),
                    "recordsFiltered" => intval($totalFiltered),
                    "data"            => $data
                    );

        echo json_encode($json_data);

    }
    // end listing

    // begin edit view
    public function edit($id)
    {
        $id                                 =   base64_decode($id);

        $getQuiz                            =   Quiz::where('id',$id)->first();
        $getSelectedLesson                  =   Lesson::whereIn('id',explode(',',$getQuiz['lessons_id']))->get();
        $getLessons                         =   Lesson::whereNotIn('id',explode(',',$getQuiz['lessons_id']))->get();
        // $getSelectedSubCategory             =   Category::where('id',$getQuiz['sub_category_id'])->first();

        // $getTeachers                        =   User::where('role_id',2)->where('id','!=',$getQuiz['user_id'])->get();
        // $getCategories                      =   Category::where('parent_id',0)->where('id','!=',$getQuiz['category_id'])->get();
        // $getSubCategories                   =   Category::where('parent_id',$getQuiz['category_id'])->where('id','!=',$getQuiz['sub_category_id'])->get();
        return view('admin.quizzes.edit',compact('getQuiz','id','getSelectedLesson','getLessons'));
    }
    // end edit view


    // begin view course
    public function viewQuiz($id)
    {
        $id                     =   base64_decode($id);
        $getQuiz                =   Quiz::where('id',$id)->first();

        $lessons   =   array();
        $question_ids = array();
        $getLessons             =   Lesson::whereIn('id',explode(',',$getQuiz['lessons_id']))->get();

        $getquestions = QuizzQuestion::where('quiz_id',$id)->get();
        foreach($getquestions as $getquestion){
            array_push($question_ids,$getquestion->question_id);
        }
        $getQuizQuestion        =   QuizQuestion::all();
        $getQuestion        =   QuizzQuestion::where('quiz_id',$id)->orderby('index')->get();
        if(!empty($getLessons))
        {
            foreach($getLessons    as  $lesson)
            {
                $lessonsName    =   $lesson->title;
                array_push($lessons,$lessonsName);
            }
        }
        else
        {
            $lessonsName    =   '';
        }

        $lessonsNames   =   implode(',',$lessons);
        return view('admin.quizzes.view',compact('getQuiz','id','lessonsNames','getQuizQuestion','getQuestion','question_ids'));

    }
    // end view course

    // begin update
    public function update(Request $request)
    {
        $getQuiz    = Quiz::where('id', $request->id)->first();
        $updateQuiz = Quiz::where('id', $request->id);
        if(!empty($request->lesson))
        {
            $lessonId   =   $request->lesson;
        }
        else
        {
            $lessonId   =   0;
        }

        if($updateQuiz->update([
            'title'                     =>   $request->title,
            'short_description'         =>   $request->short_description,
            'long_description'          =>   $request->long_description,
            'passing_grade'             =>   $request->passing_grade,
            'points_cut_after_re_take'  =>   $request->points_cut_after_re_take,
            'lesson_id'                 =>   $lessonId,
            'lessons_id'                =>   implode(',',$request->lessons_id)

            ]))
        {
                return redirect()->back()->with('success','Quiz updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Quiz can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        $id     =   base64_decode($id);
        QuizzQuestion::where('quiz_id',$id)->get();
        if(Quiz::where('id',$id)->delete())
        {
            return redirect()->back()->with('success','Quiz deleted successfully');
        }
        else
        {
            return redirect()->back()->with('error','Quiz cannot be deleted');
        }
    }
    // end delete

    // begin add questions to quiz
    public function addQuestionsToQuiz(Request $request)
    {
        $getQuiz    = Quiz::find($request->quiz);
        $questions = $request->question_id;
        $max_index = QuizzQuestion::where('quiz_id',$getQuiz->id)->max('index');
        // dd($max_index);

        if(!empty($questions) && $max_index == null){
            for($i = 0; $i < count($questions); $i++){
                $q_question = new QuizzQuestion();
                $q_question->question_id = $questions[$i];
                $q_question->quiz_id = $getQuiz->id;
                $q_question->index = $i;
                $q_question->save();


            }
        }

        if(!empty($questions) && $max_index != null){
            for($i = 0; $i < count($questions); $i++){
                $q_question = new QuizzQuestion();
                $q_question->question_id = $questions[$i];
                $q_question->quiz_id = $getQuiz->id;
                $q_question->index = $max_index + 1;
                $q_question->save();
                $max_index++;


            }
        }

        return back();

    }
    // end add questions to quiz

    // begin delete questions from quiz
    public function deleteQuestionFromQuiz($id,$quiz)
    {
        QuizzQuestion::find($id)->delete();

        return back();
    }
    // end delete questions from quiz

    public function updateIndex(Request $request)
    {
        $question_a = QuizzQuestion::where('question_id',$request->id)->where('quiz_id',$request->quiz_id)->first();
        $question_b = QuizzQuestion::where('index',$request->newIndex)->where('quiz_id',$request->quiz_id)->first();

        // dd($request->newIndex);
        $question_a->index = $request->newIndex;
        $question_a->save();

        $question_b->index = $request->oldIndex;
        $question_b->save();


        $response = [
            'message' => 'success'
        ];
        return json_encode($response);
    }



    public function addLGToQuiz(Request $request){


        // dd($request->all());
        if(!empty($request->lgs)){
            for($i = 0; $i < count($request->lgs); $i++){
                $quizlg = new QuizLearningGoal();
                $quizlg->quiz_id = $request->quiz_id;
                $quizlg->level_id = $request->level_idd;
                $quizlg->domain_id = $request->domain_idd;
                $quizlg->section_id = $request->section_id;
                $quizlg->lg_id = $request->lgs[$i];

                $quizlg->save();

            }
            return back()->with('success','Learning Goals Added to Quiz Successfully');

        }

        return back()->with('error','Check atleast one checkbox of learning goals');
    }

    public function removeQuizLgs(Request $request){

        $lesson_goal = QuizLearningGoal::where('lg_id',$request->lg_id)
            ->where('quiz_id',$request->quiz_id)
            ->where('domain_id',$request->domain_id)
            ->where('level_id',$request->level_id)->delete();

            $response = [
                'data' => 'Learning Goal removed from Quiz',
            ];

            return json_encode($response);
    }

}

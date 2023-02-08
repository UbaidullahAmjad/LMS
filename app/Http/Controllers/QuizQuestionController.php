<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Quiz;
use App\QuizQuestion;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class QuizQuestionController extends Controller
{
    // begin listing view
        // public function index()
        // {
        //     return view('admin.quiz_questions.listing');
        // }
    // end listing view


    // begin add view
        // public function add()
        // {
        //     $getTeachers        =   User::where('role_id',2)->get();
        //     $getCategories      =   Category::where('parent_id',0)->get();
        //     $getLessons         =   Lesson::get();

        //     return view('admin.quiz_questions.add',compact('getTeachers','getCategories','getLessons'));
        // }
    // end add view




    // begin store
    public function store(Request $request)
    {

        $addQuizQuestion                        =   new QuizQuestion;

        $addQuizQuestion->title                 =   $request->title;
        $addQuizQuestion->description           =   $request->description;
        $addQuizQuestion->type                  =   $request->type;
        $addQuizQuestion->option1               =   $request->option1;
        $addQuizQuestion->option2               =   $request->option2;
        $addQuizQuestion->option3               =   $request->option3;
        $addQuizQuestion->option4               =   $request->option4;
        $addQuizQuestion->option5               =   $request->option5;
        $addQuizQuestion->answer                =   $request->answer;


        if($addQuizQuestion->save())
        {
            return redirect()->back()->with('success','Quiz question added successfully');
        }
        else
        {
            return redirect()->back()->with('error','Quiz question cannot be added');
        }
    }
    // end store


    // begin listing
        // public function listing(Request $request)
        // {
        //     $columns = array(
        //                         0 =>'id',
        //                         1 =>'id',
        //                         2 =>'title',
        //                         3 =>'short_description',
        //                         4 =>'long_description',
        //                         5 =>'passing_grade',
        //                         6 =>'points_cut_after_re_take',
        //                         7 =>'lesson_id',
        //                         8 =>'created_at',
        //                     );

        //     $totalData  =   Quiz::orderBy('id','desc');
        //     // if(!empty($request->parent_id))
        //     // {
        //     //     $totalData  =   $totalData->where('parent_id',$request->parent_id);
        //     // }
        //     // else
        //     // {
        //     //     $totalData  =   $totalData->where('parent_id',0);
        //     // }
        //     $totalData  =   $totalData->count();

        //     $totalFiltered = $totalData;

        //     $limit = $request->input('length');
        //     $start = $request->input('start');
        //     $order = $columns[$request->input('order.0.column')];
        //     $dir = $request->input('order.0.dir');

        //     if(empty($request->input('search.value')))
        //     {
        //         $posts = Quiz::offset($start)
        //                         ->limit($limit)
        //                         ->orderBy($order,$dir);
        //                         // if(!empty($request->parent_id))
        //                         // {
        //                         //     $posts  =   $posts->where('parent_id',$request->parent_id);
        //                         // }
        //                         // else
        //                         // {
        //                         //     $posts  =   $posts->where('parent_id',0);
        //                         // }
        //         $posts    =   $posts->get();
        //     }
        //     else
        //     {
        //         $search = $request->input('search.value');

        //         $posts =  Quiz::Where(function($query) use ($search)
        //                         {
        //                             $query->where('title','LIKE',"%{$search}%");
        //                         })
        //                         ->offset($start)
        //                         ->limit($limit)
        //                         ->orderBy($order,$dir);
        //                         // if(!empty($request->parent_id))
        //                         // {
        //                         //     $posts  =   $posts->where('parent_id',$request->parent_id);
        //                         // }
        //                         // else
        //                         // {
        //                         //     $posts  =   $posts->where('parent_id',0);
        //                         // }
        //         $posts  =   $posts->get();

        //         $totalFiltered = Quiz::Where(function($query) use ($search)
        //                                 {
        //                                     $query->where('title','LIKE',"%{$search}%");
        //                                 });
        //         $totalFiltered  =   $totalFiltered->count();
        //     }
        //     $data = array();
        //     if(!empty($posts))
        //     {
        //         foreach ($posts as $key => $post)
        //         {
        //             $getSubCategory         =   Category::where('id',$post->sub_category_id)->first();
        //             if(!empty($getSubCategory))
        //             {
        //                 $subCategoryName    =   $getSubCategory['name'];
        //             }
        //             else
        //             {
        //                 $subCategoryName    =   '';
        //             }
        //             $totalSubCategories     =   Quiz::count();
        //             $edit                   =   url('/admin/quiz/edit/'.base64_encode($post->id));
        //             $delete                 =   url('/admin/quiz/delete/'.base64_encode($post->id));
        //             $view                   =   url('/admin/quiz/view/'.base64_encode($post->id));

        //             $srNo                                    =  $key+1;
        //             $businessCreatedAt                       =  explode(' ',$post->created_at);
        //             $nestedData['id']                        =  $srNo;
        //             $nestedData['title']                     =  $post->title;
        //             $nestedData['passing_grade']             =  $post->passing_grade;
        //             $nestedData['points_cut_after_re_take']  =  $post->points_cut_after_re_take;
        //             $nestedData['lesson_id']                 =  $post->Lesson->title;
        //             // $nestedData['total_reviews']    = $post->total_reviews;
        //             // $nestedData['total_ratings']    = $post->total_ratings;
        //             // $nestedData['total_enrolled']   = $post->total_enrolled;
        //                 // $nestedData['sub_category'] = '<a href="'.$subCategories.'">'.$totalSubCategories.'</a>';

        //             // $nestedData['status'] = $post->status;
        //             // $nestedData['created'] = $businessCreatedAt[0];
        //             $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
        //             <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
        //             <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
        //             <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$delete.' ><i class="fa fa-times fa fa-white"></i></a>
        //             </div>';
        //             $data[] = $nestedData;
        //         }
        //     }

        //     $json_data = array(
        //                 'dir' => $dir,
        //                 "draw"            => intval($request->input('draw')),
        //                 "recordsTotal"    => intval($totalData),
        //                 "recordsFiltered" => intval($totalFiltered),
        //                 "data"            => $data
        //                 );

        //     echo json_encode($json_data);

        // }
    // end listing

    // begin edit view
        // public function edit($id)
        // {
        //     $id                                 =   base64_decode($id);

        //     $getQuiz                            =   Quiz::where('id',$id)->first();
        //     $getSelectedLesson                  =   Lesson::where('id',$getQuiz['lesson_id'])->first();
        //     $getLessons                         =   Lesson::where('id','!=',$getQuiz['lesson_id'])->get();
        //     return view('admin.quiz_questions.edit',compact('getQuiz','id','getSelectedLesson','getLessons'));
        // }
    // end edit view


    // begin view course
        // public function viewQuiz($id)
        // {
        //     $id                     =   base64_decode($id);
        //     $getQuiz                =   Quiz::where('id',$id)->first();
        //     return view('admin.quiz_questions.view',compact('getQuiz','id'));
        // }
    // end view course

    // begin update
    public function update(Request $request)
    {
        $getQuizQuestion    = QuizQuestion::where('id', $request->id)->first();
        $updateQuizQuestion = QuizQuestion::where('id', $request->id);
        if(!empty($request->quiz))
        {
            $quizId   =   $request->quiz;
        }
        else
        {
            $quizId   =   0;
        }

        if(!empty($request->question_bank))
        {
            $questionBankId   =   $request->question_bank;
        }
        else
        {
            $questionBankId   =   0;
        }

        if($updateQuizQuestion->update([
            'title'                 =>   $request->title,
            'description'           =>   $request->description,
            'type'                  =>   $request->type,
            'option1'               =>   $request->option1,
            'option2'               =>   $request->option2,
            'option3'               =>   $request->option3,
            'option4'               =>   $request->option4,
            'option5'               =>   $request->option5,
            'answer'                =>   $request->answer,
            'quiz_id'               =>   $quizId,
            'question_bank_id'      =>   $questionBankId
            ]))
        {
                return redirect()->back()->with('success','Quiz question updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Quiz question can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        $id     =   base64_decode($id);
        if(QuizQuestion::where('id',$id)->delete())
        {
            return redirect()->back()->with('success','Quiz question deleted successfully');
        }
        else
        {
            return redirect()->back()->with('error','Quiz question cannot be deleted');
        }
    }
    // end delete


}

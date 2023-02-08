<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Quiz;
use App\QuestionBank;
use App\User;
use App\Lesson;
use App\QuizQuestion;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class QuestionBankController extends Controller
{
        // begin listing view
        public function index()
        {

            return view('admin.question_banks.listing');
        }
        // end listing view


        // begin add view
        public function add()
        {
            return view('admin.question_banks.add');
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
            $addQuestionBank                        =   new QuestionBank;

            $addQuestionBank->name                           =   $request->name;
            if($addQuestionBank->save())
            {
                return redirect()->back()->with('success','Question bank added successfully');
            }
            else
            {
                return redirect()->back()->with('error','Question bank cannot be added');
            }
        }
        // end store


        // begin listing
        public function listing(Request $request)
        {
            $columns = array(
                                0 =>'id',
                                1 =>'id',
                                2 =>'name',
                                3 =>'created_at',
                            );

            $totalData  =   QuestionBank::orderBy('id','desc');
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
                $posts = QuestionBank::offset($start)
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
                $posts    =   $posts->get();
            }
            else
            {
                $search = $request->input('search.value');

                $posts =  QuestionBank::Where(function($query) use ($search)
                                {
                                    $query->where('name','LIKE',"%{$search}%");
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

                $totalFiltered = QuestionBank::Where(function($query) use ($search)
                                        {
                                            $query->where('name','LIKE',"%{$search}%");
                                        });
                $totalFiltered  =   $totalFiltered->count();
            }
            $data = array();
            if(!empty($posts))
            {
                foreach ($posts as $key => $post)
                {
                    if(Auth::user()->role_id    ==  2)
                    {
                        $edit                   =   url('/teacher/questionBank/edit/'.base64_encode($post->id));
                        $delete                 =   url('/teacher/questionBank/delete/'.base64_encode($post->id));
                        $view                   =   url('/teacher/questionBank/view/'.base64_encode($post->id));
                    }
                    else
                    {
                        $edit                   =   url('/admin/questionBank/edit/'.base64_encode($post->id));
                        $delete                 =   url('/admin/questionBank/delete/'.base64_encode($post->id));
                        $view                   =   url('/admin/questionBank/view/'.base64_encode($post->id));
                    }

                    $srNo                                    =  $key+1;
                    $businessCreatedAt                       =  explode(' ',$post->created_at);
                    $nestedData['id']                        =  $srNo;
                    $nestedData['name']                      =  $post->name;
                    $nestedData['created']                   =  $businessCreatedAt[0];
                    $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$view.' ><i class="fa fa-eye"></i></a>
                    <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
                    <a type="button" title="Edit" onclick="return myFunction()" class="btn btn-transparent btn-xs" href='.$delete.' ><i class="fa fa-times fa fa-white"></i></a>
                    </div>';
                    $data[] = $nestedData;
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
            $getQuestionBank                            =   QuestionBank::where('id',$id)->first();

            return view('admin.question_banks.edit',compact('getQuestionBank','id'));
        }
        // end edit view


        // begin view details
        public function view($id)
        {
            $id                     =   base64_decode($id);
            $getQuestionBank                =   QuizQuestion::all();

            return view('admin.question_banks.view',compact('getQuestionBank','id'));

        }
        // end view details

        // begin update
        public function update(Request $request)
        {
            $updateQuestionBank = QuestionBank::where('id', $request->id);
            if($updateQuestionBank->update([
                'name'                     =>   $request->name
                ]))
            {
                    return redirect()->back()->with('success','Question bank updated successfully.');
            }
            else
            {
                return redirect()->back()->with('error','Question bank can not be updated');
            }
        }
        // end update

        // begin delete
        public function delete($id)
        {
            $id     =   base64_decode($id);
            if(QuestionBank::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','Question bank deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','Question bank cannot be deleted');
            }
        }
        // end delete
}

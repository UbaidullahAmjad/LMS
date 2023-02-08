<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\Search;
use App\Question;

class SearchController extends Controller
{
    public function index(Request $request) 
    {
        
        $searchTerm = $request->input('searchTerm');


        $coursequery = Course::query();

        if(isset($searchTerm))
        {
            $search_data = collect();

            $lang =app()->getLocale();

            if($lang == 'ar' || $lang == 'ur')
            {

                $course_title = $coursequery->where('title->'.app()->getLocale(), 'LIKE', '%'.$searchTerm.'%')->get();
                 
            }
            else{
                
                 $course_title = Course::where('title', 'LIKE', "%$searchTerm%")->where('status','=',1)->get();

            }
            

            

            if (isset($course_title) && count($course_title) > 0)
            {
                
                $search_data->push($course_title);
                                

            }

            $course_tags = Course::where('level_tags', 'LIKE', "%$searchTerm%")->where('status','=',1)->get();

            if (isset($course_tags) && count($course_tags) > 0)
            {
                
                $search_data->push($course_tags);
                                

            }

            $search_data = $search_data->flatten();

        	$courses = Course::search($searchTerm)->paginate(20);
        	return view('front.search', compact('search_data', 'searchTerm'));
    	}
    	else
    	{
    		return back()->with('delete', trans('flash.NoSearch'));
    	}
        
    }

   
}

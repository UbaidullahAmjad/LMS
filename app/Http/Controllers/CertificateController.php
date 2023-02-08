<?php

namespace App\Http\Controllers;
use App\Course;
use App\Order;
use Auth;
use Redirect;
use PDF;
use Illuminate\Http\Request;
use App\CourseProgress;
use Crypt;

class CertificateController extends Controller
{
    public function show($id)
    {

        $order = Order::where('course_id', decrypt($id))->first();
    	$course = Course::where('id', decrypt($id))->first();
        $progress = CourseProgress::where('course_id', $course->id)->where('user_id', Auth::user()->id)->first();
    	return view('front.certificate.certificate', compact('course', 'order', 'progress'));
    }

    public function pdfdownload($id)
    {
        $id = Crypt::decrypt($id);

    	$course = Course::where('id', $id)->first();
        $orders = Order::where('course_id', $id)->first();
        $progress = CourseProgress::where('course_id', $course->id)->where('user_id', Auth::user()->id)->first();
        
        $pdf = PDF::loadView('front.certificate.download', compact('course', 'progress'), [], 
        [ 
          'title' => 'Certificate', 
          'orientation' => 'L'
        ]);

        return $pdf->download('certificate.pdf');
        // return $pdf->stream('certificate.pdf');
    }
}

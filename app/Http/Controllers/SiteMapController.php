<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sitemap\SitemapGenerator;

class SiteMapController extends Controller
{

	public function index()
	{
		return view('admin.sitemap.edit');
	}

    public function sitemap(Request $request)
    {
    	SitemapGenerator::create(url('/'))->writeToFile('sitemap.xml');

    	return back()->with('success',trans('flash.CreatedSuccessfully'));
    }

    public function download(Request $request)
    {
    	return response()->download( public_path('sitemap.xml'));
    }
}

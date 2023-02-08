<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();

        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $banner = new Banner();
        if ($request->hasFile('banner')) {
            $file = $request->banner;
            $name = $file->getClientOriginalName();

            $fileName = time() . $name;

            $file->move(storage_path() . '/app/public/', $fileName);
            $banner->banner =  $fileName;
        }

        $banner->save();

        return redirect('/admin/banner/index')->with('success', 'Banner Added');
    }

    public function update(Request $request)
    {
        $banner = Banner::find($request->ban);
        if ($request->hasFile('banner')) {
            $file = $request->banner;
            $name = $file->getClientOriginalName();

            $fileName = time() . $name;

            $file->move(storage_path() . '/app/public/', $fileName);
            $banner->banner =  $fileName;
        }

        $banner->save();

        return redirect('/admin/banner/index')->with('success', 'Banner Upadted');
    }


    public function delete($id)
    {
        Banner::find($id)->delete();
        return redirect('/admin/banner/index')->with('success', 'Banner Deleted');
    }
}

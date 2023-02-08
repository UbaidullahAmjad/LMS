<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CategoryController extends Controller
{
    // begin listing view
    public function index()
    {
        $id     =   0;
        return view('admin.categories.listing',compact('id'));
    }
    // end listing view

    // begin listing sub category view
    public function subCategory($id)
    {
        $id     =   base64_decode($id);
        $getParentCategory  =   Category::where('id',$id)->first();
        return view('admin.categories.subCategoryListing',compact('id','getParentCategory'));
    }
    // end listing sub category view

    // begin add view
    public function add($id)
    {
        // if($id  ==  0)
        // {

        // }
        // else
        // {
            $id                 =   base64_decode($id);
        // }
        $getCategory       =   Category::where('id',$id)->first();
        return view('admin.categories.add',compact('getCategory','id'));
    }
    // end add view

    // begin add sub category view
    public function addSubCategory($id)
    {
        $id                 =   base64_decode($id);
        $getParentCategory  =   Category::where('id',$id)->first();
        return view('admin.categories.addSubCategory',compact('id','getParentCategory'));
    }
    // end add sub category view

    // begin store
    public function store(Request $request)
    {
        $addCategory                        =   new Category;
        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/category_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $addCategory->image                 =   $filename;
            }
            else
            {
                $addCategory->image                 =   '';
            }

        }
        else
        {
            $addCategory->image                 =   '';
        }

        if( $request->hasFile('icon'))
        {
            $icon                           =   $request->file('icon');
            $path1                          =   public_path(). '/category_icons';
            $filename1                      =   $icon->getClientOriginalName().time() . '.' . $icon->getClientOriginalExtension();
            if($icon->move($path1, $filename1))
            {
                $addCategory->icon                 =   $filename1;
            }
            else
            {
                $addCategory->icon                 =   '';
            }

        }
        else
        {
            $addCategory->icon                 =   '';
        }
        if(!empty($request->parent))
        {
            $parentId   =   $request->parent;
        }
        else
        {
            $parentId   =   0;
        }

        $addCategory->name                  =   $request->category;
        $addCategory->parent_id             =   $parentId;

        if($addCategory->save())
        {
            return redirect()->back()->with('success','Category Added successfully');
        }
        else
        {
            return redirect()->back()->with('error','Category cannot be added');
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

        $totalData  =   Category::orderBy('id','desc');
        if(!empty($request->parent_id))
        {
            $totalData  =   $totalData->where('parent_id',$request->parent_id);
        }
        else
        {
            $totalData  =   $totalData->where('parent_id',0);
        }
        $totalData  =   $totalData->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $posts = Category::offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
                            if(!empty($request->parent_id))
                            {
                                $posts  =   $posts->where('parent_id',$request->parent_id);
                            }
                            else
                            {
                                $posts  =   $posts->where('parent_id',0);
                            }
            $posts    =   $posts->get();
        }
        else
        {
            $search = $request->input('search.value');

            $posts =  Category::Where(function($query) use ($search)
                            {
                                $query->where('name','LIKE',"%{$search}%");
                            })
                            ->offset($start)
                            ->limit($limit)
                            ->orderBy($order,$dir);
                            if(!empty($request->parent_id))
                            {
                                $posts  =   $posts->where('parent_id',$request->parent_id);
                            }
                            else
                            {
                                $posts  =   $posts->where('parent_id',0);
                            }
            $posts  =   $posts->get();

            $totalFiltered = Category::Where(function($query) use ($search)
                                    {
                                        $query->where('name','LIKE',"%{$search}%");
                                    });
                                    if(!empty($request->parent_id))
                                    {
                                        $totalFiltered  =   $totalFiltered->where('parent_id',$request->parent_id);
                                    }
            $totalFiltered  =   $totalFiltered->count();
        }
        $data = array();
        if(!empty($posts))
        {
            foreach ($posts as $key => $post)
            {
                $totalSubCategories     =   Category::where('parent_id',$post->id)->count();
                $edit                   =   url('/admin/category/edit/'.base64_encode($post->id));
                $delete                 =   url('/admin/category/delete/'.base64_encode($post->id));
                $subCategories          =   url('/admin/category/sub/'.base64_encode($post->id));
                $image          =   asset("public/category_images/".$post->image);
                $icon          =   asset("public/category_icons/".$post->icon);

                $srNo           =   $key+1;
                $businessCreatedAt      =   explode(' ',$post->created_at);
                $nestedData['id'] = $srNo;
                $nestedData['name'] = $post->name;
                $nestedData['image'] = '<img src="'.$image.'" style="height:80px;width:80px;">';
                $nestedData['icon'] = '<img src="'.$icon.'" style="height:80px;width:80px;">';
                $nestedData['sub_category'] = '<a href="'.$subCategories.'">'.$totalSubCategories.'</a>';

                // $nestedData['status'] = $post->status;
                $nestedData['created'] = $businessCreatedAt[0];
                $nestedData['options'] = '<div class="btn-group" role="group" aria-label="...">
                <a type="button" title="Edit" class="btn btn-transparent btn-xs" href='.$edit.' ><i class="fa fa-pencil"></i></a>
                <a type="button" title="delete" onclick="return myFunction()" class="btn btn-transparent btn-xs" href='.$delete.' ><i class="fa fa-times fa fa-white"></i></a>
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
        $id                       =   base64_decode($id);
        $getCategory              =   Category::where('id',$id)->first();
        return view('admin.categories.edit',compact('getCategory','id'));

    }
    // end edit view

    // begin update
    public function update(Request $request)
    {
        $updateCategory = Category::where('id', $request->id);
        $getCategory = Category::where('id', $request->id)->first();

        if( $request->hasFile('image'))
        {
            $image                      =   $request->file('image');
            $path                       =   public_path(). '/category_images';
            $filename                   =   $image->getClientOriginalName().time() . '.' . $image->getClientOriginalExtension();
            if($image->move($path, $filename))
            {
                $categoryImage                 =   $filename;
            }
            else
            {
                $categoryImage                 =   $getCategory['image'];
            }

        }
        else
        {
            $categoryImage                 =   $getCategory['image'];
        }

        if( $request->hasFile('icon'))
        {
            $icon                           =   $request->file('icon');
            $path1                          =   public_path(). '/category_icons';
            $filename1                      =   $icon->getClientOriginalName().time() . '.' . $icon->getClientOriginalExtension();
            if($icon->move($path1, $filename1))
            {
                $categoryIcon                 =   $filename1;
            }
            else
            {
                $categoryIcon                 =   $getCategory['icon'];
            }

        }
        else
        {
            $categoryIcon                 =   $getCategory['icon'];
        }

        if($updateCategory->update([
            // 'approved' => 1,
            'name'                  =>   $request->category,
            'icon'                  =>   $categoryIcon,
            'image'                  =>   $categoryImage
            ]))
        {
                return redirect()->back()->with('success','Category updated successfully.');
        }
        else
        {
            return redirect()->back()->with('error','Category can not be updated');
        }
    }
    // end update

    // begin delete
    public function delete($id)
    {
        // dd($request->status);
        $id     =   base64_decode($id);
        $checkSubCategories =   Category::where('parent_id',$id)->first();
        if(!empty($checkSubCategories))
        {
            return redirect()->back()->with('error','User need to delete sub categories first');
        }
        else
        {
            if(Category::where('id',$id)->delete())
            {
                return redirect()->back()->with('success','User deleted successfully');
            }
            else
            {
                return redirect()->back()->with('error','User cannot be deleted');
            }
        }
    }
    // end delete

    // begin get sub categories dropdown
    public function getSubCategoryDropdown($id)
    {
        $subCategory  = Category::where('parent_id',$id)->get();
        $count = 1;
        $result_string = '';
        foreach ($subCategory as $key => $value)
        {
            $result_string .="<option value=".$value->id.">".$value->name."</option>";
            $count++;
        }
        if(!empty($result_string))
        {
            return $result_string;
        }

        else
        {
           return 'Sub Category not found.';
        }
    }
    // end get sub categories dropdown

}

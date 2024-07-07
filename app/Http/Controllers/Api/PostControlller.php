<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Faker\Core\File;
use Illuminate\Support\Facades\Validator;

class PostControlller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();
        return response()->json([
            'status' => true,
            'message' => 'Posts fetched successfully',
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors(),
            ], 401);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            $destination = public_path('/uploads');
            $image->move($destination, $image_name);
        }

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image_name,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::find($id);
        return response()->json([
            'status' => true,
            'message' => 'Post fetched successfully',
            'data' => $data,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors(),
            ], 401);
        }

        $postImage = Post::select('id', 'image')
                    ->where(['id' => $id])->get();

        if ($request->hasFile('image')) {
            $image_path = public_path('uploads/'.$postImage[0]->image);
            
            if(file_exists($image_path)) {
                    unlink($image_path);
            }

            $image = $request->file('image');
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            $destination = public_path('/uploads');
            $image->move($destination, $image_name);
            
            }else{
                $image_name = $postImage->image;
            }

        $post = Post::where('id', $id)->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image_name,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $imagePath = Post::select('image')->where('id',$id)->get();
        $file_path = public_path('/uploads/' . $imagePath[0]->image);
        unlink($file_path);
        $post = Post::where('id', $id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully',
            'post' => $post,
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class PostController extends Controller
{
    public function store(Request $request)
    {
        try {
             // Inline validation
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'note'  => 'required|string',
            ], [
                'title.required' => 'A title is required',
                'title.max' => 'Title cannot be more than 255 characters',
                'note.required' => 'Content is required for the post',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create Post
            $post = Post::create([
                'title' => $request->title,
                'note'  => $request->note,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Post created successfully!',
                'data' => $post,
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Database error!',
                'error' => $e->getMessage(),
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(){
        return "getting post here";
    }
}

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
            //validation request
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'note' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
        } catch (QueryException $e) {
            
        } catch (\Exception $e) {
            
        }
    }
}

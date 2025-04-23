<?php 

protected $postService;

public function __construct(PostService $postService)
{
    $this->postService = $postService;
}

public function index()
{
    $post = $this->postService->getAllPosts();
    return view('posts.index', compact('posts'));
}


namespace App\Http\Controllers;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    $this->middleware('auth');
}

public function dashboard()
{
    return view('admin.dashboard');
}

public function__invoke(Post $post)
{
    return view('post.show', compact('post'))
}


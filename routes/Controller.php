<?php

//php artisan make:controller Admincontroller
//Controller with Dependency Injection
namespace App\Http\Controllers;

use App\Services\PostService;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index()
    {
        $posts = $this->postService->getAllPosts();
        return view('posts.index', compact('posts'));
    }
}


// Controller Middleware
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }
}

//Invokable Controller
//An invokable controller has only one method, and it is invoked automatically when you call the controller. This is useful for simple actions like handling single requests.
//Usage:
//Use when you only need one action (e.g., handling a form submission).

//Example:
//php artisan make:controller ShowPostController --invokable
namespace App\Http\Controllers;

use App\Models\Post;

class ShowPostController extends Controller
{
    public function __invoke(Post $post)
    {
        return view('posts.show', compact('post'));
    }
}
//Controller with Form Request Validation
//In Laravel, you can delegate validation logic from the controller to form request classes. This is particularly useful for large and complex forms.

//Usage:

//To keep your controller cleaner, delegate validation logic to a dedicated Form Request class.

//Example:
//1. First, create a form request:
//php artisan make:request StorePostRequest
//2.In StorePostRequest.php, define validation rules:
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set to false if authorization is required
    }

    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'content' => 'required',
        ];
    }
}

//3. Inject this request into your controller method:
namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function store(StorePostRequest $request)
    {
        Post::create($request->validated());
        return redirect()->route('posts.index');
    }
}

//Controller with Constructor and Dependency Injection
//Laravel allows you to inject dependencies into your controller's constructor. This is especially useful for shared services or repositories.

//Usage:

//For services like email services, logging, or repositories.

//Example

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function show($id)
    {
        $user = $this->userService->findUserById($id);
        return view('user.show', compact('user'));
    }
}

<?php

use Illuminate\Support\Facades\Route;

Route::get("user/{name?}", function($name = "Guest"){
 return "Name: " . $name;
});
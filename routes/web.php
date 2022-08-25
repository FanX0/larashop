<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------
| Web Routes       
|--------------
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::match(["GET", "POST"], "/register", function(){return redirect("/login");})->name("register");


/*--------------------------------------------------------------------------------------------------------------------------
       Karena menggunakan resource controller,maka letakkan sebelum kode route resource (diatasnya)
------------------------------------------------------------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------------------------------------------------------------------------------------
                                                                    UserController
 --------------------------------------------------------------------------------------------------------------------------------------------------------*/
// menghubungkan UserController
Route::resource("users", UserController::class);
/* --------------------------------------------------------------------------------------------------------------------------------------------------------
                                                                    CategoryController    
 --------------------------------------------------------------------------------------------------------------------------------------------------------*/
// ajax endpoint (mencari kategori berdasarkan keyword) dalam CategoryController@ajaxSearch
Route::get('/ajax/categories/search', [CategoryController::class, 'ajaxSearch']);

 //Soft delete dalam CategoryController@trash
Route::get('/categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');

// Resore dalam CategoryController@restore
Route::get('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');

// Delete permanent dalam CategoryController@deletePermanent
Route::delete('/categories/{category}/delete-permanent', [CategoryController::class, 'deletePermanent'])->name('categories.delete-permanent');
// menghubungkan CategoryController
Route::resource('categories', CategoryController::class);               
/* --------------------------------------------------------------------------------------------------------------------------------------------------------
                                                                    BookController
 --------------------------------------------------------------------------------------------------------------------------------------------------------*/

 //Restore dalam BookController@restore     dengan method post
 Route::post('/books/{book}/restore', [BookController::class, 'restore'])->name('books.restore');

// Trash dalam BookController@trash
Route::get('/books/trash', [BookController::class, 'trash'])->name('books.trash');
// menghubungkan BookController
Route::resource('books', BookController::class);

/* --------------------------------------------------------------------------------------------------------------------------------------------------------
                                                                    BookController
 --------------------------------------------------------------------------------------------------------------------------------------------------------*/


// menghubungkan OrderController
Route::resource('orders', OrderController::class);






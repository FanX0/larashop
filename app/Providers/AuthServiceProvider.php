<?php

namespace App\Providers;


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
// Mendefinisikan Gate di AuthServiceProvider
    public function boot()
    {
        $this->registerPolicies();
        //  logika untuk mengizinkan manage users
        Gate::define('manage-users', function($user){
            // array_intersect dikombinasikan dengan count kita bisa mengecek apakah $user->roles memiliki salah satu dari beberapa role yang kita cari
            //  menggunakan json_decode() karena $user->roles bertipe JSON String array
            return count(array_intersect(["ADMIN"], json_decode($user->roles)));

        });
        //  logika untuk mengizinkan manage categories
        Gate::define('manage-categories', function($user){
            return count(array_intersect(["ADMIN", "STAFF"], json_decode($user->roles)));
            });
        //  logika untuk mengizinkan manage books
        Gate::define('manage-books', function($user){
            return count(array_intersect(["ADMIN", "STAFF"], json_decode($user->roles)));    
            });
        //  logika untuk mengizinkan manage orders
        Gate::define('manage-orders', function($user){
                return count(array_intersect(["ADMIN", "STAFF"], json_decode($user->roles)));
            });
    }
}

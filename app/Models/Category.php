<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    // Jika deleted_at bernilai NULL maka kategori sedang aktif (tidak di tong sampah), sebaliknya jika field deleted_at memiliki nilai dianggap (di tong sampah)
    use SoftDeletes;

    // fitur relationship many-to-many di model category
    public function books(){
        return $this->belongsToMany('App\Models\Book');
       }
}

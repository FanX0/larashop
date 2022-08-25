<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{

    public function __construct(){
        $this->middleware(function($request, $next){
 
            if(Gate::allows('manage-categories')) return $next($request);
            abort(403, 'Anda tidak memiliki cukup hak akses');
           });
        //    Kini hanya user yang boleh lewat melalui Gate manage-categories yang bisa menggunakan CategoryController ini
        }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
// Fitur category list  Menangkap request categories 
    public function index(Request $request){
        $categories = \App\Models\Category::paginate(10);
        // menangkap request dari form filter
        $filterKeyword = $request->get('name');
        //  jika $filterKeyword memiliki nilai maka kita gunakan variable tersebut 
        if($filterKeyword){
        // untuk memfilter model Category yang akan dilempar ke view
        $categories = \App\Models\Category::where("name", "LIKE", "%$filterKeyword%")->paginate(10);
        }
        return view('categories.index', ['categories' => $categories]);
    }
   

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
// Menangkap request create categories 
    public function store(Request $request)
    {
        \Validator::make($request->all(), [
            "name" => "required|min:3|max:20",
            "image" => "required"
           ])->validate();

        // menangkap request dengan nama 'name' ke dalam variabel $name
        $name = $request->get('name');
        $new_category = new \App\Models\Category;
        $new_category->name = $name;
        // pengecekkan apakah ada request bertipe file dengan nama 'image'
        if($request->file('image')){
        //Jika ada maka kita simpan file tersebut 
        $image_path = $request->file('image')->store('category_images', 'public');
        $new_category->image = $image_path;
        }
        // mengambil nilai id dari user yang sedang login dan berikan ke field created_by
        $new_category->created_by = Auth::user()->id;
        // Slug merupakan karakter yang tidak menyalahi aturan URL Contoh:   Sepatu Olahraga    menjadi :   sepatu-olahraga
        $new_category->slug = \Str::slug($name, '-');
        // Menyimpan kategori
        $new_category->save();
        // kita redirect kembali ke halaman create category beserta dengan pesan keberhasilan
        return redirect()->route('categories.create')->with('status', 'Category successfully created');
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
// Fitur show detail category  dengan method GET
    public function show($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        return view('categories.show', ['category' => $category]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // mencari Category yang id nya bernilai sesuai dengan nilai dari $id
        $category_to_edit = \App\Models\Category::findOrFail($id);
        // lempar data tersebut sebagai variabel $category ke view categories.edit
        return view('categories.edit', ['category' => $category_to_edit]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
// Menangkap request untuk update   Dengan Method Put
    public function update(Request $request, $id){

        $category = \App\Models\Category::findOrFail($id);
            \Validator::make($request->all(), [
                "name" => "required|min:3|max:20",
                "image" => "required",
                "slug" => [
                "required",
                // (unique:nama_table) gunanya membuat field slug unique tapi kita kecualikan nilai slug yang sekarang di database
                Rule::unique("categories")->ignore($category->slug, "slug")
                ]
            ])->validate();

        // tangkap masing-masing field text
         $name = $request->get('name');
         $slug = $request->get('slug');
        //  cari Category yang sedang diedit
         $category = \App\Models\Category::findOrFail($id);
        //  berikan field-field yang diedit dengan nilai dari request yang kita tangkap
         $category->name = $name;
         $category->slug = $slug;
        // mengecek jika ada perlu mengupdate field image 
         if($request->file('image')){
             if($category->image && file_exists(storage_path('app/public/' . $category->image))){
            // mengecek apakah kategori yang diedit ini memiliki image sebelumnya di server jika ada kita hapus file tersebut
             Storage::delete('public/' . $category->name);
             }
             $new_image = $request->file('image')->store('category_images', 'public');
             $category->image = $new_image;
             }

            //  field ini diisi dengan ID dari user yang login
             $category->updated_by = Auth::user()->id;
            // mengupdate slug berdasarkan nama baru category yang disubmit dari form edit
             $category->slug = \Str::slug($name);
            //   save terhadap model Category yang sedang diedit
             $category->save();
            //  status bahwa update berhasil
             return redirect()->route('categories.edit', [$id])->with('status', 'Category successfully updated');
            
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
// Menangkap request delete
    public function destroy($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        // delete Category tersebut menggunakan method delete()
        $category->delete();
        // redirect kembali ke halaman categories list dengan pesan status bahwa delete berhasil
        return redirect()->route('categories.index') ->with('status', 'Category successfully moved to trash');
    }


// Show soft deleted category
public function trash()
    {
        // menggunakan model Category dengan method onlyTrashed() yang status nya soft delete yaitu field deleted_at nya tidak NULL
        $deleted_category = \App\Models\Category::onlyTrashed()->paginate(10);

        return view('categories.trash', ['categories' => $deleted_category]);
}

// Fitur Restore
public function restore($id)
    {
        // mencari Category kita menggunakan method eloquent withTrashed() karena kita mencari yang aktif maupun yang ada di trash / soft deleted.
        $category = \App\Models\Category::withTrashed()->findOrFail($id);
        if($category->trashed()){
        $category->restore();
        } else {
        return redirect()->route('categories.index')->with('status', 'Category is not in trash');
        }
        return redirect()->route('categories.index')->with('status', 'Category successfully restored');
    }

// Fitur Delete permanent 
public function deletePermanent($id)
    {
         // mencari Category kita menggunakan method eloquent withTrashed() karena kita mencari yang aktif maupun yang ada di trash / soft deleted.
        $category = \App\Models\Category::withTrashed()->findOrFail($id);
        // jika kategori yang akan dihapus permanent statusnya tidak di tong sampah / trashed / soft delete
        if(!$category->trashed()){
        // maka kita stop operasi hapus permanent ini dan redirect dengan pesan tidak bisa menghapus kategori yang sedang aktif
        return redirect()->route('categories.index')->with('status', 'Can not delete permanent active category');

        // jika kategori tersebut memang sedang dalam tong sampah
        } else {
        $category->forceDelete();
        // maka kita hapus secara permanent menggunakan method forceDelete() kemudian redirect kembali ke halaman dengan pesan telah di hapus permanen
        return redirect()->route('categories.index')->with('status', 'Category permanently deleted');
        }
    }

// ajax endpoint untuk kategori
public function ajaxSearch(Request $request){
        $keyword = $request->get('q');
       $categories = \App\Models\Category::where("name", "LIKE", "%$keyword%")->get();
        return $categories;
}



}
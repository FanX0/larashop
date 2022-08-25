<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Otorisasi UserController
    public function __construct(){
        $this->middleware(function($request, $next){
 
            if(Gate::allows('manage-users')) return $next($request);
            abort(403, 'Anda tidak memiliki cukup hak akses');
           });
        //    Kini resource manage user sudah memiliki otorisasi yaitu hanya boleh diaskses oleh user yang memiliki role "ADMIN".
    }




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    // melakukan injection $request agar kita bisa menggunakannya
    public function index(Request $request){

    {
    //Fitur Filter User 
        $users = \App\Models\User::paginate(10);
        // Memberi variabel status dengan nilai berasal dari request
        $status = $request->get('status');
    // Menangkap data berasal dari keyword          digunakan untuk  PENCARIAN
        $filterKeyword = $request->get('keyword');
        // check jika ada $filterKeyword maka kita query User yang emailnya memiliki sebagian dari keyword seperti ini:
        if($filterKeyword){
            if($status){
            $users = \App\Models\User::where('email', 'LIKE',"%$filterKeyword%")
                ->where('status', $status)
                ->paginate(10);
            } else {
            $users = \App\Models\User::where('email', 'LIKE',"%$filterKeyword%")
                ->paginate(10);
            }
           }       
    //Fitur Status 
        if($status){
            $users = \App\Models\User::where('status', $status)->paginate(10);
        } else {
            $users = \App\Models\User::paginate(10);
        }
        
        return view('users.index', ['users' => $users]);
       
    }
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("users.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /* validation dengan method make() untuk menerima 2 parameter 
            paramater 1 : dengan semua request dari form $request->all() bertipe array
            paramater 2 : juga bertipe array, yaitu rules dari masing-masing field yang divalidasi 
        beberapa validation rule yang telah kita gunakan
           1. required          Menandakan  field harus diisi 
           2. min:x             Menandakan  panjang minimal dari nilai field adalah x character
           3. max:x             Menandakan  panjang maximal dari nilai field adalah x character
           4. unique:table_name Menandakan  field harus unique berdasarkan data di field yang sama pada table table_name Misalnya "email" => unique:users  Artinya data email harus unique berdasarkan data yang ada di table users field email 
           5. digits_between:x,y Menandakan  nilai dari field harus berupa digits dan panjangnya antara x dan y
           6. email             Menandakan  nilai dari field harus berupa email. Kita gunakan untuk validasi email
           7. same:field_lain   Menandakan  nilai dari sebuah field harus sama dengan nilai dari field_lain
           */

        \Validator::make($request->all(),[
            "name" => "required|min:5|max:100",                     
            "username" => "required|min:5|max:20",
            "roles" => "required",
            "phone" => "required|digits_between:10,12",
            "address" => "required|min:20|max:200",
            "avatar" => "required",
            "email" => "required|email",
            "password" => "required",
            "password_confirmation" => "required|same:password" 
        ])->validate();

        $new_user = new \App\Models\User; 
        $new_user->name = $request->get('name');
        $new_user->username = $request->get('username');
        $new_user->roles = json_encode($request->get('roles'));
        $new_user->name = $request->get('name');
        $new_user->address = $request->get('address');
        $new_user->phone = $request->get('phone');
        $new_user->email = $request->get('email');
        $new_user->password = Hash::make($request->get('password'));
        
        if($request->file('avatar')){
            $file = $request->file('avatar')->store('avatars', 'public');
            $new_user->avatar = $file;
           }
        $new_user->save();
        return redirect()->route('users.create')->with('status', 'User successfully
        created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
// Mencari user dengan id tertentu 
    public function show($id)
    {
        $user = \App\Models\User::findOrFail($id);
    return view('users.show', ['user' => $user]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
// Mengambil data user yang akan diedit lalu lempar ke view ~
    public function edit($id)
    {
        // method findOrFail() bukan find() agar seandainya user tidak ditemukan, Laravel akan otomatis memberikan tampilan error model not found.
        $user = \App\Models\User::findOrFail($id);
        //jika user ditemukan, kita lempar view user.edit dengan data user yang bernilai data user yang tadi kita cari dengan findOrFail()
        return view('users.edit', ['user' => $user]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

//  Menangkap request edit dan mengupdate ke database
    public function update(Request $request, $id)
    {
        \Validator::make($request->all(), [
            "name" => "required|min:5|max:100",
            "roles" => "required",
            "phone" => "required|digits_between:10,12",
            "address" => "required|min:20|max:200",
           ])->validate();
           

        $user = \App\Models\User::findOrFail($id);
        $user->name = $request->get('name');

        // PHP Array menjadi JSON Array menggunakan fungsi json_encode() agar dapat disimpan ke database.
        $user->roles = json_encode($request->get('roles'));
        $user->address = $request->get('address');
        $user->phone = $request->get('phone');
        $user->status = $request->get('status');
        // kita cek jika terdapat request bertipe file dengan nama 'avatar'
        if($request->file('avatar')){
            // apakah user memiliki file avatar dan file tersebut di server, jika ada kita hapus file tersebut.
            if($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))){
            /*Untuk menghapus file kita gunakan \Storage::delete() 
            menggunakan 'public/' .             $user- >avatar         yang akan menghapus file di folder storage/app/public/avatars/fileavataruser.png misalnya. */
                Storage::delete('public/'.$user->avatar);
            }
            // simpan file yang diupload ke folder "avatars" dengan method store()
            $file = $request->file('avatar')->store('avatars', 'public');
            // set field 'avatar' user dengan path baru dari image yang diupload tadi
            $user->avatar = $file;
            // kita update ke database dengan method save()
            $user->save();
            // redirect ke form edit user dengan status bahwa berhasil melakukan update
            return redirect()->route('users.edit', [$id])->with('status', 'User succesfully updated');

           }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
// Menangkap request delete dan menghapus user di database 
    public function destroy($id)
        {
        // pertama kita cari user yang akan dihapus berdasarkan route parameter id
        $user = \App\Models\User::findOrFail($id);
        //  Setelah itu kita hapus user tersebut 
        $user->delete();
        //  Lalu kita redirect kembali ke halaman list user dengan pesan bahwa delete telah berhasil
        return redirect()->route('users.index')->with('status', 'User
        successfully deleted');
        }   
}

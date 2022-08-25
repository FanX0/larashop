@extends("layouts.global")
@section("title") Users list @endsection
@section("content")

{{-- form control untuk filter berdasarkan status      UserController@index  --}}
<form action="{{route('users.index')}}">
    <div class="row">
        <div class="col-md-6">
             {{-- Data yang ditangkap berasal dari apa yang diketikan oleh user terhubung ke  UserController@index--}}
            <input
                value="{{Request::get('keyword')}}"
                name="keyword"
                class="form-control"
                type="text"
                placeholder="Masukan email untuk filter..."/>
        </div>
        <div class="col-md-6">
            {{-- berdasarkan status user --}}
            <input {{Request::get('status') == 'ACTIVE' ? 'checked' : ''}} 
                value="ACTIVE"
                name="status"
                type="radio"
                class="form-control"
                id="active">
                <label for="active">Active</label>
            
            <input {{Request::get('status') == 'INACTIVE' ? 'checked' : ''}} 
                value="INACTIVE"
                name="status"
                type="radio"
                class="form-control"
                id="inactive">
                <label for="inactive">Inactive</label>
            <input
                type="submit"
                value="Filter"
                class="btn btn-primary">
        </div>
    </div>
</form>
    <br>
        {{-- Memberi alert sukses --}}
        @if(session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
        @endif 
{{-- Fitur Create  user terintegrasi dengan           UserController@create --}}
        <div class="row">
            <div class="col-md-12 text-right">
                <a href="{{route('users.create')}}" class="btn btn-primary">Create user</a>
            </div>
        </div>
    <br>

 <table class="table table-bordered">
    <thead>
        <tr>
            <th><b>Name</b></th>
            <th><b>Username</b></th>
            <th><b>Email</b></th>
            <th><b>Avatar</b></th>
            <th><b>Status</b></th>
            <th><b>Action</b></th>
            </tr>
    </thead>
 <tbody>
    @foreach($users as $user)
    <tr>
        <td>{{$user->name}}</td>
        <td>{{$user->username}}</td>
        <td>{{$user->email}}</td>
        <td>
            @if($user->avatar)
            <img src="{{asset('storage/'.$user->avatar)}}"width="70px"/> 
            @else
            N/A
            @endif
        </td>
        {{-- menampilkan status user --}}
        <td>
            {{-- Jika Aktif --}}
            @if($user->status == "ACTIVE")
            <span class="badge badge-success">
            {{$user->status}}
            </span>
            {{-- Jika Tidak --}}
            @else 
            <span class="badge badge-danger">
            {{$user->status}}
            </span>
            @endif
           </td>
      
        <td>

{{-- Fitur Edit  user terintegrasi dengan           UserController@Update --}}
            <a class="btn btn-info text-white btn-sm" href="{{route('users.edit',[$user->id])}}">Edit</a>
{{-- Fitur Detail  user terintegrasi dengan         UserController@Show --}}
            <a href="{{route('users.show', [$user->id])}}"class="btn btn-primary btn-sm">Detail</a>
{{-- Fitur delete user   terintegrasi dengan        UserController@destroy--}}
                 <form
                    {{-- Sebagai Alert jika ingin menghapus --}}
                    onsubmit="return confirm('Delete this user permanently?')"
                    class="d-inline"
                    action="{{route('users.destroy', [$user->id])}}"

                    {{-- nilai method adalah POST karena kita akan menggunakan method DELETE untuk mengirimkan request ke path /users/{user} alias named route users.destroy --}}
                    method="POST">

                    {{-- tambhkan helper @csrf agar request kita valid --}}
                    @csrf
                    <input

                    {{-- mengindikasikanbahwa form ini akan mengirimkan data menggunakan method DELETE --}}
                    type="hidden"
                    name="_method"
                    value="DELETE">
                    <input
                    type="submit"
                    value="Delete"
                    class="btn btn-danger btn-sm">
                    </form>
        </td>
    </tr>

@endforeach
</tbody>
<tfoot>
    <tr>
    <td colspan=10>
        {{$users->appends(Request::all())->links()}}
    </td>
    </tr>
   </tfoot> 
</table>
@endsection


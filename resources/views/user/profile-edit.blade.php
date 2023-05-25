@extends('user.layouts.master')
@section('title')
Admin
@endsection
@section('content')
<style>
  input[type="file"] {
    display: block;
  }

  .imageThumb {
    max-height: 75px;
    border: 2px solid;
    padding: 1px;
    cursor: pointer;
  }

  .pip {
    display: inline-block;
    margin: 10px 10px 0 0;
  }

  .remove {
    display: block;
    background: #444;
    border: 1px solid black;
    color: white;
    text-align: center;
    cursor: pointer;
  }

  .remove:hover {
    background: white;
    color: black;
  }
</style>

<div class="content-wrapper">

  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Edit User </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">User </li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          @if(session()->has('message'))
          <div class="alert alert-success final_msg">
            {{ session()->get('message') }}
          </div>
          @endif
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">User</h3>
            </div>

            <form action="{{route('profile.update')}}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="card-body">
                <div class="form-group">
                  <label for="title"> Name</label>
                  <input type="text" name="name" class="form-control" id="name" placeholder="Enter name" value="{{ $data->name }}" maxlength="50">
                  @error('name')
                  <div class="error">{{ $message }}</div>
                  @enderror
                </div>
              
                <div class="form-group">
                  <label for="title"> Email</label>
                  <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" value="{{ $data->email }}" maxlength="50">
                  @error('email')
                  <div class="error">{{ $message }}</div>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="title"> Password</label>
                  <input type="password" name="password" class="form-control" id="password" placeholder="Enter password"  maxlength="50">
                  @error('password')
                  <div class="error">{{ $message }}</div>
                  @enderror
                </div>
          <input type="hidden" name ="id" value=" {{ $data->id }} ">
                <div class="form-group">
                  <label for="title"> Confirm Password</label>
                  <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Enter confirm_password"  maxlength="50">
                  @error('password_confirmation')
                  <div class="error">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </section>

</div>

@endsection
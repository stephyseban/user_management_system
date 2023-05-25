@extends('user.layouts.master')
@section('title')
Admin
@endsection
@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />



<div class="content-wrapper">

  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Users List </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Users List</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          @if(session()->has('message'))
          <div class="alert alert-success final_msg">
            {{ session()->get('message') }}
          </div>
          @endif
          <div class="card">
            <div class="card-body">
              <table id="example1" class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Slno</th>
                    <th> Name</th>
                    <th>Email</th>
                  </tr>
                </thead>
                <tbody>
                  @if($users)
                  <?php $i = 0; ?>
                  @foreach($users as $user)
                  <?php $i++; ?>
                  <tr>
                    <td> {{$i}}</td>
                    <td> {{$user->name??''}}</td>
                    <td> {{$user->email??''}}</td>
                  </tr>
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

    </div>

  </section>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>


@endsection
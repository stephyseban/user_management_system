@extends('admin.layouts.master')
@section('title')
Admin
@endsection
@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<div class="content-wrapper">

  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Project </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Project</li>
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
            <div class="card-header" align="right">
              <a href="{{route('add.project')}}"> <button type="button" class="btn btn-success">Add</button> </a>
            </div>

            <div class="card-body">
              <table id="example2" class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Slno</th>
                    <th>Project Name</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($projects)
                  <?php $i = 0; ?>
                  @foreach($projects as $project)
                  <?php $i++; ?>
                  <tr>
                    <td> {{$i}}</td>
                    <td> {{$project->title??''}}</td>
                    <td> {{$project->getProjectCategory->category??''}}</td>
                    <td> 
                      @if(isset($project->image))
                    <img src="{{ url('storage/project-image/'.$project->image) }}"  height="120" id="old_iamge">
                                                                                           
                    @endif                                                                                        
                  </td>
                    <td>
                      <a href="{{route('edit.project',['id'=>$project->id])}}"> <button type="button" class="btn btn-info ">Edit</button> </a> &nbsp;
                      <a href="#"> <button type="button" class="btn btn-danger btnDelete" data-id="{{$project->id}}">delete</button> <a>
                    </td>
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

<script>
  $(document).ready(function() {
    $("#example2").on('click', '.btnDelete', function() {
      var id = $(this).attr('data-id');

      swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0CC27E',
        cancelButtonColor: '#FF586B',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        confirmButtonClass: 'btn btn-success mr-5',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false
      }).then(function() {

        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          url: "{{url('admin/project/delete')}}" + '/' + id,
          data: {
            'id': id
          },
          type: 'POST',

          success: function(data) {
            if (data.status == 'success') {
              swal(
                'Deleted!', data.msg, 'success'
              ).then(function() {
                table.draw();
              }), setTimeout(function() {
                location.reload();
              }, 1500);
            } else {
              swal(
                'No Deleted!', data.msg, 'failure'
              );
            }


          }
        });

      }, function(dismiss) {
        // dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'
        if (dismiss === 'cancel') {
          swal(
            'Cancelled', 'Product not Deleted :)', 'error'
          )
        }
      })

    });
  });
</script>

@endsection
@extends('layouts.app')

@section('content')

<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/logo.png') }}" alt="{{ Config::get('constants.SITE_NAME') }}" height="60" width="60">
  </div>

  @include('layouts.auth-header')

  @include('layouts.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Deceased Students - {{ $semester->name }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Deceased Students</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
           <div class="col-12">

            @if(count($deceased_students) != 0)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Deceased Students') }}</h3><br>
				 <a href="{{ url('registration/download-deceased-students') }}" class="btn btn-primary">Download List</a>
               </div>
               <!-- /.card-header -->
               <div class="card-body">

                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>Registration Number</th>
                          <th>Programme</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($deceased_students as $reg)
                   <tr>
                      <td>{{ $reg->student->first_name }} {{ $reg->student->middle_name }} {{ $reg->student->surname }}</td>
                      <td>{{ $reg->student->gender }}</td>
                      <td>{{ $reg->student->registration_number }}</td>
                      <td>{{ $reg->student->campusProgram->program->name }}</td>
                   </tr>
                 @endforeach
                   </tbody>
                  </table>
                  
               </div>
            </div>
            @endif
           </div>
          </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  @include('layouts.footer')

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection

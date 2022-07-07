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
            <h1 class="m-0">Clearance</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
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
        
        <!-- Main row -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                 <h3 class="card-title">Request Clearance Status</h3>
              </div>
              <div class="card-body">
                <table class="table table-bordered">
                   <thead>
                    <tr>
                      <th>Finance</th>
                      <th>Library</th>
                      <th>Dean of Students</th>
                      <th>HOD</th>
                    </tr>
                   </thead>
                   <tbody>
                     <tr>
                       <td>@if($clearance->finance_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>

                       <td>@if($clearance->library_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>


                       <td>@if($clearance->hostel_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>

                       <td>@if($clearance->hod_status === 0) <i class="fa fa-ban"></i> @else <i class="fa fa-check"></i> @endif</td>
                     </tr>

                   </tbody>
                 </table>
              </div>
            </div>
          </div>
          
        </div>
        <!-- /.row (main row) -->
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

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
            <h1 class="m-0">Admission Documents</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Admission Documents</a></li>
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
            <!-- general form elements -->

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Admission Documents') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
                <div class="card-body">
                  <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Document</th>
                         <th>Action</th>
                       </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><a href="{{ url('application/download-admission-letter') }}">Admission Letter</a></td>
                        <td><a href="{{ url('application/download-admission-letter') }}" class="btn btn-primary"><i class="fa fa-download"></i> Download</a></td>
                      </tr>
                      @foreach($attachments as $attachment)
                      <tr>
                        <td><a href="{{ url('application/download-attachment?id='.$attachment->id) }}">{{ $attachment->name }}</a></td>
                        <td><a href="{{ url('application/download-attachment?id='.$attachment->id) }}" class="btn btn-primary"><i class="fa fa-download"></i> Download</a></td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
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

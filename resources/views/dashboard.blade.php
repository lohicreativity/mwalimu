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
            <h1 class="m-0">Welcome, {{ $staff->title }} {{ $staff->first_name }} {{ $staff->surname }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
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
        @if(Auth::user()->hasRole('hod'))
        @if($postponements_hod_count != 0 || $special_exams_hod_count != 0)
        <div class="alert alert-warning">You have pending postponement requests</div>
        @endif
        @endif

        @if(Auth::user()->hasRole('arc'))
        @if($postponements_arc_count != 0 || $special_exams_arc_count != 0)
        <div class="alert alert-warning">You have pending postponement requests</div>
        @endif
        @if($resumptions_arc_count != 0)
        <div class="alert alert-warning">You have pending resumptions requests</div>
        @endif
        @endif

        @if(Auth::user()->hasRole('finance-officer'))
        @if(!$last_session)
        @if($postponements_count != 0 || $deceased_count != 0)
        <div class="alert alert-warning">You have new change of status cases</div>
        @endif
        @else
        @if($postponements_count != 0)
        @if($last_session->last_activity > strtotime($last_postponement->updated_at))
        <div class="alert alert-warning">You have pending postponement requests</div>
        @endif
        @endif
        @if()
        @if($last_session->last_activity > strtotime($last_deceased->updated_at))
        <div class="alert alert-warning">You have deceased cases</div>
        @endif
        @endif
        @endif
        @endif
        
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

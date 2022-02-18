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
            <h1 class="m-0">Appicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Applicants</a></li>
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
 
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Select Application Window') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/applicants/list','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                    <div class="form-group col-12">
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}">{{ $window->begin_date }} - {{ $window->end_date }} </option>
                        @endforeach
                     </select>
                   </div>
                 </div>
                   <div class="ss-form-actions">
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                   </div>
 
                  {!! Form::close() !!}
               </div>
             </div>
             <!-- /.card -->

             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Select Application Window') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  <table class="table table-bordered">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>Submission Status</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($applicants as $applicant)
                   <tr>
                      <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                      <td>{{ $applicant->gender }}</td>
                      <td>@if($applicant->submission_complete_status == 1)
                           <span class="badge badge-warning">Submitted</span>
                          @else
                           <span class="badge badge-warning">Pending</span>
                          @endif
                      </td>
                   </tr>
                 @endforeach
                   </tbody>
                  </table>

                  <div class="ss-pagination-links">
                     {!! $applicants->render() !!}
                  </div>
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

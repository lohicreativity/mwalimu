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
            <h1 class="m-0">Admit Applicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Admit Applicants</a></li>
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
                  {!! Form::open(['url'=>'application/send-admission-letter','class'=>'ss-form-processing']) !!}
                    <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Application Window') !!}
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} </option>
                        @endforeach
                     </select>
                   </div>
                   <div class="form-group col-6">
                    {!! Form::label('','Programme Level') !!}
                    <select name="program_level_id" class="form-control" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                      @endforeach
                    </select>
                  </div>
                 </div>
                   <div class="ss-form-actions">
                    <button type="submit" class="btn btn-primary">{{ __('Send Admission Letter') }}</button>
                   </div>
 
                  {!! Form::close() !!}
               </div>
             </div>
             <!-- /.card -->

            

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

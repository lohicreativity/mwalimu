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
            <h1 class="m-0">Entry Requirements</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Entry Requirements</a></li>
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
                  {!! Form::open(['url'=>'application/entry-requirements-capacity','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                    <div class="form-group col-12">
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} </option>
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
            
            @if(count($entry_requirements) != 0 && $application_window)
            <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Entry Requirements') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/entry-requirements-capacity/update','class'=>'ss-form-processing']) !!}
                    {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                  <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Capacity</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($entry_requirements as $requirement)
                  <tr>
                    <td>{{ $requirement->campusProgram->program->name }}</td>
                    <td>{{ Form::input('number','requirement_'.$requirement->id,$requirement->max_capacity,['class'=>'form-control', @if(Auth::user()->hasRole('hod'))'readonly'=>true @endif]) }}</td>
                  </tr>
                  @endforeach
                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))
                  <tr>
                    <td></td>
                    <td><button class="btn btn-primary" type="submit">Update Max Capacity</button></td>
                  </tr>
                  @endif
                  </tbody>
                </table>
                {!! Form::close() !!}
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

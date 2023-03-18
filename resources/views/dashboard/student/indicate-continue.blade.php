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
		  			@if($student->continue_status == 1)
		
		              <div class="alert alert-success col-12">
                      <h3 class="text-white" style="font-size:1vw!important;"><i class="fa fa-check-circle"></i> 
                      You have already indicated to continue to {{ $applicant->program_level_id}}. Please <a href="{{ url('application/login') }}"> click here </a> and log in using your Form IV index number and date of birth to indicate your programme selections. </h3>
                    </div>
		
			@endif
		
          <div class="col-sm-6">

            <h1 class="m-0">Indicate Continueing</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Indicate Continueing</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
     <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Indicate Continueing</h3>
              </div>
			  {!! Form::open(['url'=>'student/indicate-continue','class'=>'ss-form-processing']) !!}
              <div class="card-body">
			  {!! Form::input('hidden','student_id',$student->id) !!}
                     <div class="form-group">
                        {!! Form::label('','Select campus') !!}
                        <select name="campus_id" class="form-control" required>
                             <option value="">Select Campus</option>
                          @foreach($campuses as $campus)
                             <option value="{{ $campus->id }}" @if($campus->id == $student->applicant->campus_id) selected="selected" @endif>{{ $campus->name }}</option>
                          @endforeach
                        </select>
                     </div>
              </div>
              <div class="card-footer">
                 <button @if($student->continue_status == 1) disabled="disabled" @else type="submit" @endif class="btn btn-primary">Continue with Upper Level</button>
              </div>
              {!! Form::close() !!}
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

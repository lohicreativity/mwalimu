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
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{ __('Options Allocations') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Options Allocation') }}</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Allocate Options</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $study_academic_yr = [
                         'class'=>'form-control',
                         'placeholder'=>'',
						 'readonly'=>true,
                         'required'=>true
                     ];
                     $semster = [
                         'class'=>'form-control',
                         'placeholder'=>'',
						 'readonly'=>true,						 
                         'required'=>true
                     ];					 
                 @endphp				  
                 {!! Form::open(['url'=>'academic/allocate-options','class'=>'ss-form-processing']) !!}				 
                   <div class="row">
					 <div class="form-group col-4">
						{!! Form::label('','Academic year') !!}
						{!! Form::text('study_academic_year',$study_academic_year->academicYear->year,$study_academic_yr) !!}
					  </div>
					  <div class="form-group col-4">
						{!! Form::label('','Semester') !!}
						{!! Form::text('semester',@if($semester) {{$semster = $semester->id}} @endif {{($semester->name)}},$semster) !!}
					  </div>
					  <div class="form-group col-4">
						{!! Form::label('','Programme Level') !!}
						<select name="program_level_id" class="form-control" required>
						  <option value="">Select Programme Level</option>
						  @foreach($awards as $award)
							  @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
							  <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
							  @endif
						  @endforeach
						</select>
					  </div> 
                   </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Allocate Options') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->


          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
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

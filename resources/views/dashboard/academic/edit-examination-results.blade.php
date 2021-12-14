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
            <h1>{{ __('Edit Examination Results') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Edit Examination Results') }}</li>
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

            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Edit Examination Results') }} - {{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name }} </h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $course_work_score = [
                     'placeholder'=>'Coursework score',
                     'class'=>'form-control',
                     'readonly'=>true,
                     'required'=>true
                  ];

                  $final_score = [
                     'placeholder'=>'Final score',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $supp_score = [
                     'placeholder'=>'Supp score',
                     'class'=>'form-control'
                  ];
              @endphp
              {!! Form::open(['url'=>'academic/results/update-examination-results','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                    <div class="form-group col-4">
                      {!! Form::label('','Coursework score') !!}
                      {!! Form::text('course_work_score',$result->course_work_score,$course_work_score) !!}
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Final score') !!}
                      {!! Form::text('final_score',$result->final_score*100/$policy->final_min_mark,$final_score) !!}
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Supp score') !!}
                      {!! Form::text('supp_score',$result->supp_score,$supp_score) !!}

                      {!! Form::input('hidden','student_id',$student->id) !!}
                      {!! Form::input('hidden','exam_type',$result->exam_type) !!}

                      {!! Form::input('hidden','study_academic_year_id',$result->moduleAssignment->study_academic_year_id) !!}

                      {!! Form::input('hidden','module_assignment_id',$result->moduleAssignment->id) !!}
                    </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
              {!! Form::close() !!}
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

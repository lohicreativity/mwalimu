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
            <h1>{{ __('Assessment Results') }} - {{ $module_assignment->module->name }} - {{ $module_assignment->module->code }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignments') }}">{{ __('Module Assignment') }}</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/assessment-plans') }}">{{ __('Assessment Plans') }}</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/syllabus') }}">{{ __('Syllabus') }}</a></li>
              <li class="breadcrumb-item active">{{ __('Results') }}</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-6">

            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Upload Assessment Results') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'academic/module-assignment-result/store','files'=>true,'class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Assessment') !!}
                    <select name="assessment_plan_id" class="form-control" required>
                      <option value="">Select Assessment</option>
                      @if(!$final_upload_status)
                      @foreach($module_assignment->assessmentPlans as $plan)
                      <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                      @endforeach
                      @endif
                      @if($module_assignment->course_work_process_status == 'PROCESSED')
                      <option value="FINAL_EXAM">Final Exam</option>
                      <option value="SUPPLEMENTARY">Supplementary Exam</option>
                      @endif
                    </select>

                    {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Upload results') !!}
                    {!! Form::file('results_file',['class'=>'form-control','required'=>true]) !!}
                  </div>
                </div>

                  @if(session('non_opted_students'))
                    <div class="alert alert-danger alert-dismissible ss-messages-box" role="alert">
                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <p>The following students did not opt this module. Please, remove them from your CSV file.</p>
                      @foreach(session('non_opted_students') as $key=>$stud)
                        <p> {{ ($key+1) }}. {{ $stud->registration_number }} - {{ $stud->first_name }} {{ $stud->middle_name }} {{ $stud->surname }} </p>
                      @endforeach
                   </div><!-- end of ss-messages_box -->
                   @endif
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Upload Results') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            </div>
          <!-- /.col -->

          <div class="col-6">

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Module Results Statistics') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <p>Total Number of Students: <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/total-students') }}" target="_blank">{{ $total_students_count }}</a></p>
                 <p>Students with Coursework: <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-course-work') }}" target="_blank">{{ $students_with_coursework_count }}</a></p>
                 <p>Students with no Coursework: <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-no-course-work') }}" target="_blank">{{ $students_with_no_coursework_count }}</a></p>
                 <p>Students with Final Marks: <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-final-marks') }}" target="_blank">{{ $students_with_final_marks_count }}</a></p>
                 <p>Students with no Final Marks: <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-no-final-marks') }}" target="_blank">{{ $students_with_no_final_marks_count }}</a></p>
                 <p>Students Passed: <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-passed') }}" target="_blank">{{ $students_passed_count }}</a></p>
                 <p>Students with Supplementary: <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-supplementary') }}" target="_blank">{{ $students_with_supplemetary_count }}</a></p>
                 <p>Abscond: </p>
                  
                 {!! Form::open(['url'=>'academic/staff-module-assignment/process-course-work','class'=>'ss-form-processing']) !!}

                @if(!$final_upload_status)
                 {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}
                 <div class="ss-form-controls">
                  <button type="submit" class="btn btn-primary">{{ __('Process Course Work') }}</button>
                 </div>
                 {!! Form::close() !!}
                @endif
              </div>
              <!-- /.card-body -->
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

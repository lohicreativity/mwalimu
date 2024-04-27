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
            @php
              $module_name = str_replace(' Of ',' of ',$module_assignment->module->name);
              $module_name = str_replace(' And ',' and ',$module_name);
              $module_name = str_replace(' In ',' in ',$module_name);

            @endphp
            <h1>{{ __('Assessment Results') }} - {{ $module_name }} - {{ $module_assignment->module->code }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignments') }}">{{ __('Module Assignment') }}</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/assessment-plans') }}">{{ __('CA Components') }}</a></li>
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
           <div class="col-12">
            <div class="card card-default">
              <div class="card-header">
              <ul class="nav nav-tabs">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module/'.$module_assignment->module_id.'/download-syllabus') }}">{{ __('Module Syllabus') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/attendance') }}">{{ __('Attendance') }}</a></li>
                  @if($module->course_work_based == 1)
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/assessment-plans') }}">{{ __('CA Components') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results Management') }}</a></li>
                  @else
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results Management') }}</a></li>
                  @endif
                </ul>
              </div>
            </div>
           </div>
        </div>
        <div class="row">
          <div class="col-6">

            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Upload Assessment Results') }}</h3>
                <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/csv/download') }}" class="ss-right">Download Formatted CSV File?</a>
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
                      
                      @if($module->course_work_based == 0)
                       <option value="FINAL_EXAM">Final Exam</option>
                       @if($second_semester_publish_status)
                        <option value="SUPPLEMENTARY">Supplementary Exam</option>
                       @endif
                      @else
                        @if(!$final_upload_status)
                          @foreach($module_assignment->assessmentPlans as $plan)
                          <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                          @endforeach
                        @endif
                        @if($module_assignment->course_work_process_status == 'PROCESSED')
                          @if(!$program_results_process_status)
                          <option value="FINAL_EXAM">Final Exam</option>
                          @endif
                          @if($second_semester_publish_status)
                          <option value="SUPPLEMENTARY">Supplementary Exam</option>
                          @endif
                        @endif
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

                    @if(session('invalid_students'))
                    <div class="alert alert-danger alert-dismissible ss-messages-box" role="alert">
                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <p>The following students do not study this module. Please remove them from your CSV file.</p>
                      @foreach(session('invalid_students') as $key=>$stud)
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
                 <p>Total Number of Students: 
                    @if($total_students_count == 0)
                      {{ $total_students_count }}
                    @else 
                    <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/total-students') }}" target="_blank">
                      {{ $total_students_count }}
                    </a>
                    @endif
                </p>
                @if($module->course_work_based == 1)
                    @if($students_with_no_coursework_count > 0)
                      <p>Without Coursework: 
                      <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-no-course-work') }}" target="_blank">
                        {{ $students_with_no_coursework_count }}
                      </a>
                    </p>
                    @endif
                @endif

                @if($students_with_no_final_marks_count > 0)
                  <p>Without Final Marks: 
                    <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-no-final-marks') }}" target="_blank">
                      {{ $students_with_no_final_marks_count }}
                    </a>
                  </p>
                @endif

                @if($first_semester_publish_status || $second_semester_publish_status)
                    @if($special_exam_cases_count > 0)
                      <p>Special Exam Cases: 
                        <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-special') }}" target="_blank">
                          {{ $special_exam_cases_count }}
                        </a>
                      </p>
                    @endif

                    @if($carry_cases_count > 0)
                      <p>Carry Cases: 
                        <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-carry') }}" target="_blank">
                          {{ $carry_cases_count }}
                        </a>
                      </p>
                    @endif

                    @if($supp_cases_count > 0)
                      <p>Supplementary Cases: 
                        <a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results/students-with-supplementary') }}" target="_blank">
                          {{ $supp_cases_count }}
                        </a>
                      </p>
                    @endif
                @endif

                 @if($second_semester_publish_status)
                 <p>Without Supplementary Marks: 
                  {{ $students_with_no_supplementary_count }}
                </p>
                 @endif
                  
                 {!! Form::open(['url'=>'academic/staff-module-assignment/process-course-work','class'=>'ss-form-processing']) !!}

                @if(!$final_upload_status && $module->course_work_based == 1)
                 {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}
                 <div class="ss-form-controls">
                  <button type="submit" class="btn btn-primary">{{ __('Process Coursework') }}</button>
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

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
              $module_name = str_replace(' Of ',' of ',$module_assignment->programModuleAssignment->module->name);
              $module_name = str_replace(' And ',' and ',$module_name);
              $module_name = str_replace(' In ',' in ',$module_name);

            @endphp
            <h1>{{ __('Attendance') }} - {{ $module_name }} - {{ $module_assignment->programModuleAssignment->module->code }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignments') }}">{{ __('Module Assignment') }}</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results') }}</a></li>
              <li class="breadcrumb-item active">{{ __('CA Components') }}</li>
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
                <div class="card-header p-2">
                <ul class="nav nav-tabs">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module/'.$module_assignment->module_id.'/download-syllabus') }}">{{ __('Module Syllabus') }}</a></li>
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/attendance') }}">{{ __('Attendance') }}</a></li>
                  @if($module->course_work_based == 1)
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/assessment-plans') }}">{{ __('CA Components') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results Management') }}</a></li>
                  @else
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results Management') }}</a></li>
                  @endif
                </ul>
              </div><!-- /.card-header -->
              <!-- form start -->
            </div>



             <div class="card card-default">
                <div class="card-header">
                  <h3>Attendance</h3>
                </div>
                <div class="card-body">
                @if($module_assignment->programModuleAssignment->category == 'COMPULSORY')
            <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Programme</th>
                         <th>Streams</th>
                         <th>Groups</th>
                       </tr>
                    </thead>
                    <tbody>
                       
                          @php
                            $students_number = 0;
                            $program_name = str_replace(' Of ',' of ',$campus_program->program->name);
                            $program_name = str_replace(' And ',' and ',$program_name);
                            $program_name = str_replace(' In ',' in ',$program_name);
                          @endphp
                           @foreach($campus_program->students as $stud)
                             @if($stud->studentshipStatus)
                             @foreach($stud->registrations as $reg)
                              @if($reg->year_of_study == $module_assignment->programModuleAssignment->year_of_study && $reg->semester_id == $module_assignment->programModuleAssignment->semester_id)
                                 @php
                                   $students_number += 1;
                                 @endphp
                              @endif
                             @endforeach
                             @endif
                           @endforeach
                       <tr>
                        <td><a href="{{ url('academic/campus/campus-program/'.$campus_program->id.'/attendance?year_of_study='.$module_assignment->programModuleAssignment->year_of_study.
                        '&study_academic_year_id='.$module_assignment->programModuleAssignment->study_academic_year_id.'&semester_id='.$module_assignment->programModuleAssignment->semester_id) }}" 
                        target="_blank">{{ $program_name }} - Year {{ $module_assignment->programModuleAssignment->year_of_study }} ({{ $students_number }})</a>
                         </td>
                         <td>   

                           @foreach($campus_program->streams as $stream)
                            @if($stream->campus_program_id == $module_assignment->programModuleAssignment->campus_program_id && $stream->year_of_study == $module_assignment->programModuleAssignment->year_of_study)
                            <p class="ss-no-margin"><a href="{{ url('academic/stream/'.$stream->id.'/attendance') }}" target="_blank">Stream_{{ $stream->name }}_({{ $stream->number_of_students }})</a></p>
                            @endif

                           @endforeach
                       
                           
                               

                               
 
                      

                        </td>
                        <td>
                          @foreach($campus_program->streams as $stream)
                            
                             @foreach($stream->groups as $group)
                              <p class="ss-no-margin"><a href="{{ url('academic/group/'.$group->id.'/attendance') }}" target="_blank">Group_{{ $group->name }}_Stream_{{ $stream->name }}_({{ $group->number_of_students }})</a></p>

                            @endforeach
                           
                          @endforeach
                         </td>
                       </tr>
                    
                    </tbody>
                  </table>
                  @endif

                  <table class="table table-bordered">
                     <tr>
                       <td><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/module-attendance') }}" target="_blank">{{ __('Module Attendance') }}</a></td>
                     </tr>
                  </table>
                </div>
              </div>

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

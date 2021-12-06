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
            <h1>{{ __('Examination Results') }} - {{ $study_academic_year->academicYear->year }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Examination Results') }}</li>
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
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results') }}">{{ __('Process Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-program-results') }}">{{ __('View Programme Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-module-results') }}">{{ __('View Module Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-student-results') }}">{{ __('View Student Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules') }}">{{ __('Uploaded Modules') }}</a></li>
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <h3>Name: {{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name }}</h3>

                 @php
                    $programIds = [];
                 @endphp
                 @foreach($results as $res)
                   @php
                      $programIds[] = $res->moduleAssignment->programModuleAssignment->id;
                   @endphp
                 @endforeach
                
                 
                 @foreach($semesters as $key=>$semester)

                   @if(count($semester->remarks) != 0)
                <div class="row">
                <div class="col-12">
                 <h4 class="ss-no-margin">{{ $semester->name }}</h4>
                 <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>SN</th>
                        <th>Code</th>
                        <th>Module Name</th>
                        <th>C/Work</th>
                        <th>Final</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Remark</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $count = 1;
                      @endphp
                    @foreach($core_programs as $program)
                        @if($semester->id == $program->semester_id && !in_array($program->id,$programIds))
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $program->module->code }}</td>
                          <td>{{ $program->module->name }}</td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                        @php
                          $count += 1;
                        @endphp
                       @endif
                      @foreach($results as $result)
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->course_work_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->final_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->total_score }} @else {{ $result->supp_score }}@endif</td>
                          <td>{{ $result->grade }}</td>
                          <td>{{ $result->final_exam_remark }}</td>
                        </tr>
                          @php
                            $count += 1;
                          @endphp
                         @endif
                      @endforeach
                    @endforeach
                    @foreach($optional_programs as $program)
                        @if($semester->id == $program->semester_id && !in_array($program->id,$programIds))
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $program->module->code }}</td>
                          <td>{{ $program->module->name }}</td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                        @php
                          $count += 1;
                        @endphp
                       @endif
                     @endforeach
                    </tbody>
                 </table>
               </div>
                 <br>
                 <div class="col-6">
                 <p class="ss-bold">Summary: {{ $semester->name }}</p>
                 <table class="table table-bordered">
                   <thead>
                      <tr>
                        <th>Remark</th>
                        <th>GPA</th>
                      </tr>
                   </thead>
                   <tbody>
                      @foreach($semester->remarks as $remark)
                      <tr>
                         <td><strong>{{ $remark->remark }}</strong>
                             @if($remark->serialized) [{{ implode(', ',unserialize($remark->serialized)['supp_exams']) }}] @endif
                             @if($remark->serialized) [{{ implode(', ',unserialize($remark->serialized)['retake_exams']) }}] @endif
                             @if($remark->serialized) [{{ implode(', ',unserialize($remark->serialized)['carry_exams']) }}] @endif
                         </td>
                         <td>@if($remark->gpa) {{ bcdiv($remark->gpa,1,1) }} @else N/A @endif</td>
                      </tr>
                      @endforeach
                   </tbody>
                 </table>
               </div>
                 
                   @if($annual_remark && $key == (count($semesters)-1))
                   <div class="col-6">
                    <p class="ss-bold">Annual Remark</p>
                     <table class="table table-bordered">
                   <thead>
                      <tr>
                        <th>Remark</th>
                        <th>GPA</th>
                      </tr>
                   </thead>
                   <tbody>
                      <tr>
                        <td><strong>{{ $annual_remark->remark }}</strong></td>
                        <td>@if($annual_remark->gpa) {{ bcdiv($annual_remark->gpa,1,1) }} @else N/A @endif</td>
                       </tr>
                   </tbody>
                 </table>
                 <br>
               </div>
                   @endif
                   
                 </div>
                 @endif
                 @endforeach
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

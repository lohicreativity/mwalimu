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
            <h1>{{ __('Overall Examination Results') }} </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/results/show-student-report?registration_number='.$student->registration_number) }}">{{ __('Examination Results') }} - {{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</a></li>
              <li class="breadcrumb-item active">{{ __('Overall Examination Results') }}</li>
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
                <ul class="nav nav-tabs">
                  @can('process-examination-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Process Results') }}</a></li>
                  @endcan
                  @can('view-programme-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-program-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Programme Results') }}</a></li>
                  @endcan
                  @can('view-module-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Module Results') }}</a></li>
                  @endcan
                  @can('view-student-results')
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/results/show-student-results') }}">{{ __('Student Results') }}</a></li>
                  @endcan
                  @can('publish-examination-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  @endcan
                  @can('view-uploaded-modules')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules') }}">{{ __('Uploaded Modules') }}</a></li>
                  @endcan
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <p><a href="{{ url('academic/results/'.$student->id.'/'.$study_academic_year->id.'/'.$year_of_study.'/show-student-perfomance-report') }}">Perfomance Report</a></p>
                 <p class="ss-no-margin"><strong>Student Name:</strong> {{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name }}</p>
                 <p class="ss-no-margin"><strong>Programme:</strong> {{ $student->campusProgram->program->name }}</p>
                 <p class="ss-no-margin"><strong>Registration Number:</strong> {{ $student->registration_number }}</p>
                 <p class="ss-no-margin"><strong>Year of Study:</strong> {{ $student->year_of_study }}</p><br>

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
                       {{--
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
                       --}}
                      @foreach($results as $result)
                        @if($result->final_exam_remark == 'POSTPONED' && count($result->moduleAssignment->specialExams) == 0)

                        @else
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)
                         @php
                         $special_exam_status = false;
                            foreach($special_exams as $exam){
                              if($exam->module_assignment_id == $result->module_assignment_id){
                                $special_exam_status = true;
                                break;
                              }
                            }

                            $supp_publish_status = false;
                            foreach($publications as $publication){
                              if($publication->semester_id == $semester->id){
                                $supp_publish_status = true;
                              }
                            }
                        @endphp
                         @if($result->retakeHistory)
                           @if(count($result->retakeHistory->retakableResults) != 0)

                           @foreach($result->retakeHistory->retakableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @elseif($result->carryHistory)
                           @if(count($result->carryHistory->carrableResults) != 0)

                           @foreach($result->carryHistory->carrableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @else
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          <td>
                            @if(!$supp_publish_status)
                              @if(empty($result->course_work_score))
                              -
                              @else
                                {{ $result->course_work_score }} 
                              @endif
                            @else
                              @if($result->supp_remark != null) MM 
                              @else
                                @if(empty($result->course_work_score))
                                -
                                @else
                                  {{ $result->course_work_score }} 
                                @endif
                              @endif
                            @endif
                          </td>
                          <td @if($result->exam_type == 'APPEAL') class="ss-grey" @endif>
                            @if(!$supp_publish_status)
                              @if(empty($result->final_score) || $special_exam_status)
                              -
                              @else
                              {{ $result->final_score }} 
                              @endif
                            @else
                              @if($result->supp_remark != null)
                                @if(empty($result->supp_score))
                                -
                                @else
                                {{ $result->supp_score }} 
                                @endif
                              @else
                                @if(empty($result->final_score))
                                -
                                @else
                                {{ $result->final_score }} 
                                @endif
                              @endif 
                            @endif
                          </td>
                          <td>@if(!$supp_publish_status) 
                            @if((empty($result->course_work_score) && empty($result->final_score)) || $special_exam_status)
                              -
                            @else
                              {{ round($result->total_score) }}
                            @endif 
                        @else
                          @if($result->supp_remark != null)
                            @if(empty($result->supp_score))
                            -
                            @else
                            {{ $result->supp_score }} 
                            @endif
                          @else
                            @if(empty($result->total_score))
                            -
                            @else
                            {{ $result->total_score }} 
                            @endif

                          @endif
                        @endif</td>
                        <td>
                          @if(!empty($result->supp_remark) && !$supp_publish_status)
                            F
                          @else
                              @if(!empty($result->supp_remark) && $supp_publish_status)
                                @if($result->grade) 
                                  {{ $result->grade }}*
                                @else - @endif
                              @elseif($special_exam_status && !empty($result->final_score) && !$supp_publish_status)
                                -
                              @else
                                @if($result->grade) 
                                {{ $result->grade }} 
                                @else - @endif
                              @endif
                          @endif
                        </td>
                          <td>
                            @if(!empty($result->supp_remark) && !$supp_publish_status) FAIL 
                            @elseif($special_exam_status && !empty($result->final_score) && !$supp_publish_status) POSTPONED 
                            @elseif($result->supp_remark != null && $supp_publish_status) @if($result->supp_remark == 'RETAKE' || $result->supp_remark == 'CARRY') FAIL @else {{ $result->supp_remark }} @endif
                            @else {{ $result->final_exam_remark }} 
                            @endif</td>
                        </tr>
                          @php
                            $count += 1;
                          @endphp
                         @endif
                         @endif
                        @endif
                      @endforeach
                    @endforeach
                    @foreach($optional_programs as $program)
                       {{--
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
                       --}}
                       @foreach($results as $result)
                         @if($result->final_exam_remark == 'POSTPONED' && count($result->moduleAssignment->specialExams) == 0)

                         @else

                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)

                         @if($result->retakeHistory)
                           @if(count($result->retakeHistory->retakableResults) != 0)

                           @foreach($result->retakeHistory->retakableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @elseif($result->carryHistory)
                           @if(count($result->carryHistory->carrableResults) != 0)

                           @foreach($result->carryHistory->carrableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @else
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          @if(!$supp_publish_status)
                          @if(empty($result->course_work_score))
                          -
                          @else
                            {{ $result->course_work_score }} 
                          @endif
                        @else
                          @if($result->supp_remark != null) N/A 
                          @else
                            @if(empty($result->course_work_score))
                            -
                            @else
                              {{ $result->course_work_score }} 
                            @endif
                          @endif
                        @endif
                      </td>
                      <td @if($result->exam_type == 'APPEAL') class="ss-grey" @endif>
                        @if(!$supp_publish_status)
                          @if(empty($result->final_score) || $special_exam_status)
                          -
                          @else
                          {{ $result->final_score }} 
                          @endif
                        @else
                          @if($result->supp_remark != null)
                            @if(empty($result->supp_score))
                            -
                            @else
                            {{ $result->supp_score }} 
                            @endif
                          @else
                            @if(empty($result->final_score))
                            -
                            @else
                            {{ $result->final_score }} 
                            @endif
                          @endif 
                        @endif
                      </td>
                      <td>@if(!$supp_publish_status) 
                        @if((empty($result->course_work_score) && empty($result->final_score)) || $special_exam_status)
                          -
                        @else
                          {{ round($result->total_score) }}
                        @endif 
                    @else
                      @if($result->supp_remark != null)
                        @if(empty($result->supp_score))
                        -
                        @else
                        {{ $result->supp_score }} 
                        @endif
                      @else
                        @if(empty($result->total_score))
                        -
                        @else
                        {{ $result->total_score }} 
                        @endif

                      @endif
                    @endif</td>
                    <td>
                      @if(!empty($result->supp_remark) && !$supp_publish_status)
                        F
                      @else
                          @if(!empty($result->supp_remark) && $supp_publish_status)
                            @if($result->grade) 
                              {{ $result->grade }}*
                            @else - @endif
                          @elseif($special_exam_status && !empty($result->final_score) && !$supp_publish_status)
                            -
                          @else
                            @if($result->grade) 
                            {{ $result->grade }} 
                            @else - @endif
                          @endif
                      @endif
                    </td>
                      <td>
                        @if(!empty($result->supp_remark) && !$supp_publish_status) FAIL 
                        @elseif($special_exam_status && !empty($result->final_score) && !$supp_publish_status) POSTPONED 
                        @elseif($result->supp_remark != null && $supp_publish_status) @if($result->supp_remark == 'RETAKE' || $result->supp_remark == 'CARRY') FAIL @else {{ $result->supp_remark }} @endif
                        @else {{ $result->final_exam_remark }} 
                        @endif</td>
                        </tr>
                          @php
                            $count += 1;
                          @endphp
                         @endif
                         @endif
                        @endif
                      @endforeach
                     @endforeach
                    </tbody>
                 </table>
               </div>
                 <br>
                 <div class="col-6">
                 <p class="ss-bold">Result Summary: {{ $semester->name }}</p>
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
                        <td>@if($remark->remark != 'PASS' && $supp_publish_status) <strong>{{ $remark->supp_remark }}</strong> @else <strong>{{ $remark->remark }}</strong> @endif
                          @if($remark->serialized) 
                            @if(!$supp_publish_status) 
                              @if(!empty(unserialize($remark->serialized)['supp_exams'])) [{{ implode(', ',unserialize($remark->serialized)['supp_exams']) }}] @endif 
                            @else
                              @if(!empty(unserialize($remark->supp_serialized)['supp_exams'])) [{{ implode(', ',unserialize($remark->supp_serialized)['supp_exams']) }}] @endif 
                            @endif
                          @endif

                          @if($remark->serialized) 
                            @if(!$supp_publish_status) 
                              @if(!empty(unserialize($remark->serialized)['retake_exams'])) [{{ implode(', ',unserialize($remark->serialized)['retake_exams']) }}] @endif 
                            @else
                                @if(!empty(unserialize($remark->supp_serialized)['retake_exams'])) [{{ implode(', ',unserialize($remark->supp_serialized)['retake_exams']) }}] @endif 
                            @endif
                          @endif

                          @if($remark->serialized) 
                            @if(!$supp_publish_status) 
                              @if(!empty(unserialize($remark->serialized)['carry_exams'])) [{{ implode(', ',unserialize($remark->serialized)['carry_exams']) }}] @endif 
                            @else
                                @if(!empty(unserialize($remark->supp_serialized)['carry_exams'])) [{{ implode(', ',unserialize($remark->supp_serialized)['carry_exams']) }}] @endif 
                            @endif
                          @endif
                         </td>
                         <td>@if($remark->gpa) @if($remark->remark != 'PASS' && !$supp_publish_status) N/A @else {{ bcdiv($remark->gpa,1,1) }} @endif @else N/A @endif</td>
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

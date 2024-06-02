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
                 <h3>Examination Results</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">

                 @php
                    $programIds = [];
                 @endphp
                 @foreach($results as $res)
                   @php
                      $programIds[] = $res->moduleAssignment->programModuleAssignment->id;
                   @endphp
                 @endforeach
                
                 
                 @foreach($semesters as $key=>$semester)
                    @php
                       $publish_status = false;
                       $supp_publish_status = false;
                       $sem_reg[$semester->id] = false;
                    @endphp

                    @foreach($student->registrations as $reg)
                        @if($reg->semester_id == $semester->id)
                          @php
                            $sem_reg[$semester->id] = true;
                          @endphp
                        @endif
                    @endforeach

                   @foreach($publications as $publication)
                      @if($publication->semester_id == $semester->id)
                        @php
                          $publish_status = true;
                        @endphp
                      @endif
                      @if($publication->type == 'SUPP' && $publication->status == 'PUBLISHED')
                        @php
                          $supp_publish_status = true;
                        @endphp
                      @endif
                   @endforeach

                   @if(count($semester->remarks) != 0 && $publish_status && $sem_reg[$semester->id] == true)
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
                          @php
                            $special_exam_status = false;
                            foreach($special_exams as $exam){
                              if($exam->module_assignment_id == $result->module_assignment_id){
                                $special_exam_status = true;
                                break;
                              }
                            }
                          @endphp
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
                            @else {{ $result->final_exam_remark }} 
                            @endif</td>
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
                       @foreach($results as $result)
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)
                            @php
                              $special_exam_status = false;
                              foreach($special_exams as $exam){
                                if($exam->module_assignment_id == $result->module_assignment_id){
                                  $special_exam_status = true;
                                  break;
                                }
                              }
                            @endphp
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
                                @if(!empty($result->remark) && $supp_publish_status)
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
                            @else {{ $result->final_exam_remark }} 
                            @endif</td>

                        </tr>
                          @php
                            $count += 1;
                          @endphp
                         @endif
                      @endforeach
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
                 @else
                    <p>No Results Published for {{ $semester->name }}</p>
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

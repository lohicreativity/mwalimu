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
                       $appeal_date = now();
                    @endphp

                   @foreach($publications as $publication)
                      @if($publication->semester_id == $semester->id)
                        @php
                          $publish_status = true;
                          $appeal_date = $publication->created_at;
                        @endphp
                      @endif
                   @endforeach

                   @if(count($semester->remarks) != 0 && $publish_status)
                <div class="row">
                <div class="col-12">
                 <h4 class="ss-no-margin">{{ $semester->name }} - Appeal Deadline ({{ Carbon\Carbon::parse($appeal_date)->addDays(7)->format('Y-m-d') }})</h4>
                 <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th></th>
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
                    {!! Form::open(['url'=>'student/appeal/store','class'=>'ss-form-processing']) !!}
                    @foreach($core_programs as $program)
                      @foreach($results as $result)
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)
                         <tr>
                          <td>
                            @if(App\Domain\Academic\Models\Appeal::exists($appeals,$result))
                            {!! Form::checkbox('result_'.$result->id,$result->id,true,['disabled'=>'disabled']) !!}
                            @else
                            {!! Form::checkbox('result_'.$result->id,$result->id) !!}
                            @endif
                          </td>
                          <td>{{ $count }}</td>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->course_work_score }} @else N/A @endif</td>
                          <td @if($result->exam_type == 'APPEAL') class="ss-grey" @endif>@if(!$result->supp_processed_at) {{ $result->final_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ round($result->total_score) }} @else {{ round($result->supp_score) }}@endif</td>
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
                       @foreach($results as $result)
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)
                         <tr @if($result->exam_type == 'APPEAL') class="ss-grey" @endif>
                          <td>@if(App\Domain\Academic\Models\Appeal::exists($appeals,$result))
                            {!! Form::checkbox('result_'.$result->id,$result->id,true,['disabled'=>'disabled']) !!}
                            @else
                            {!! Form::checkbox('result_'.$result->id,$result->id) !!}
                            @endif
                          </td>
                          <td>{{ $count }}</td>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->course_work_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->final_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ round($result->total_score) }} @else {{ round($result->supp_score) }}@endif</td>
                          <td>{{ $result->grade }}</td>
                          <td>{{ $result->final_exam_remark }}</td>
                        </tr>
                          @php
                            $count += 1;
                          @endphp
                         @endif
                      @endforeach
                     @endforeach
                       <tr>
                          <td>
                        {!! Form::input('hidden','year_of_study',$year_of_study) !!}
                        {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                        <button @if(Carbon\Carbon::parse($appeal_date)->addDays(7) < now()) disabled="disabled" @else type="submit" @endif class="btn btn-primary">Appeal Results</button>
                          </td>
                      </tr>
                      {!! Form::close() !!}
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
                             @if($remark->serialized) @if(!empty(unserialize($remark->serialized)['supp_exams'])) [{{ implode(', ',unserialize($remark->serialized)['supp_exams']) }}] @endif @endif
                             @if($remark->serialized) @if(!empty(unserialize($remark->serialized)['retake_exams'])) [{{ implode(', ',unserialize($remark->serialized)['retake_exams']) }}] @endif @endif
                             @if($remark->serialized) @if(!empty(unserialize($remark->serialized)['carry_exams'])) [{{ implode(', ',unserialize($remark->serialized)['carry_exams']) }}] @endif @endif
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

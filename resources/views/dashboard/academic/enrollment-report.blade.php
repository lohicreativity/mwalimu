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
            <h1>{{ __('Enrollment Report') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Enrollment Report') }}</li>
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
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/enrollment-report','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="row">
                   <div class="form-group col-6">
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
                  <div class="form-group col-6">
                    {!! Form::label('','Year of Study') !!}
                    <select name="year_of_study" class="form-control" required>
                       <option value="">Select Year of Study</option>
                       @for($i = 1; $i <= 3; $i++)
                       <option value="{{ $i }}">{{ $i }}</option>
                       @endfor
                    </select>
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
             
             <div class="card">
              <div class="card-header">
                <h3 class="card-title">Enrollment Report</h3><br>
                @if(count($students) > 0)
                  @if($request->get('program_level_id') == 4)<a href="{{ url('academic/submit-enrolled-students?program_level_id='.$request->get('program_level_id').'&year_of_study='.$request->get('year_of_study')) }}" class="btn btn-primary">Submit Enrolled Students</a> @endif
                  <a href="{{ url('academic/download-enrolled-students?program_level_id='.$request->get('program_level_id').'&year_of_study='.$request->get('year_of_study')) }}" class="btn btn-primary">Download Enrolled Students</a>
                @endif
                @if($request->get('program_level_id') == 4)<a href="{{ url('academic/download-submitted-enrolled-students?program_level_id='.$request->get('program_level_id').'&year_of_study='.$request->get('year_of_study')) }}" class="btn btn-primary">Donwload Submitted Students</a> @endif
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/enrollment-report','method'=>'GET']) !!}
                 {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                 {!! Form::input('hidden','year_of_study',$request->get('year_of_study')) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for student name or registration number">
                 <select name="campus_program_id" class="form-control">
                   <option value="">Select Programme</option>
                   @foreach($campus_programs as $program)
                   <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                   @endforeach
                 </select>
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!}
                  <table class="table table-bordered ss-margin-top">
                     <thead>
                       <tr>
                         <th>SN</th>
                         <th>Registration#</th>
                         <th>F4 Index#</th>
                         <th>First Name</th>
                         <th>Surname</th>
                         <th>Sex</th>
                         <th>Date of Birth</th>
                         <th>Year of Study</th>
                         <th>Is Repeat</th>
                         <th>Sponsorship</th>
                         <th>Enrollment Year</th>
                         <th>Award Name</th>
                       </tr>
                     </thead>
                     <tbody>
                       @foreach($students as $key=>$student)
                       @php
                          $f4indexno = $f6indexno = [];
                          
                          foreach($student->applicant->nectaResultDetails as $detail){
                              if($detail->exam_id == 1){
                                  $f4indexno[] = $detail->index_number;
                              }
                          }

                          $f4indexno = count($f4indexno) > 0? $f4indexno : $student->applicant->index_number;

                          if(is_array($f4indexno)){
                              $f4indexno=implode(', ',$f4indexno);
                          }
                       @endphp
                         <tr>
                          <td>{{ ($key + 1) }}</td>
                          <td>{{ $student->registration_number }}</td>
                          <td>{{ $f4indexno }}</td>
                          <td>{{ $student->first_name }}</td>
                          <td>{{ $student->surname }}</td>
                          <td>{{ $student->gender }}</td>
                          <td>{{ $student->applicant->birth_date }}</td>
                          <td>{{ $student->year_of_study }}</td>
                          @php
                          $is_year_repeat = 'NO';
                           foreach($student->annualRemarks as $remark){
                                 if($remark->year_of_study == $student->year_of_study){
                                    if($remark->remark == 'CARRY' || $remark->remark == 'RETAKE'){
                                       $is_year_repeat = 'YES';
                                    }
                                 }
                           }
                           @endphp
                          <td>{{ $is_year_repeat }}</td>
                          <td>Private</td>
                          <td>{{ $student->registration_year}}/{{($student->registration_year + 1) }}</td>
                          <td>{{ $student->campusProgram->code }}</td>
                         </tr>
                      @endforeach
                     </tbody>
                  </table>

                  <div class="ss-pagination-links">
                     {!! $students->appends($request->except('page'))->render() !!}
                  </div>
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

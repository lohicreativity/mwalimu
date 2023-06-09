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
                    {!! Form::label('','NTA Level') !!}
                    <select name="nta_level_id" class="form-control" required>
                       <option value="">Select NTA Level</option>
                       @foreach($nta_levels as $level)
                       <option value="{{ $level->id }}">{{ $level->name }}</option>
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
                <a href="{{ url('academic/submit-enrolled-students?nta_level_id='.$request->get('nta_level_id').'&year_of_study='.$request->get('year_of_study')) }}" class="btn btn-primary">Submit Enrolled Students</a> 
                <a href="{{ url('academic/download-enrolled-students?nta_level_id='.$request->get('nta_level_id').'&year_of_study='.$request->get('year_of_study')) }}" class="btn btn-primary">Download Enrolled Students</a>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/enrollment-report','method'=>'GET']) !!}
                 {!! Form::input('hidden','nta_level_id',$request->get('nta_level_id')) !!}
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
                         <th>Registration Number</th>
                         <th>First Name</th>
                         <th>Surname</th>
                         <th>Gender</th>
                         <th>Date of Birth</th>
                         <th>Year of Study</th>
                         <th>Is Year Repeat</th>
                         <th>Sponsorship</th>
                         <th>Enrollment Year</th>
                         <th>Award Name</th>
                       </tr>
                     </thead>
                     <tbody>
                       @foreach($students as $student)
                         <tr>
                          <td>{{ $student->registration_number }}</td>
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
                          <td>{{ $student->applicant->admission_year }}</td>
                          <td>{{ $student->campusProgram->program->name }}</td>
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

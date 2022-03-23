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
                <a href="{{ url('academic/submit-enrolled-students?nta_level_id='.$request->get('nta_level_id').'&year_of_study='.$request->get('year_of_study')) }}"
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                  <table class="table table-bordered">
                     <thead>
                       <tr>
                         <th>First Name</th>
                         <th>Middle Name</th>
                         <th>Surname</th>
                         <th>Gender</th>
                         <th>Nationality</th>
                         <th>Date of Birth</th>
                         <th>Award Category</th>
                         <th>Field Specialization</th>
                         <th>Year of Study</th>
                         <th>Study Mode</th>
                         <th>Is Year Repeat</th>
                         <th>Entry Qualification</th>
                         <th>Sponsorship</th>
                         <th>Enrollment Year</th>
                         <th>Phyical Challenges</th>
                         <th>F4 Index Number</th>
                         <th>Award Name</th>
                         <th>Registration Number</th>
                         <th>Institution Code</th>
                         <th>Programme Code</th>
                         <th>Status</th>
                       </tr>
                     </thead>
                     <tbody>
                       @foreach($students as $student)
                         <tr>
                          <td>{{ $student->first_name }}</td>
                          <td>{{ $student->middle_name }}</td>
                          <td>{{ $student->surname }}</td>
                          <td>{{ $student->gender }}</td>
                          <td>{{ $student->applicant->nationality }}</td>
                          <td>{{ $student->applicant->birth_date }}</td>
                          <td>{{ $student->campusProgram->program->award->name }}</td>
                          <td>
                             @php
                             foreach($student->campusProgram->program->departments as $dpt){
                                if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                                    $department = $dpt;
                                }
                             }
                             @endphp
                            {{ $department->name }}</td>
                          <td>{{ $student->year_of_study }}</td>
                          <td>{{ $student->study_mode }}</td>
                          <td>NO</td>
                          <td>{{ $student->applicant->entry_mode }}</td>
                          <td>Private</td>
                          <td>{{ $student->applicant->admission_year }}</td>
                          <td>{{ $student->applicant->disabilityStatus->name }}</td>
                          <td>{{ $student->applicant->index_number }}</td>
                          <td>{{ $student->campusProgram->program->name }}</td>
                          <td>{{ $student->registration_number }}</td>
                          <td>{{ substr($student->campusProgram->regulator_code,0,2) }}</td>
                          <td>{{ $student->campusProgram->regulator_code }}</td>
                          <td>@if($student->year_of_study == 1) FRESHER @else CONTINUING @endif</td>
                         </tr>
                      @endforeach
                     </tbody>
                  </table>

                  <div class="ss-pagination-links">
                     {!! $students->render() !!}
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

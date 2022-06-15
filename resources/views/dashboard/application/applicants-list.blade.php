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
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Applicants List</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Applicants List</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
           <div class="col-12">
 
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Select Application Window') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/applicants/list','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                    <div class="form-group col-12">
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} - {{ $window->campus->name }} - {{ $window->intake->name }} </option>
                        @endforeach
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

            @if($application_window)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Select Application Window') }}</h3><br>
                 <a href="{{ url('application/download-applicants-list?duration='.$request->get('duration').'&status='.$request->get('status').'&department_id='.$request->get('department_id').'&gender='.$request->get('gender').'&nta_level_id='.$request->get('nta_level_id').'&campus_program_id='.$request->get('campus_program_id').'&application_window_id='.$request->get('application_window_id')) }}" class="btn btn-primary">Download Applicants List</a>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {{-- {!! Form::open(['url'=>'application/applicants/list','method'=>'GET']) !!}

                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                  <div class="input-group">
                   <select name="department_id" class="form-control">
                      <option value="">Select Department</option>
                      @foreach($departments as $department)
                      <option value="{{ $department->id }}">{{ $department->name }}</option>
                      @endforeach
                   </select>
                   <select name="nta_level_id" class="form-control">
                      <option value="">Select NTA Level</option>
                      @foreach($nta_levels as $level)
                      <option value="{{ $level->id }}">{{ $level->name }}</option>
                      @endforeach
                   </select>
                   <select name="campus_program_id" class="form-control">
                      <option value="">Select Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                   </select>
                   <select name="gender" class="form-control">
                      <option value="">Select Gender</option>
                      <option value="M">Male</option>
                      <option value="F">Female</option>
                   </select>
                   <select name="status" class="form-control">
                      <option value="">Select Status</option>
                      <option value="progress">On Progress</option>
                      <option value="completed">Completed</option>
                      <option value="submitted">Submitted</option>
                   </select>
                   <select name="duration" class="form-control">
                      <option value="">Select Duration</option>
                      <option value="today">Today</option>
                      <option value="all">All</option>
                   </select>
                   <span class="input-group-btn">
                     <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                   </span>
                  </div>
                  {!! Form::close() !!} --}}

                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>@if($applicant->submission_complete_status == 1) Programmes @else Phone @endif</th>
                          <th>Submission Status</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($applicants as $applicant)
                   <tr>
                      <td><a href="#" data-toggle="modal" data-target="#ss-progress-{{ $applicant->id }}">{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</a></td>
                      <td>{{ $applicant->gender }}</td>
                      <td>@if($applicant->submission_complete_status == 1)
                            @foreach($applicant->selections as $selection)
                            <p class="ss-font-xs">{{ $selection->campusProgram->program->name }}</p>
                            @endforeach
                          @else
                            {{ $applicant->phone }}
                          @endif
                      </td>
                      <td>@if($applicant->submission_complete_status == 1)
                           <span class="badge badge-success">Submitted</span>
                          @elseif($applicant->programs_complete_status == 1 && $applicant->submission_complete_status == 0)
                           <span class="badge badge-info">Completed</span>
                          @else
                           <span class="badge badge-warning">On Progress</span>
                          @endif
                      </td>
                   </tr>
                 @endforeach
                   </tbody>
                  </table>

                @foreach($applicants as $applicant)
                    <div class="modal fade" id="ss-progress-{{ $applicant->id }}">
                        <div class="modal-dialog">
                          <div class="modal-content modal-lg">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Progress</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                               <table class="table table-bordered">
                                  <tr>
                                    <td>1. Basic Information</td>
                                    <td>@if($applicant->basic_info_complete_status == 1) <i class="fa fa-check"></i> @endif</td>
                                  </tr>
                                  <tr>
                                    <td>2. Next of Kin</td>
                                    <td>@if($applicant->next_of_kin_complete_status == 1) <i class="fa fa-check"></i> @endif</td>
                                  </tr>
                                  <tr>
                                    <td>3. Payments</td>
                                    <td>@if($applicant->payments_complete_status == 1) <i class="fa fa-check"></i> @endif</td>
                                  </tr>
                                  <tr>
                                    <td>4. Results</td>
                                    <td>@if($applicant->results_complete_status == 1) <i class="fa fa-check"></i> @endif</td>
                                  </tr>
                                  <tr>
                                    <td>5. Programmes Selection</td>
                                    <td>@if($applicant->programs_complete_status == 1) <i class="fa fa-check"></i> @endif</td>
                                  </tr>
                               </table>
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
                    @endforeach

                  <div class="ss-pagination-links">
                     {!! $applicants->appends($request->except('page'))->render() !!}
                  </div>
               </div>
            </div>
            @endif
           </div>
          </div>
      </div><!-- /.container-fluid -->
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

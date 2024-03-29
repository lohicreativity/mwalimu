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
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))   
                    <div class="form-group col-12">
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} - {{ $window->campus->name }} - {{ $window->intake->name }} </option>
                        @endforeach
                     </select>
                   </div>
                   @else
                   <div class="form-group col-12">
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                          @if($staff->campus_id == $window->campus_id)
                        <option value="{{ $window->id }}" @if($window->status == 'ACTIVE') selected="selected"@endif>{{ $window->begin_date }} - {{ $window->end_date }} - {{ $window->campus->name }} - {{ $window->intake->name }} </option>
                          @endif
                        @endforeach
                     </select>
                   </div>




                   @endif
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
                  <div class="row">
                    <div class="col-md-4">
                      <a href="{{ url('application/download-applicants-list?duration='.$request->get('duration').'&status='.$request->get('status').'&department_id='.$request->get('department_id').'&gender='.$request->get('gender').'&nta_level_id='.$request->get('nta_level_id').'&campus_program_id='.$request->get('campus_program_id').'&application_window_id='.$request->get('application_window_id')) }}" class="btn btn-primary">Download Applicants List</a>
                    </div>
                    <div class="col-md-4">
                      <label class="float-right mt-2">Status</label>
                    </div>
                    <div class="col-md-4">
                      <select id="applicant_status" class="form-control">
                        <option value="progress" @if($request->get('status') == 'progress') selected @endif>Progress</option>
                        <option value="completed" @if($request->get('status') == 'completed') selected @endif>Completed</option>
                        <option value="submitted" @if($request->get('status') == 'submitted') selected @endif>Submitted</option>
                        <option value="total" @if($request->get('status') == 'total') selected @endif>Total</option>
                      </select>
                    </div>
                  </div>
                  
                  
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
                          <th>SN</th>
                          <th>Index#</th>						  
                          <th>Name</th>
                          <th>Sex</th>
                          <th>Phone#</th>
                          <th>Award</th>
                          <th>Batch#</th>
                          <th>Application Status</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($applicants as $key=>$applicant)
                   <tr>
					  <td> {{ ($key+1) }} </td>
					  <td> {{ $applicant->index_number }}
                      <td><a href="#" data-toggle="modal" data-target="#ss-progress-{{ $applicant->id }}">{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</a></td>
                      <td>{{ $applicant->gender }}</td>
                      <td>{{ $applicant->phone }}</td>
                      <td>
                        @if(!empty($applicant->programLevel->code))
                        {{ $applicant->programLevel->code }}
                        @else
                        N/A
                        @endif
                      </td>
                      <td>@foreach($batches as $batch) @if($batch->id == $applicant->batch_id){{ $batch->batch_no }} @break @endif @endforeach</td>
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
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content modal-lg">
                            <div class="modal-header">
                              <h5 class="modal-title"><i class="fa fa-exclamation-sign"></i>{{ $applicant->first_name }} {{ $applicant->surname }} | {{ $applicant->index_number }} | {{ $applicant->programLevel->name }} | {{ $applicant->entry_mode }}</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

                              <div class="accordion" id="accordionExample-2">

                                <div class="card">
                                  <div class="card-header" id="ss-basic-information">
                                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#" aria-expanded="true" aria-controls="collapseBasicInformation">
                                        1. Basic Information
                                        @if($applicant->basic_info_complete_status == 1) <i class="fa fa-check float-right"></i> @endif
                                      </button>
                                  </div>
                                </div>

                                <div class="card">
                                  <div class="card-header" id="ss-next-of-kin">
                                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#" aria-expanded="true" aria-controls="collapseNextOfKin">
                                        2. Next Of Kin
                                        @if($applicant->next_of_kin_complete_status == 1) <i class="fa fa-check float-right"></i> @endif
                                      </button>
                                  </div>
                                </div>
                                
                                <div class="card">
                                  <div class="card-header" id="ss-payments-complete">
                                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#" aria-expanded="true" aria-controls="collapsePayments">
                                        3. Payments
                                        @if($applicant->payment_complete_status == 1) <i class="fa fa-check float-right"></i> @endif
                                      </button>
                                  </div>
                                </div>

                                <div class="card">
                                  <div class="card-header" id="ss-results-complete">
                                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#" aria-expanded="true" aria-controls="collapseResults">
                                        4. Results
                                        @if($applicant->results_complete_status == 1) <i class="fa fa-check float-right"></i> @endif
                                      </button>
                                  </div>
                                </div>

                                <div class="card">
                                  <div class="card-header" id="ss-programmes-selections">
                                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseProgrammeSelection" aria-expanded="true" aria-controls="collapseProgrammeSelection">
                                        5. Programmes Selection
                                        @if($applicant->programs_complete_status == 1) <i class="fa fa-check float-right"></i> @endif
                                      </button>
                                  </div>

                                  <div id="collapseProgrammeSelection" class="collapse" aria-labelledby="ss-programmes-selection" data-parent="#accordionExample-2">
                                    <div class="card-body">
                                      
                                    @if(count($applicant->selections) > 0)
                                      @foreach($applicant->selections as $selection)

                                        @if($selection->order == 1)
                                        <p>{{ $selection->campusProgram->code }} - 1st choice </p>                                        
                                        @elseif($selection->order == 2)
                                        <p>{{ $selection->campusProgram->code }} - 2nd choice </p>
                                        @elseif($selection->order == 3)
                                        <p>{{ $selection->campusProgram->code }} - 3rd choice </p>
                                        @else
                                        <p>{{ $selection->campusProgram->code }} - 4th choice </p> 
                                        @endif

                                      @endforeach
                                    @endif


                                    </div>
                                  </div>
                                  
                                </div>

                                
                              </div>

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
                    <div class="float-right ss-pagination-links"> {!! $applicants->appends($request->except('page'))->render() !!} </div>

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

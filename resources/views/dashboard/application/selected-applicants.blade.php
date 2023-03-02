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
            <h1 class="m-0">Selected Applicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Selected Applicants</a></li>
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
                  {!! Form::open(['url'=>'application/selected-applicants','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Application Window') !!}
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} </option>
                        @endforeach
                     </select>
                   </div>
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
                 <h3 class="card-title">{{ __('Selected Applicants') }}</h3><br>
                 <a href="{{ url('application/selected-applicants/download?application_window_id='.$request->get('application_window_id').'&program_level_id='.$request->get('program_level_id').'&campus_program_id='.$request->get('campus_program_id').'&nta_level_id='.$request->get('nta_level_id').'&gender='.$request->get('gender')) }}" class="btn btn-primary">Download Selected Applicants</a>

                 <!-- <a href="{{ url('application/submit-selected-applicants?application_window_id='.$request->get('application_window_id').'&program_level_id='.$request->get('program_level_id')) }}" class="btn btn-primary">Submit Selected Students</a> -->
                 @if($request->get('program_level_id') == 4 && $application_window->enrollment_report_download_status == 1) 
                 <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-submit-applicants">Submit Applicants to TCU</a>
                 <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-select-program">Retrieve Applicants from TCU</a>
                 <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-select-program-confirmed">Retrieve Confirmed Applicants from TCU</a>
                 @elseif(($request->get('program_level_id') == 1 || $request->get('program_level_id') == 2) && $application_window->enrollment_report_download_status == 1)
                 <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-submit-applicants">Submit Selected Applicants to NACTE</a>
                 <a href="{{ url('application/get-nacte-applicants?program_level_id='.$request->get('program_level_id').'&application_window_id='.$request->get('application_window_id')) }}" class="btn btn-primary">Retrieve Verified Applicants from NACTE</a>
                 @endif
               </div>

               <div class="modal fade" id="ss-select-program">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title"> Select Programme</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    {!! Form::open(['url'=>'application/retrieve-applicants-tcu']) !!}
                    {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                    {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                    
                    <div class="form-group">
                    {!! Form::label('','Select programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                        <option value="">Select Programme</option>
                        @foreach($campus_programs as $program)
                        <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                        @endforeach
                    </select>
                  </div>

                    <div class="ss-form-actions">
                      <button type="submit" class="btn btn-primary">Get Applicants Status (TCU)</button>
                    </div>
                    {!! Form::close() !!}
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

            <div class="modal fade" id="ss-select-program-confirmed">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title"> Select Programme</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    {!! Form::open(['url'=>'application/retrieve-confirmed-applicants-tcu']) !!}
                    {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                    {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                    
                    <div class="form-group">
                    {!! Form::label('','Select programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                        <option value="">Select Programme</option>
                        @foreach($confirmed_campus_programs as $program)
                        <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                        @endforeach
                    </select>
                  </div>

                    <div class="ss-form-actions">
                      <button type="submit" class="btn btn-primary">Get Confirmed Applicants (TCU)</button>
                    </div>
                    {!! Form::close() !!}
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

               <div class="modal fade" id="ss-submit-applicants">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title"> Submit Applicants</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    {!! Form::open(['url'=>'application/submit-selected-applicants-tcu']) !!}
                    {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                    {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                    <table id="ss-submit-selected-applicants" class="table table-bordered ss-margin-top">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>Programme</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                  
                 @foreach($selected_applicants as $applicant)
                   <tr>
                      <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                      <td>{{ $applicant->gender }}</td>
                      <td>@foreach($applicant->selections as $selection)
                           @if($selection->status == 'APPROVING')
                           {{ $selection->campusProgram->program->name }}
                           @endif
                          @endforeach
                      </td>
                      <td>@if(App\Domain\Application\Models\ApplicantSubmissionLog::containsApplicant($submission_logs,$applicant->id))
                        <span class="badge badge-success">Submitted</span>
                      @else
                        <span class="badge badge-warning">Not Submitted</span>
                      @endif
                      </td>
                      <td>
                        @if(App\Domain\Application\Models\ApplicantSubmissionLog::containsApplicant($submission_logs,$applicant->id))
                        {!! Form::checkbox('applicant_'.$applicant->id,$applicant->id,null,['disabled'=>'disabled']) !!}
                        @else
                        {!! Form::checkbox('applicant_'.$applicant->id,$applicant->id,true) !!}
                        @endif
                      </td>
                   </tr>
                 @endforeach
                   
                   </tbody>
                  </table>
                  @if($request->get('program_level_id') == 4)
                  <button type="submit" class="btn btn-primary">Submit To TCU</button>
                  @else
                  <button type="submit" class="btn btn-primary">Submit To NACTE</button>
                  @endif
                  {!! Form::close() !!}
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
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/selected-applicants','method'=>'GET']) !!}

                  {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                  {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                  <!-- <div class="input-group">
                   <input type="text" name="query" placeholder="Search for applicant name" class="form-control">
                   <select name="nta_level_id" class="form-control">
                      <option value="">Select NTA Level</option>
                      @foreach($nta_levels as $level)
                      <option value="{{ $level->id }}">{{ $level->name }}</option>
                      @endforeach
                   </select>
                   <select name="campus_program_id" class="form-control ss-select-tags-">
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
                   <span class="input-group-btn">
                     <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                   </span>
                  </div> -->
                  {!! Form::close() !!}

                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>#</th>
                          <th>Name</th>
						  <th>Form IV Index No.</th>
						    @if($request->get('program_level_id') != 1)
								@if($request->get('program_level_id') == 2)
									<th>NACTE Reg. No./F4 Index No.</th>
								@else
									<th>F6 Index No./AVN</th>
								@endif
							@endif
                          <th>Phone</th>
                          <th>Gender</th>
                          <th>Programme</th>
                          <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($applicants as $applicant)
                   <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                      <td>{{ $applicant->index_number }}</td>
					  @if($request->get('program_level_id') != 1)
						
						<td>@foreach($applicant->nectaResultDetails as $detail)
								@if($detail->exam_id == 2) 
									{{ $detail->index_number }} 
								@endif
							@endforeach <br>
							@foreach($applicant->nacteResultDetails as $detail)
								{{ $detail->avn }}
							@endforeach
						</td>
					  @endif
					  <td>{{ $applicant->phone }}</td>
                      <td>{{ $applicant->gender }}</td>
                      <td>@foreach($applicant->selections as $selection)
							@if($selection->status != 'ELIGIBLE' && $applicant->status == 'SELECTED')
								{{ $selection->campusProgram->program->code }}
								@if($selection->order == 1)
									(1st Choice)
								@elseif($selection->order == 2)
									(2nd Choice)
								@elseif($selection->order == 3)
									(3rd Choice)
								@elseif($selection->order == 4)
									(4th Choice)
								@endif
								@break
							@elseif($applicant->status == null)
								@if($applicant->selections[count($applicant->selections) - 1]->order != null)
									{{ $selection->campusProgram->program->code }}
								@else
									{{ $selection->campusProgram->program->code }},
								@endif
							@endif
                          @endforeach
                      </td>
                      <td>
                        @if($applicant->status == 'SELECTED')
							@foreach($applicant->selections as $selection)
								@if($selection->status == 'SELECTED' || $selection->status == 'APPROVING')
									@if($selection->status == 'SELECTED')
									<span class="badge badge-success">
										@if($selection->status == 'APPROVING') PRE-SELECTED 
										@else {{ $selection->status }} 
										@endif 
										@if($applicant->multiple_admissions == 1)*
										@endif
									</span>
									@else
									<span class="badge badge-warning">
										@if($selection->status == 'APPROVING') PRE-SELECTED 
										@else {{ $selection->status }} 
										@endif
									</span>
									@endif
								@endif
							@endforeach
                        @elseif($applicant->status == null)
                        <span class="badge badge-danger">NOT SELECTED</span>
                        @endif
                      </td>
                   </tr>
                 @endforeach
                   </tbody>
                  </table>

                
               </div>
            </div>

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

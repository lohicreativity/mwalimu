@extends('layouts.app')

@section('content')

<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/logo.png') }}" alt="{{ Config::get('constants.SITE_NAME') }}" height="60" width="60">
  </div>
  <script src="{{ asset('js/script.js') }}"></script>
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
          @if(count($applicants) > 0)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Selected Applicants') }}</h3><br><br>

                    <a href="{{ url('application/selected-applicants/download?application_window_id='.$request->get('application_window_id').'&program_level_id='.$request->get('program_level_id').'&campus_program_id='.$request->get('campus_program_id').'&nta_level_id='.$request->get('nta_level_id').'&gender='.$request->get('gender')) }}" class="btn btn-primary">Download Selected Applicants</a>
                 @endif
                 @if(Auth::user()->hasRole('admission-officer'))
                 
                    @if($request->get('program_level_id') == 4 && $application_window->enrollment_report_download_status == 1) 
                      @if(count($selected_applicants) > 0) <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-submit-applicants">Submit Selected Applicants to TCU</a> @endif
                      @if($submission_status > 0) 
                        <a href="{{ url('application/submit-selected-applicants-tcu/download?application_window_id='.$request->get('application_window_id').'&program_level_id='.$request->get('program_level_id')) }}" class="btn btn-primary">Download Submitted Applicants</a> 
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-select-program">Retrieve Applicants from TCU</a> 
                      @endif
                      @if($confirmation_status)
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-select-program-confirmed">Retrieve Confirmed Applicants from TCU</a>
                      @endif

                    @elseif(($request->get('program_level_id') == 1 || $request->get('program_level_id') == 2) && $application_window->enrollment_report_download_status == 1)
                      @if(count($selected_applicants) > 0)
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-submit-applicants">Submit Selected Applicants to NACTVET</a>
                      @endif  
                      @if($submission_status > 0)
                        <a href="{{ url('application/get-nacte-applicants?program_level_id='.$request->get('program_level_id').'&application_window_id='.$request->get('application_window_id')) }}" class="btn btn-primary">Retrieve Verified Applicants from NACTVET</a>
                      @endif
                    @endif
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
                          <th>SN</th>
                          <th>Name</th>
                          <th>Sex</th>
                          <th>Index#</th>
                          <th>Programme</th>
                          <th>Batch#</th>
                          <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                  
                 @foreach($selected_applicants as $key=>$applicant)
                    @if($applicant->status != 'SUBMITTED')
                      <tr>
                          <td>{{ ($key+1) }}</td>
                          <td>{{ $applicant->first_name }} {{ substr($applicant->middle_name,0,1) }}. {{ $applicant->surname }}</td>
                          <td>{{ $applicant->gender }}</td>
                          <td> 
                            @foreach($applicant->nectaResultDetails as $key => $result)
                              @if($result->exam_id == 1 && $result->verified == 1)
                                {{ $result->index_number }} @if($key > 0)<br> @endif
                              @endif
                            @endforeach
                          </td>
                          <td>@foreach($applicant->selections as $selection)
                              @if($selection->status == 'APPROVING')
                              {{ $selection->campusProgram->code }} @break
                              @endif
                              @endforeach
                              @if($applicant->status == 'NOT SELECTED') N/A @endif
                          </td>
                          <td> @foreach($batches as $batch)
                                  @if($batch->id == $applicant->batch_id)
                                      {{ $batch->batch_no}}
                                      @break
                                  @endif
                                @endforeach
                          </td>
                          <td>
                            @if(App\Domain\Application\Models\ApplicantSubmissionLog::containsApplicant($submission_logs,$applicant->id))
                            {!! Form::checkbox('applicant_ids[]',$applicant->id,null,['disabled'=>'disabled']) !!}
                            @else
                            {!! Form::checkbox('applicant_ids[]',$applicant->id,true) !!}
                            @endif
                          </td>
                      </tr>
                    @endif
                 @endforeach
                   
                   </tbody>
                  </table>
                  @if($request->get('program_level_id') == 4)
                  <button type="submit" class="btn btn-primary">Submit to TCU</button>
                  @else
                  <button type="submit" class="btn btn-primary">Submit to NACTVET</button>
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
            @if(count($applicants) > 0)
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/selected-applicants','method'=>'GET']) !!}

                  {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                  {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                  {!! Form::close() !!}

                  <table id="hello" class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>SN</th>
                          <th>Name</th>
                          <th>F4 Index#</th>
                            @if($request->get('program_level_id') != 1)
                              @if($request->get('program_level_id') == 2)
                                <th>NACTVET Reg#/F6 Index#</th>
                              @else
                                <th>F6 Index#/AVN</th>
                              @endif
                            @endif
                          <th>Batch#</th>							
                          <th>Phone</th>
                          <th>Sex</th>
                          <th>Programme</th>
                          <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $counter = 1; @endphp
                    @foreach($applicants as $applicant)

                      @foreach($applicant->selections as $selection)

                        @if($selection->status != 'ELIGIBLE' && ($applicant->status == 'SELECTED' || $applicant->status == 'SUBMITTED'))
                          <tr>
                              <td>{{ $counter++ }}</td>
                              <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                              <td>@php $index_no = $applicant->index_number; @endphp {{ $index_no }}
                                <br>
                                   @if(count($applicant->nectaResultDetails)> 1)
                                       @foreach($applicant->nectaResultDetails as $key=>$detail)
                                                @if($detail->exam_id == 1 && $detail->verified == 1)
                                                  @if($detail->index_number != $index_no)
                                                        {{ $detail->index_number }} @if($key>0)<br> @endif
                                                  @endif
                                                @endif
                                         @endforeach
                                   @endif
                              </td>
                              @if($request->get('program_level_id') != 1)	
                              <td>
                                  @if($applicant->entry_mode == 'EQUIVALENT')
                                      @foreach($applicant->nacteResultDetails as $detail)
                                        @if($detail->verified == 1)
                                          @if(!empty($detail->avn)) {{ $detail->avn }} @else {{ $detail->registration_number }} @endif
                                        @endif
                                      @endforeach <br>
                                      @foreach($applicant->nectaResultDetails as $detail)
                                        @if($detail->exam_id == 2 && $detail->verified == 1) 
                                          {{ $detail->index_number }} 
                                        @endif
                                      @endforeach
                                  @else
                                      @foreach($applicant->nectaResultDetails as $detail)
                                        @if($detail->exam_id == 2 && $detail->verified == 1) 
                                          {{ $detail->index_number }} 
                                        @endif
                                      @endforeach <br>
                                      @foreach($applicant->nacteResultDetails as $detail)
                                        @if($detail->verified == 1)
                                          @if(!empty($detail->avn)) {{ $detail->avn }} @else {{ $detail->registration_number }} @endif
                                        @endif
                                      @endforeach
                                  @endif
                              </td>
                              @endif
                              <td>@foreach($batches as $batch) @if($batch->id == $applicant->batch_id) {{ $batch->batch_no }} @break @endif @endforeach</td>
                              <td>{{ $applicant->phone }}</td>
                              <td>{{ $applicant->gender }}</td>
                              <td>{{ $selection->campusProgram->code }}
                                  
                                  @if($selection->order == 1)
                                    (1st Choice)
                                  @elseif($selection->order == 2)
                                    (2nd Choice)
                                  @elseif($selection->order == 3)
                                    (3rd Choice)
                                  @elseif($selection->order == 4)
                                    (4th Choice)
                                  @endif
                              
                              </td>
                              <td>
                                @if($applicant->status == 'SELECTED' || $applicant->status == 'SUBMITTED')
                                  @if($selection->status == 'SELECTED' || $selection->status == 'APPROVING')
                                      @if($selection->status == 'SELECTED')
                                        @if(!str_contains($applicant->admission_confirmation_status, 'OTHER'))
                                          <span class="badge badge-success"> {{ $selection->status }} @if($applicant->multiple_admissions == 1)** @endif </span> <br>
                                        @else
                                          <span class="badge badge-danger"> {{ $selection->status }} @if($applicant->multiple_admissions == 1)** @endif </span> <br>
                                        @endif
                                        @if($applicant->admission_confirmation_status == 'CONFIRMED' || $applicant->confirmation_status == 'CONFIRMED')
                                          <span style="font-style: italic; font-color:green">Confirmed</span>
                                        @elseif(str_contains($applicant->admission_confirmation_status, 'OTHER') || str_contains($applicant->confirmation_status, 'OTHER'))
                                          <span class="text-sm" style="font-style: italic; font-color:green">Confirmed Elsewhere</span>
                                        @else
                                          <span class="text-sm" style="font-style: italic; font-color:green">Retrieved from the Regulator</span>
                                        @endif
                                      @else
                                        <span class="badge badge-warning"> PRE-SELECTED </span> <br>
                                        @if($applicant->status == 'SUBMITTED')
                                          <span class="text-sm" style="font-style: italic; font-color:green">Submitted to the Regulator</span>
                                        @else
                                          <span class="text-sm" style="font-style: italic; font-color:red">Awaiting Submission</span>
                                        @endif
                                      @endif
                                  @else
                                      <span class="badge badge-danger"> AWAITING APPROVAL </span> <br>   
                                      <span class="text-sm" style="font-style: italic; font-color:green">Submitted to the Regulator</span>                                 
                                  @endif  
                                @endif
                              </td>
                          </tr>

                        @elseif($applicant->status == null || $applicant->status == "NOT SELECTED")
                        <tr>
                          <td>{{ $counter }}</td>
                          <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                          <td>{{ $applicant->index_number }}</td>
                          <td>
                            @if($request->get('program_level_id') != 1)	
                                @if($applicant->entry_mode == 'EQUIVALENT')
                                    @foreach($applicant->nacteResultDetails as $detail)
                                        @if($detail->verified == 1)
                                          @if(!empty($detail->avn)) {{ $detail->avn }} @else {{ $detail->registration_number }} @endif
                                        @endif
                                    @endforeach <br>

                                    @foreach($applicant->nectaResultDetails as $detail)
                                        @if($detail->exam_id == 2 && $detail->verified == 1) 
                                          {{ $detail->index_number }} 
                                        @endif
                                    @endforeach
                                @else
                                    @foreach($applicant->nectaResultDetails as $detail)
                                        @if($detail->exam_id == 2 && $detail->verified == 1) 
                                          {{ $detail->index_number }} 
                                        @endif
                                    @endforeach <br>

                                    @foreach($applicant->nacteResultDetails as $detail)
                                        @if($detail->verified == 1)
                                          @if(!empty($detail->avn)) {{ $detail->avn }} @else {{ $detail->registration_number }} @endif
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                          </td>
                          <td>@foreach($batches as $batch) @if($batch->id == $applicant->batch_id){{ $batch->batch_no }} @break @endif @endforeach</td>
                          <td>{{ $applicant->phone }}</td>
                          <td>{{ $applicant->gender }}</td>
                          <td>     
                            @if(count($applicant->selections) > 0)
                                @php $total_selections = $batch_id = 0;
                                    foreach($applicant->selections as $y){
                                      foreach($batches as $batch){
                                        if($batch->id == $applicant->batch_id){
                                          $batch_id = $batch->id;
                                        }
                                      }
                                      if($y->batch_id == $batch_id){
                                        $total_selections++;
                                      }
                                    }
                                    $x = $total_selections; 
                                    $y = false;
                                    foreach($submission_logs as $submission_log){
                                      if($applicant->batch_id == $submission_log->batch_id && $applicant->batch_id != $batch_id){
                                          $y = true;
                                      }
                                    }
                                @endphp
                                @foreach($applicant->selections as $select)
                                    @if($select->batch_id == $batch_id)
                                      @if($total_selections > 1)
                                        @php --$x ; @endphp
                                        {{ $select->campusProgram->code }}@if($x != 0), @endif
                                      @else
                                        {{ $select->campusProgram->code }}
                                      @endif
                                    @endif
                                @endforeach
                            @endif
                          </td>
                            @if($applicant->status == null)
                              <td><span class="badge badge-warning">AWAITING SELECTION</span>  </td>
                            @else
                              <td>
                                <span class="badge badge-danger">NOT SELECTED</span>  <br>
                                @if($applicant->program_level_id == 4 )
                                    @if($applicant->status == 'SUBMITTED')
                                      <span class="text-sm" style="font-style: italic; font-color:green">Submitted to the Regulator</span>
                                    @else
                                      @if($y)
                                          <span class="text-sm" style="font-style: italic; font-color:red">Awaiting Submission</span>
                                      @else
                                          <span class="text-sm" style="font-style: italic; font-color:red">Retrieved from the Regulator</span>
                                      @endif
      
                                    @endif
                                @endif
                              </td>
                            @endif
                        </tr>
                          
                    @php $counter++; @endphp
                    @break
                    
                    @endif
                    @endforeach
                @endforeach  
                </tbody>
              </table>

              <div class="float-right ss-pagination-links"> {!! $applicants->appends($request->except('page'))->render() !!} </div>
                @endif
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
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
            <h1 class="m-0">List of Failed Submission Cases</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">List of Failed Submission Cases</a></li>
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
                  <div class="row">
                    <div class="col-md-4">
                      <a href="{{ url('application/download-applicants-list?duration='.$request->get('duration').'&status='.$request->get('status').'&department_id='.$request->get('department_id').'&gender='.$request->get('gender').'&nta_level_id='.$request->get('nta_level_id').'&campus_program_id='.$request->get('campus_program_id').'&application_window_id='.$request->get('application_window_id')) }}" class="btn btn-primary">Download Applicants List</a>
                    </div>
                  </div>
                  
                  
               </div>
               <!-- /.card-header -->
               <div class="card-body">

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
                          <th>Stage</th>
                          <th>Reason</th>
                          <th>Action</th>
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
                      <td>@if($applicant->status == null)
                            Add to TCU
                          @else Submit Selected
                          @endif
                      </td>
                      <td> {{ $applicant->pushed_reason }} </td>
                      <td> </td>
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

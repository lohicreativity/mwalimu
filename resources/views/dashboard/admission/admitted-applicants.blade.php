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
            <h1 class="m-0">Admitted Applicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Admitted Applicants</a></li>
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
                  {!! Form::open(['url'=>'application/admitted-applicants','class'=>'ss-form-processing','method'=>'GET']) !!}
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
                 <h3 class="card-title">{{ __('Admitted Applicants') }}</h3><br>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {{--
                  {!! Form::open(['url'=>'application/admitted-applicants','method'=>'GET']) !!}

                  {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                  {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                  <div class="input-group">
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
                  </div>
                  {!! Form::close() !!} --}}
                  <br>

                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>#</th>
                          <th>Name</th>
						  <th>Form IV Index No.</th>
						  <th>Form VI Index No./AVN</th>
                          <th>Phone</th>
                          <th>Gender</th>
                          <th>Programme</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($applicants as $applicant)
                   <tr>
                      <td>{{ $loop->iteration }}</td>
					  <td><a href="#" data-toggle="modal" data-target="#ss-progress-{{ $applicant->id }}">{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</a></td>					  
					  <td>{{ $applicant->index_number }}</td>
					  <td>@foreach($applicant->nectaResultDetails as $detail)
					              @if($detail->exam_id == 2) {{ $detail->index_number }} @endif
						  @endforeach <br>
						  @foreach($applicant->nacteResultDetails as $detail)
					        {{ $detail->avn }}
						  @endforeach
					  </td>
                      <td>{{ $applicant->phone }}</td>
                      <td>{{ $applicant->gender }}</td>
                      <td>@foreach($applicant->selections as $selection)
                           @if($selection->status == 'SELECTED')
                           {{ $selection->campusProgram->program->code }}
                           @endif
                          @endforeach
                      </td>
                   </tr>
                 @endforeach
                   </tbody>
                  </table>

                  
               </div>
            </div>

			     @foreach($applicants as $applicant)
                    <div style="margin-top:20px;" class="modal fade" id="ss-progress-{{ $applicant->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content modal-lg">
							<div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

								<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
								<!-- <div class="container bootstrap snippets bootdey"> -->
								<div class="col-md-12">
									<div class="row">
										<div class="col-md-3 col-sm-3">
											<div class="text-center">
												<img class="profile-user-img img-fluid" src="{{ asset('uploads/'.$applicant->passport_picture) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'" alt="Applicant Picture">
												                 
											</div> <!-- /.thumbnail -->

										</div> <!-- /.col -->


										<div class="col-md-9 col-sm-9">
											<h2>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</h2>
											<h6>{{ $applicant->index_number }} &nbsp; | &nbsp; 
											@foreach($applicant->selections as $selection)
												@if($selection->status == 'SELECTED')
													{{ $selection->campusProgram->program->code }}
												@endif
											@endforeach											
											&nbsp; | &nbsp; {{ (ucwords(strtolower($applicant->intake->name))) }} Intake &nbsp; | &nbsp; <span style="color:red">{{ (ucwords(strtolower($applicant->status))) }} </span></h6>
											<hr>
											<ul style="list-style-type: none; inline">
												<li><i class="icon-li fa fa-envelope"></i> &nbsp; &nbsp;{{ $applicant->email }}</li>
												<li><i class="icon-li fa fa-phone"></i> &nbsp; &nbsp;{{ $applicant->phone }}</li>
											</ul>
											<a href="{{ url('finance/download-payments?keyword='.$applicant->index_number) }}" class="btn btn-primary">Download Admission Letter </a>									  

											<hr>

											<div class="accordion" id="applicant-accordion">
												<div class="card">
												  <div class="card-header" id="ss-address">
													  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseAddress" aria-expanded="true" aria-controls="collapseAddress">
														&nbsp; More Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
													  </button>
												  </div>

												  <div id="collapseAddress" class="collapse" aria-labelledby="ss-address" data-parent="#applicant-accordion">
													<div class="card-body">

														  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($applicant->gender == 'M') Male @elseif($applicant->gender == 'F') Female @endif
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; {{ $applicant->birth_date }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $applicant->nationality }}											  
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; {{ $applicant->disabilityStatus->name }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; {{ ucwords(strtolower($applicant->entry_mode)) }}	 												  
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $applicant->address }}	 	
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; {{ ucwords(strtolower($applicant->ward->name)) }},&nbsp; {{ ucwords(strtolower($applicant->region->name)) }},&nbsp; {{ ucwords(strtolower($applicant->country->name)) }}	 	 
													</div>
												  </div>
												</div>
												
												<div class="card">
												  <div class="card-header" id="ss-next-of-kin">
													  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNextOfKin" aria-expanded="true" aria-controls="collapseNextOfKin">
														&nbsp; Next Of Kin Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
													  </button>
												  </div>

												  <div id="collapseNextOfKin" class="collapse" aria-labelledby="ss-next-of-kin" data-parent="#applicant-accordion">
													<div class="card-body">

													  @if($applicant->nextOfKin)
														  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Names:</span> &nbsp; {{ $applicant->nextOfKin->first_name }} {{ $applicant->nextOfKin->middle_name }} {{ $applicant->nextOfKin->surname }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($applicant->nextOfKin->gender == 'M') Male @elseif($applicant->nextOfKin->gender == 'F') Female @endif
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Relationship:</span> &nbsp; {{ $applicant->nextOfKin->relationship }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $applicant->nextOfKin->nationality }}											  
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Phone:</span> &nbsp; {{ $applicant->nextOfKin->phone }}	
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $applicant->nextOfKin->address }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; {{ ucwords(strtolower($applicant->nextOfKin->ward->name)) }},&nbsp; {{ ucwords(strtolower($applicant->nextOfKin->region->name)) }},&nbsp; {{ ucwords(strtolower($applicant->nextOfKin->country->name)) }}	 	 
																										  
													   @endif
													</div>
												  </div>
												</div>

												<div class="card">
												  <div class="card-header" id="ss-letter">
													  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseLetter" aria-expanded="true" aria-controls="collapseLetter">
														&nbsp; Admission Letter &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
													  </button>
												  </div>

												  <div id="collapseLetter" class="collapse" aria-labelledby="ss-letter" data-parent="#applicant-accordion">
													<div class="card-body">
														 <iframe
															  src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('/uploads/Admission-Letter-'.$applicant->first_name.'-'.$applicant->surname.'.pdf') }}#toolbar=0&scrollbar=0"
															  frameBorder="0"
															  scrolling="auto"
															  height="400px"
															  width="100%"
														  ></iframe>
													</div>
												  </div>												  
												  
												</div>												
											</div>                                  
										</div>
									</div>
								</div>
							</div>
                          </div>
                          <!-- /.modal-content -->
						</div> 
                      <!-- /.modal -->
					 </div>
                 @endforeach




	
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

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
            <h1 class="m-0">Registered Students - {{ $semester->name }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Registered Students</a></li>
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
                 <h3 class="card-title">{{ __('Select Academic Year') }}</h3>
               </div>
              <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'registration/active-students','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id || $year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
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
             		   
            @if(count($active_students) != 0)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Active Students') }}</h3><br>
				 <a href="{{ url('registration/download-active-students') }}" class="btn btn-primary">Download List</a>
               </div>
               <!-- /.card-header -->
               <div class="card-body">

                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>SN</th>
                          <th>Name</th>
                          <th>Gender</th>
						  <th>Form IV Index No. </th>
						  <th>Form VI Index No./AVN </th>						  
                          <th>Registration Number</th>
                          <th>Programme</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($active_students as $key=>$reg)
                   <tr>
					  <td>{{($key+1)}} </td>
					  <td><a href="#" data-toggle="modal" data-target="#ss-progress-{{ $reg->student->id }}">{{ $reg->student->first_name }} {{ $reg->student->middle_name }} {{ $reg->student->surname }}</a></td>
                      <td>{{ $reg->student->gender }}</td>
					  <td>{{ $reg->student->applicant->index_number }}</td>
					  <td>
						@php($fiv_index = null)
						@php($avn = null)
						
						@foreach($reg->student->applicant->nectaResultDetails as $detail)
							@if($detail->exam_id == 2) @php ($fiv_index = $detail->index_number) @endif
						@endforeach
						@foreach($reg->student->applicant->nacteResultDetails as $detail)
							 @php ($avn = $detail->avn)
						@endforeach 
						
						@if(!empty($fiv_index) && empty($avn)) {{ $fiv_index }}
						@elseif(empty($fiv_index) && !empty($avn)) {{ $avn }}
						@elseif(!empty($fiv_index) && !empty($avn)) {{ $fiv_index}}; <br>{{ $avn}}
						@endif
					  </td>
                      <td>{{ $reg->student->registration_number }}</td>
                      <td>{{ $reg->student->campusProgram->program->code }}</td>
                   </tr>
                 @endforeach
                   </tbody>
                  </table>
                  
               </div>
            </div>
			
			     @foreach($active_students as $reg)
                    <div style="margin-top:20px;" class="modal fade" id="ss-progress-{{ $reg->student->id }}">
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
										<div class="col-md-4 col-sm-5">
											<div class="thumbnail">
												<img src="https://bootdey.com/img/Content/User_for_snippets.png" width="90%" alt="Student Picture">
											</div> <!-- /.thumbnail -->

										</div> <!-- /.col -->


										<div class="col-md-8 col-sm-12">
											<h2>{{ $reg->student->first_name }} {{ $reg->student->middle_name }} {{ $reg->student->surname }}</h2>
											<h6>{{ $reg->student->registration_number }} &nbsp; | &nbsp; {{ $reg->student->campusProgram->program->code}} &nbsp; | &nbsp; Year {{ $reg->student->year_of_study }} &nbsp; | &nbsp; <span style="color:red">{{ $reg->student->studentshipStatus->name }} </span></h6>
											<hr>
											<ul style="list-style-type: none; inline">
												<li><i class="icon-li fa fa-envelope"></i> &nbsp; &nbsp;{{ $reg->student->email }}</li>
												<li><i class="icon-li fa fa-phone"></i> &nbsp; &nbsp;{{ $reg->student->phone }}</li>
											</ul>
											<hr>

											<div class="list-group">  
												<a href="#" class="list-group-item btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseBasicInformation" aria-expanded="true" aria-controls="collapseBasicInformation">
												&nbsp;&nbsp;Postal & Physical Address
													<i class="fa fa-chevron-right list-group-chevron"></i>
												</a> 
												<a href="#" class="list-group-item">
												 &nbsp;&nbsp;Next of Kin Details
													<i class="fa fa-chevron-right list-group-chevron"></i>
													<span class="badge">3</span>
												</a> 
							<!--					<a href="#" class="list-group-item">
													<i class="fa fa-envelope"></i> &nbsp;&nbsp;Messages
													<i class="fa fa-chevron-right list-group-chevron"></i>
												</a> 
												<a href="#" class="list-group-item">
													<i class="fa fa-group"></i> &nbsp;&nbsp;Friends
													<i class="fa fa-chevron-right list-group-chevron"></i>
													<span class="badge">7</span>
												</a> 
												<a href="#" class="list-group-item">
													<i class="fa fa-cog"></i> &nbsp;&nbsp;Settings
													<i class="fa fa-chevron-right list-group-chevron"></i>
												</a> 
							-->				</div> <!-- /.list-group -->
										</div>
									</div>
								</div>
								
								<div class="accordion" id="accordionExample-2">
									                                <div class="card">
                                  <div class="card-header" id="ss-next-of-kin">
                                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNextOfKin" aria-expanded="true" aria-controls="collapseNextOfKin">
                                        2. Next Of Kin
                                        @if($applicant->next_of_kin_complete_status == 1) <i class="fa fa-check float-right"></i> @endif
                                      </button>
                                  </div>

                                  <div id="collapseNextOfKin" class="collapse" aria-labelledby="ss-next-of-kin" data-parent="#accordionExample-2">
                                    <div class="card-body">

                                      @if($applicant->nextOfKin)
                                      <table class="table table-bordered table-condensed">
                                        <tr>
                                          <td>First name: </td>
                                          <td>{{ $applicant->nextOfKin->first_name }}</td>
                                        </tr>
										</table>
									   @endif
									   </div>
									   </div>
								</div>
							</div>                                                            
						  </div>
                        </div>
                          <!-- /.modal-content -->
                    </div> 
                      <!-- /.modal -->
                    @endforeach


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

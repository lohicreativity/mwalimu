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
				            <a href="{{ url('registration/download-active-students?program_level='.$request->get('program_level_id')) }}" class="btn btn-primary">Download List</a>
                </div>
               <!-- /.card-header -->
                <div class="card-body">
                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                      <tr>
                        <th>SN</th>
                        <th>Name</th>
                        <th>Sex</th>
                        <th>Form IV Index# </th>
                        @if($active_students[0]->student->applicant->program_level_id != 1)<th>Form VI Index#/AVN </th>	@endif					  
                        <th>Reg#</th>
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
                        @if($reg->student->applicant->program_level_id != 1)
                          <td>
                            @php($fiv_index = null)
                            @php($avn = null)
                          
                            @foreach($reg->student->applicant->nectaResultDetails as $detail)
                              @if($detail->exam_id == 2 && $detail->verified == 1) @php ($fiv_index = $detail->index_number) @endif
                            @endforeach
                            @foreach($reg->student->applicant->nacteResultDetails as $detail)
                              @if($detail->verified == 1) @php ($avn = $detail->avn) @endif
                            @endforeach 
                          
                            @if(!empty($fiv_index) && empty($avn)) {{ $fiv_index }}
                            @elseif(empty($fiv_index) && !empty($avn)) {{ $avn }}
                            @elseif(!empty($fiv_index) && !empty($avn)) {{ $fiv_index}}; <br>{{ $avn}}
                            @endif
                          </td>
                        @endif
                        <td>{{ $reg->student->registration_number }}</td>
                        <td>{{ $reg->student->campusProgram->code }}</td>
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
                        <span aria-hidden="true">&times;</span> </button>
                      </div>
                      <div class="modal-body">
                        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
                        <!-- <div class="container bootstrap snippets bootdey"> -->
                        <div class="col-md-12">
                          <div class="row">
                            <div class="col-md-3 col-sm-3">
                              <div class="text-center">
                                <img class="profile-user-img img-fluid" src="{{ asset('uploads/'.$reg->student->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'" alt="Student Picture">                
                              </div> <!-- /.thumbnail -->
                            </div> <!-- /.col -->
                            <div class="col-md-9 col-sm-9">
                              <h2>{{ $reg->student->first_name }} {{ $reg->student->middle_name }} {{ $reg->student->surname }}</h2>
                              <h6>{{ $reg->student->registration_number }} &nbsp; | &nbsp; {{ $reg->student->campusProgram->program->code}} &nbsp; | &nbsp; Year {{ $reg->student->year_of_study }} &nbsp; | &nbsp; <span style="color:red">{{ $reg->student->studentshipStatus->name }} </span></h6>
                              <hr>
                              <ul style="list-style-type: none; inline">
                                <li><i class="icon-li fa fa-envelope"></i> &nbsp; &nbsp;{{ $reg->student->email }}</li>
                                <li><i class="icon-li fa fa-phone"></i> &nbsp; &nbsp;{{ $reg->student->phone }}</li>
                              </ul>
                              <hr>
                              <div class="accordion" id="student-accordion">
                                <div class="card">
                                  <div class="card-header" id="ss-address">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseAddress" aria-expanded="true" aria-controls="collapseAddress">
                                    &nbsp; More Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
                                    </button>
                                  </div>

                                  <div id="collapseAddress" class="collapse" aria-labelledby="ss-address" data-parent="#student-accordion">
                                    <div class="card-body">

                                      @if($reg->student->applicant)
                                        &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($reg->student->applicant->gender == 'M') Male @elseif($reg->student->applicant->gender == 'F') Female @endif
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; {{ $reg->student->applicant->birth_date }}
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $reg->student->applicant->nationality }}											  
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; @if(!empty($reg->student->applicant->disabilityStatus->name)){{ $reg->student->applicant->disabilityStatus->name }} @else None @endif
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; {{ $reg->student->applicant->entry_mode }}	 												  
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $reg->student->applicant->address }}	 	
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; @if(!empty($reg->student->applicant->ward->name)){{ $reg->student->applicant->ward->name }},&nbsp; @endif 
                                                                                                                                  @if(!empty($reg->student->applicant->region->name)){{ $reg->student->applicant->region->name }},&nbsp; @endif
                                                                                                                                  @if(!empty($reg->student->applicant->country->name)){{ $reg->student->applicant->country->name }}	@endif 	 
                                      @endif
                                    </div>
                                  </div>
                                </div>
                                
                                <div class="card">
                                  <div class="card-header" id="ss-next-of-kin">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNextOfKin" aria-expanded="true" aria-controls="collapseNextOfKin">
                                    &nbsp; Next Of Kin Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
                                    </button>
                                  </div>

                                  <div id="collapseNextOfKin" class="collapse" aria-labelledby="ss-next-of-kin" data-parent="#student-accordion">
                                    <div class="card-body">

                                      @if($reg->student->applicant->nextOfKin)
                                        &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Names:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->first_name }} {{ $reg->student->applicant->nextOfKin->middle_name }} {{ $reg->student->applicant->nextOfKin->surname }}
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($reg->student->applicant->nextOfKin->gender == 'M') Male @elseif($reg->student->applicant->nextOfKin->gender == 'F') Female @endif
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Relationship:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->relationship }}
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->nationality }}											  
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Phone:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->phone }}	
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->address }}
                                        <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; {{ $reg->student->applicant->nextOfKin->ward->name }},&nbsp; {{ $reg->student->applicant->nextOfKin->region->name }},&nbsp; {{ $reg->student->applicant->nextOfKin->country->name }}	 	 
                                                                
                                      @endif
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

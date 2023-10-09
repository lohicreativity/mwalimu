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
            <h1 class="m-0">Select Programmes - {{ $campus->name }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
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
            @if($applicant->payment_complete_status == 0)
            <div class="alert alert-warning">Payment section not completed.</div>
            @else

            @if(($applicant->status == 'NOT SELECTED') || ($applicant->status == null))  
            @if(count($full_programs) == count($available_progs))
              <div class="col-sm-12">  
              <div class="alert alert-danger alert-dismissible ss-messages-box position-absolute z-index-auto"  role="alert">
                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h5>Unfortunately all programmes are full. Please try from other MNMA campuses.</h5>
                    </div>
                </div>
            @else
                <div class='col-sm-7'>
                </div>  
                <div class="col-sm-5">
              @if(count($full_programs) != 0)
                @if(count($full_programs) > 0 && count($full_programs) < count($available_progs))
                  <div class="alert alert-danger alert-dismissible ss-messages-box position-absolute" style="z-index: 7;"  role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <h4>Please note that the following programmes are full;</h4>
                        @foreach($full_programs as $key=>$prog)
                          <p> {{ ($key+1) }}. {{ $prog->program->name }} </p>
                        @endforeach
                  </div><!-- end of ss-messages_box -->
                    @elseif(count($full_programs) >= count($available_progs))
                      <div class="alert alert-success alert-dismissible ss-messages-box position-absolute z-index-auto" style="z-index: 7;" role="alert">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                          <h4>Please note that only the following programmes are available;</h4>
                            @foreach($available_progs as $key=>$prog)
                                <p> {{ ($key+1) }}. {{ $prog->program->name }} </p>
                            @endforeach
                      </div><!-- end of ss-messages_box -->
                @endif

                <div class="card">
              <div class="card-header">
                <h3 class="card-title">Selections</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Choice</th>
                         <th>Programme</th>
                       </tr>
                    </thead>
                    <tbody>
                    <tr>
                       <td>1</td>
                       <td>@if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->freshSelections,1)) 1st Choice Selected 
                           @else <a href="#" data-toggle="modal" data-target="#ss-first-choice">Select 1st Choice Programme</a> @endif</td>
                    </tr>
                    @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->freshSelections,1))
                    <tr>
                       <td>2</td>
                       <td>@if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->freshSelections,2)) 2nd Choice Selected 
                           @else <a href="#" data-toggle="modal" data-target="#ss-second-choice">Select 2nd Choice Programme</a>@endif</td>
                    </tr>
                    @endif
                    @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->freshSelections,2))
                    <tr>
                       <td>3</td>
                       <td>@if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->freshSelections,3)) 3rd Choice Selected 
                           @else <a href="#" data-toggle="modal" data-target="#ss-third-choice">Select 3rd Choice Programme</a>@endif</td>
                    </tr>
                    @endif
                    @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->freshSelections,3))
                    <tr>
                       <td>4</td>
                       <td>@if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->freshSelections,4)) 4th Choice Selected 
                           @else <a href="#" data-toggle="modal" data-target="#ss-forth-choice">Select 4th Choice Programme</a>@endif</td>
                    </tr>
                    @endif
                  </tbody>
                 </table>
              </div>
            </div>
          </div>
              @else  
              <div class="alert alert-warning alert-dismissible ss-messages-box position-absolute z-index-auto"  role="alert">
                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h6>Unfortunately you do not qualify in any of our programmes offered in this campus. Please try from other MNMA campuses.</h6>
                </div>
            </div> 
            @endif
          @endif
          @endif


              <div class="modal fade" id="ss-first-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">1st Choice Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->freshSelections,$prog))
                                 <span>SELECTED</span>
                                @else
        
                                  @php
                                    $o_level_points = $a_level_points = $diploma_grade = $open_grade = null;
                                    if(count($o_level_selection_points) > 0){
                                      foreach ($o_level_selection_points as $key => $value) {
                                        if($key == $prog->id){
                                          $o_level_points = $value;
                                          break;
                                        }
                                      }
                                    }

                                    if(count($a_level_selection_points) > 0){
                                      foreach ($a_level_selection_points as $key => $value) {
                                        if($key == $prog->id){
                                          $a_level_points = $value;
                                          break;
                                        }
                                      }
                                    }

                                    if(count($diploma_selection_grade) > 0){
                                      foreach ($diploma_selection_grade as $key => $value) {
                                        if($key == $prog->id){
                                          $diploma_grade = $value;
                                          break;
                                        }
                                      }
                                    }
  
                                    if(count($open_selection_grade) > 0){
                                      foreach ($open_selection_grade as $key => $value) {
                                        if($key == $prog->id){
                                          $open_grade = $value;
                                          break;
                                        }
                                      }
                                    }
                              @endphp
                              {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                                 {!! Form::input('hidden','o_level_points',$o_level_points) !!}
                                 {!! Form::input('hidden','a_level_points',$a_level_points) !!}
                                 {!! Form::input('hidden','diploma_grade', $diploma_grade) !!}
                                 {!! Form::input('hidden','open_grade', $open_grade) !!}
                                     {!! Form::input('hidden','campus_program_id',$prog->id) !!}

                                     {!! Form::input('hidden','choice',1) !!}

                                     {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
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

              <div class="modal fade" id="ss-second-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">2nd Choice Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->freshSelections,$prog))
                                 <span>SELECTED</span>
                                @else
                                @php
                                $o_level_points = $a_level_points = $diploma_grade = $open_grade = null;
                                if(count($o_level_selection_points) > 0){
                                  foreach ($o_level_selection_points as $key => $value) {
                                    if($key == $prog->id){
                                      $o_level_points = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($a_level_selection_points) > 0){
                                  foreach ($a_level_selection_points as $key => $value) {
                                    if($key == $prog->id){
                                      $a_level_points = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($diploma_selection_grade) > 0){
                                  foreach ($diploma_selection_grade as $key => $value) {
                                    if($key == $prog->id){
                                      $diploma_grade = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($open_selection_grade) > 0){
                                  foreach ($open_selection_grade as $key => $value) {
                                    if($key == $prog->id){
                                      $open_grade = $value;
                                      break;
                                    }
                                  }
                                }
                              @endphp
                              {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                                 {!! Form::input('hidden','o_level_points',$o_level_points) !!}
                                 {!! Form::input('hidden','a_level_points',$a_level_points) !!}
                                 {!! Form::input('hidden','diploma_grade', $diploma_grade) !!}
                                 {!! Form::input('hidden','open_grade', $open_grade) !!}
                                 {!! Form::input('hidden','campus_program_id',$prog->id) !!}
                                 {!! Form::input('hidden','choice',2) !!}
                                 {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
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

              <div class="modal fade" id="ss-third-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">3rd Choice Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->freshSelections,$prog))
                                 <span>SELECTED</span>
                                @else
                                @php
                                $o_level_points = $a_level_points = $diploma_grade = $open_grade = null;
                                if(count($o_level_selection_points) > 0){
                                  foreach ($o_level_selection_points as $key => $value) {
                                    if($key == $prog->id){
                                      $o_level_points = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($a_level_selection_points) > 0){
                                  foreach ($a_level_selection_points as $key => $value) {
                                    if($key == $prog->id){
                                      $a_level_points = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($diploma_selection_grade) > 0){
                                  foreach ($diploma_selection_grade as $key => $value) {
                                    if($key == $prog->id){
                                      $diploma_grade = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($open_selection_grade) > 0){
                                  foreach ($open_selection_grade as $key => $value) {
                                    if($key == $prog->id){
                                      $open_grade = $value;
                                      break;
                                    }
                                  }
                                }
                              @endphp
                              {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                                 {!! Form::input('hidden','o_level_points',$o_level_points) !!}
                                 {!! Form::input('hidden','a_level_points',$a_level_points) !!}
                                 {!! Form::input('hidden','diploma_grade', $diploma_grade) !!}
                                 {!! Form::input('hidden','open_grade', $open_grade) !!}
                                 {!! Form::input('hidden','campus_program_id',$prog->id) !!}
                                 {!! Form::input('hidden','choice',3) !!}
                                 {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
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

              <div class="modal fade" id="ss-forth-choice">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">4th Choice Programme</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      @if(count($campus_programs) != 0)
                     <table class="table table-bordered">
                       <thead>
                         <tr>
                           <th>Programme</th>
                           <th>Campus</th>
                           <th>Action</th>
                         </tr>
                       </thead>
                       <tbody>
                          @foreach($campus_programs as $prog)
                          <tr>
                              <td>{{ $prog->program->name }}</td>
                              <td>{{ $prog->campus->name }}</td>
                              <td>
                                @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelected($applicant->freshSelections,$prog))
                                 <span>SELECTED</span>
                                @else
                                @php
                                $o_level_points = $a_level_points = $diploma_grade = $open_grade = null;
                                if(count($o_level_selection_points) > 0){
                                  foreach ($o_level_selection_points as $key => $value) {
                                    if($key == $prog->id){
                                      $o_level_points = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($a_level_selection_points) > 0){
                                  foreach ($a_level_selection_points as $key => $value) {
                                    if($key == $prog->id){
                                      $a_level_points = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($diploma_selection_grade) > 0){
                                  foreach ($diploma_selection_grade as $key => $value) {
                                    if($key == $prog->id){
                                      $diploma_grade = $value;
                                      break;
                                    }
                                  }
                                }

                                if(count($open_selection_grade) > 0){
                                  foreach ($open_selection_grade as $key => $value) {
                                    if($key == $prog->id){
                                      $open_grade = $value;
                                      break;
                                    }
                                  }
                                }
                              @endphp
                              {!! Form::open(['url'=>'application/program/select','class'=>'ss-form-processing']) !!}

                                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                                 {!! Form::input('hidden','o_level_points',$o_level_points) !!}
                                 {!! Form::input('hidden','a_level_points',$a_level_points) !!}
                                 {!! Form::input('hidden','diploma_grade', $diploma_grade) !!}
                                 {!! Form::input('hidden','open_grade', $open_grade) !!}
                                 {!! Form::input('hidden','campus_program_id',$prog->id) !!}
                                 {!! Form::input('hidden','choice',4) !!}
                                 {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                <button type="submit" class="btn btn-primary">Select</button>
                                  {!! Form::close() !!}
                                @endif
                              </td>
                          </tr>
                          @endforeach
                       </tbody>
                     </table>
                     @endif
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

            @if(count($applicant->freshSelections) != 0)
				<div class="card">
				  <div class="card-header">
					<h3 class="card-title">Selections</h3>
				  </div>
				  <!-- /.card-header -->
				  <div class="card-body">
					 <table class="table table-bordered">
						<thead>
						   <tr>
							 <th>Choice</th>
							 <th>Programme</th>
							 <th>Campus</th>
							 <th>Action</th>
						   </tr>
						</thead>
						<tbody>
						@foreach($applicant->freshSelections as $key=>$selection)
             
								<tr>
								   <td>{{ $selection->order }}</td>
								   <td>{{ $selection->campusProgram->program->name }}</td>
								   <td>{{ $selection->campusProgram->campus->name }}</td>
								   <td>
									 @if($key == count($applicant->freshSelections)-1)
									<a href="{{ url('application/reset-program-selection/'.$selection->id) }}" class="ss-italic ss-color-danger">Reset Selection</a>
									 @endif
								  </td>
								</tr>							
              
						@endforeach
					  </tbody>
					 </table>
				  </div>
				</div>
            @endif

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

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
            <h1>{{ __('Special Exams') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Special Exams') }}</li>
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
                <h3 class="card-title">Select Academic Year</h3>
              </div>
              <!-- /.card-header -->
                 <div class="card-body">
                 {!! Form::open(['url'=>'academic/special-exams','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
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
                <h3 class="card-title">List of Special Exam Requests</h3>
              </div>
              <!-- /.card-header -->
                 
              <div class="card-body">
                {!! Form::open(['url'=>'academic/accept-special-exams','class'=>'ss-form-processing']) !!}

                {!! Form::input('hidden','study_academic_year_id',$request->get('study_academic_year_id')) !!}
                 
                <table id="example2" class="table table-bordered table-hover ss-margin-top ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Student</th>
                    <th>Reg Number</th>
                    <th>Semester</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    @if(!Auth::user()->hasRole('hod'))
                    <th>Recommendation</th>
                    @endif
                    <th>Actions</th>
                    @if(!Auth::user()->hasRole('hod'))
                    <th>Select</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($exams as $key=>$exam)
                  <tr>
                    <td>{{ ($key+1) }}</td>
                    <td>{{ $exam->student->first_name }} {{ $exam->student->middle_name }} {{ $exam->student->surname }}</td>
                    <td>{{ $exam->student->registration_number }}</td>
                    <td>@if($exam->semester) {{ $exam->semester->name }} @endif</td>
                    <td>{{ $exam->type }}</td>
                    <td>{{ $exam->status }}</td>
                    <td>{{ Carbon\Carbon::parse($exam->created_at)->format('Y-m-d') }} @if($exam->is_renewal == 1) * @endif</td>
                    @if(!Auth::user()->hasRole('hod'))
                    <td>@if($exam->recommended == 1) <a href="{{ url('academic/special-exam/'.$exam->id.'/recommend') }}">Recommended</a> @elseif($exam->recommended === 0) <a href="{{ url('academic/special-exam/'.$exam->id.'/recommend') }}">Not Recommended</a> @endif</td>
                    @endif
                    <td>
                      @if(Auth::user()->hasRole('hod'))
                      @if($exam->status == 'PENDING')
                      <a class="btn btn-info btn-sm" href="{{ url('academic/special-exam/'.$exam->id.'/recommend') }}">
                              <i class="fas fa-eye-open">
                              </i>
                              @if($exam->recommendation) Edit Recommendation @else Recommend @endif
                       </a>
                       @endif
                       @else
                       
                      @if($exam->status == 'PENDING')
                      <a class="btn btn-success btn-sm" href="#" data-toggle="modal" data-target="#ss-accept-post-{{ $exam->id }}">
                              <i class="fas fa-check">
                              </i>
                              Accept
                       </a>
                       <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-decline-post-{{ $exam->id }}">
                              <i class="fas fa-check">
                              </i>
                              Decline
                       </a>
                       @endif

                       <div class="modal fade" id="ss-accept-post-{{ $exam->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to accept this postponement?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/special-exam/'.$exam->id.'/accept') }}" class="btn btn-success">Accept</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
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

                      <div class="modal fade" id="ss-decline-post-{{ $exam->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to decline this postponement?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/special-exam/'.$exam->id.'/decline') }}" class="btn btn-danger">Decline</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
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
                       @endif
                    </td>
                    @if(!Auth::user()->hasRole('hod'))
                      <td>
                        @if($exam->status == 'PENDING')
                        {!! Form::checkbox('exam_'.$exam->id,$exam->id,true) !!}
                        @endif
                      </td>
                    @endif
                  </tr>
                  @endforeach

                  @if(!Auth::user()->hasRole('hod'))
                    @if(!$request->get('query'))
                     <tr>
                       <td colspan="9">
                        
                        <input type="submit" class="btn btn-primary" name="action" value="Accept Selected"> 
                        <input type="submit" class="btn btn-primary" name="action" value="Decline Selected">
                        
                      </td>
                     </tr>
                    @endif
                  @endif
                  
                  
                  </tbody>
                </table>
                {!! Form::close() !!}

                <div class="ss-pagination-links">
                   {!! $exams->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
              
            </div>
            <!-- /.card -->

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

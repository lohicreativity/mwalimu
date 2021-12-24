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
            <h1 class="m-0">Clearance</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
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
        
        <!-- Main row -->
        <div class="row">
          <div class="col-12">

             <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Select Study Academic Year') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/clearance','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
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
                 <h3 class="card-title">Request Clearance</h3>
              </div>
              <div class="card-body">
                 <table class="table table-bordered">
                   <thead>
                    <tr>
                      <th>Student</th>
                      <th>Finance</th>
                      <th>Library</th>
                      <th>Hostel</th>
                      <th>Stud Org</th>
                      <th>HOD</th>
                    </tr>
                   </thead>
                   <tbody>
                    @foreach($clearances as $clearance)
                     <tr>
                       <td>{{ $clearance->student->first_name }} {{ $clearance->student->middle_name }} {{ $clearance->student->surname }}</td>
                       <td>{{ $clearance->finance_status }} <a href="#" data-toggle="modal" data-target="#ss-stage-finance-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-finance-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="CLEARED">Cleared</option>
                                     <option value="NOT_CLEARED">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','finance') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
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
                       </td>
                       <td>{{ $clearance->library_status }}
                        <a href="#" data-toggle="modal" data-target="#ss-stage-library-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-library-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="CLEARED">Cleared</option>
                                     <option value="NOT_CLEARED">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','library') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
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
                       </td>
                       <td>{{ $clearance->hostel_status }}
                         <a href="#" data-toggle="modal" data-target="#ss-stage-hostel-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-hostel-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="CLEARED">Cleared</option>
                                     <option value="NOT_CLEARED">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','hostel') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
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
                       </td>
                       <td>{{ $clearance->stud_org_status }}
                          <a href="#" data-toggle="modal" data-target="#ss-stage-stud-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-stud-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="CLEARED">Cleared</option>
                                     <option value="NOT_CLEARED">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','stud_org') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
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
                       </td>
                       <td>{{ $clearance->hod_status }}
                           <a href="#" data-toggle="modal" data-target="#ss-stage-hod-{{ $clearance->id }}">Clear</a>
                            <div class="modal fade" id="ss-stage-hod-{{ $clearance->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              {!! Form::open(['url'=>'academic/clearance/update','class'=>'ss-form-processing']) !!}
                                 <select name="status" class="form-control" required>
                                     <option value="">Select Status</option>
                                     <option value="CLEARED">Cleared</option>
                                     <option value="NOT_CLEARED">Not Cleared</option>
                                 </select>

                                 {!! Form::input('hidden','clearance_id',$clearance->id) !!}
                                 {!! Form::input('hidden','stage','hod') !!}

                                 {!! Form::label('','Comment') !!}
                                 {!! Form::textarea('comment',null,['class'=>'form-control','rows'=>2,'placehoder'=>'Comment']) !!}

                                  <div class="ss-form-actions">
                                     <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
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
                       </td>
                     </tr>
                    @endforeach
                   </tbody>
                 </table>
              </div>
              
            </div>
          </div>
          
        </div>
        <!-- /.row (main row) -->
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

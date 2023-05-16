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
            <h1>{{ __('Application Windows') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Application Windows') }}</li>
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
          

            
            @can('add-application-window')
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Application Window') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $begin_date = [
                     'placeholder'=>'Begin date',
                     'class'=>'form-control ss-datepicker',
                     'autocomplete'=>'off',
                     'required'=>true
                  ];

                  $end_date = [
                     'placeholder'=>'End date',
                     'class'=>'form-control ss-datepicker',
                     'autocomplete'=>'off',
                     'required'=>true
                  ];

                  $capacity = [
                     'placeholder'=>'Capacity',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'application/application-window/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Intake') !!}
                    <select name="intake_id" class="form-control" required>
                      <option>Select Intake</option>
                      @foreach($intakes as $intake)
                      <option value="{{ $intake->id }}" @if($intake->name == 'September') selected="selected" @endif>{{ $intake->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Begin date') !!}
                    {!! Form::text('begin_date',null,$begin_date) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','End date') !!}
                    {!! Form::text('end_date',null,$end_date) !!}

                    {!! Form::input('hidden','status','INACTIVE') !!}
                  </div>
                  <div class="form-group col-3">
                    @if(Auth::user()->hasRole('administrator'))
                    {!! Form::label('','Campus') !!}
                    <select name="campus_id" class="form-control" required>
                      <option value="">Select Campus</option>
                      @foreach($campuses as $campus)
                      <option value="{{ $campus->id }}" @if($staff->campus_id == $campus->id) selected="selected" @endif>{{ $campus->name }}</option>
                      @endforeach
                    </select>
                    @else
                    {!! Form::label('','Campus') !!}
                    <select name="campus_id" class="form-control" required>
                      <option value="">Select Campus</option>
                      @foreach($campuses as $campus)
                      <option value="{{ $campus->id }}" @if($staff->campus_id != $campus->id) disabled="disabled" @else selected="selected" @endif>{{ $campus->name }}</option>
                      @endforeach
                    </select>
                    @endif

                  </div>
                  </div>
                </div>
                <!-- /.card-body -->
                

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Application Window') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($windows) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Application Windows') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>SN</th>
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) 
                      <th>Campus</th>
                    @endif
                    <th>Intake</th>
                    <th>Status</th>
                    <th>Begin Date</th>
                    <th>End Date</th>
                    @if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator'))                    
                    <th>Actions</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($windows as $key=>$window)
                  <tr>
                    <td>{{ ($key+1) }}</td>
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) 
                      <td>{{ $window->campus->name }}</td>
                    @endif
                    <td>{{ $window->intake->name }}</td>
                    <td>{{ $window->status }}</td>
                    <td>{{ $window->begin_date }}</td>
                    <td>{{ $window->end_date }}</td>
                    @if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator'))                    
                    <td>
                      @can('edit-application-window')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-window-{{ $window->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       @endcan
                       
                      

                       <div class="modal fade" id="ss-edit-window-{{ $window->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Application Window</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

                                 @php
                                    $begin_date = [
                                       'placeholder'=>'Begin date',
                                       'class'=>(strtotime($window->begin_date) <= strtotime(now()) && $window->status == 'ACTIVE')? 'form-control' : 'form-control ss-datepicker',
                                       'autocomplete'=>'off',
                                       'readonly'=>(strtotime($window->begin_date) <= strtotime(now()) && $window->status == 'ACTIVE')? true: null,
                                       'required'=>true
                                    ];

                                    $end_date = [
                                       'placeholder'=>'End date',
                                       'class'=>'form-control ss-datepicker',
                                       'autocomplete'=>'off',
                                       'required'=>true
                                    ];

                                    $capacity = [
                                       'placeholder'=>'Capacity',
                                       'class'=>'form-control',
                                       'required'=>true
                                    ];
                                @endphp

                                {!! Form::open(['url'=>'application/application-window/update','class'=>'ss-form-processing']) !!}

                                    <div class="row">
                                      <div class="form-group col-3">
                                        {!! Form::label('','Intake') !!}
                                        <select name="intake_id" class="form-control" required>
                                          <option>Select Intake</option>
                                          @foreach($intakes as $intake)
                                          <option value="{{ $intake->id }}" @if($window->intake_id == $intake->id) selected="selected" @else disabled="disabled" @endif>{{ $intake->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Begin date') !!}
                                        {!! Form::text('begin_date',App\Utils\DateMaker::toStandardDate($window->begin_date),$begin_date) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','End date') !!}
                                        {!! Form::text('end_date',App\Utils\DateMaker::toStandardDate($window->end_date),$end_date) !!}

                                        {!! Form::input('hidden','study_academic_year_id',$window->study_academic_year_id) !!}

                                        {!! Form::input('hidden','application_window_id',$window->id) !!}
                                        {!! Form::input('hidden','status',$window->status) !!}
                                      </div>
                                      <div class="form-group col-3">

                                        {!! Form::label('','Campus') !!}
                                        @if(Auth::user()->hasRole('administrator'))
                                        <select name="campus_id" class="form-control" required>
                                          <option value="">Select Campus</option>
                                          @foreach($campuses as $campus)
                                          <option value="{{ $campus->id }}" @if($window->campus_id == $campus->id) selected="selected" @else disabled="disabled" @endif>{{ $campus->name }}</option>
                                          @endforeach
                                        </select>
                                        @else
                                        <select name="campus_id" class="form-control" required>
                                          <option value="">Select Campus</option>
                                          @foreach($campuses as $campus)
                                          <option value="{{ $campus->id }}" @if($window->campus_id == $campus->id) selected="selected" @else disabled="disabled" @endif>{{ $campus->name }}</option>
                                          @endforeach
                                        </select>
                                        @endif
                                      </div>
                                      </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
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
                      
                      @can('delete-application-window')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-window-{{ $window->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                       @endcan

                       <div class="modal fade" id="ss-delete-window-{{ $window->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this application window from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('application/application-window/'.$window->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                      
                      @can('activate-application-window')
                       @if($window->status == 'ACTIVE')
                       <a class="btn btn-danger btn-sm" href="{{ url('application/window/'.$window->id.'/deactivate') }}">
                              <i class="fas fa-ban">
                              </i>
                              Deactivate
                       </a>
                      @else
                       <a class="btn btn-info btn-sm" href="{{ url('application/window/'.$window->id.'/activate') }}">
                              <i class="fas fa-check-circle">
                              </i>
                              Activate
                       </a>
                      @endif
                      @endcan
                    </td>
                    @endif
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @endif

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

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
            <h1>{{ __('Study Academic Years') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Study Academic Years') }}</li>
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

            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Academic Year') }}</h3>
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
              @endphp
              {!! Form::open(['url'=>'academic/study-academic-year/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Academic year') !!}
                    <select name="academic_year_id" class="form-control" required>
                      <option value="">Select Academic Year</option>
                      @foreach($academic_years as $yr)
                      <option value="{{ $yr->id }}">{{ $yr->year }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Begin date') !!}
                    {!! Form::text('begin_date',null,$begin_date) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','End date') !!}
                    {!! Form::text('end_date',null,$end_date) !!}

                    {!! Form::input('hidden','status','INACTIVE') !!}
                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Study Academic Year') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($study_academic_years) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Study Academic Years') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Year</th>
                    <th>Begin Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($study_academic_years as $year)
                  <tr>
                    <td>{{ $year->academicYear->year }}</td>
                    <td>{{ $year->begin_date }}</td>
                    <td>{{ $year->end_date }}</td>
                    <td>@if($year->status == 'ACTIVE') 
                        <span>{{ $year->status }}</span>
                        @else
                        <span class="ss-color-danger">{{ $year->status }}</span>
                        @endif
                    </td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-year-{{ $year->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>

                       <div class="modal fade" id="ss-edit-year-{{ $year->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Study Academic Year</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

                                {!! Form::open(['url'=>'academic/study-academic-year/update','class'=>'ss-form-processing']) !!}

                                    <div class="row">
                                    <div class="form-group col-4">
                                      {!! Form::label('','Academic year') !!}
                                      <select name="academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academic_years as $yr)
                                        <option value="{{ $yr->id }}" @if($yr->id == $year->academic_year_id) selected="selected" @endif>{{ $yr->year }}</option>
                                        @endforeach
                                      </select>
                                      {!! Form::input('hidden','study_academic_year_id',$year->id) !!}
                                    </div>
                                    <div class="form-group col-4">
                                      {!! Form::label('','Begin date') !!}
                                      {!! Form::text('begin_date',App\Utils\DateMaker::toStandardDate($year->begin_date),$begin_date) !!}
                                    </div>
                                    <div class="form-group col-4">
                                      {!! Form::label('','End date') !!}
                                      {!! Form::text('end_date',App\Utils\DateMaker::toStandardDate($year->end_date),$end_date) !!}

                                      {!! Form::input('hidden','status',$year->status) !!}
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
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-year-{{ $year->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-year-{{ $year->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this year from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/study-academic-year/'.$year->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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

                      @if($year->status == 'ACTIVE')
                       <a class="btn btn-danger btn-sm" href="{{ url('academic/study-academic-year/'.$year->id.'/deactivate') }}">
                              <i class="fas fa-ban">
                              </i>
                              Deactivate
                       </a>
                      @else
                       <a class="btn btn-info btn-sm" href="{{ url('academic/study-academic-year/'.$year->id.'/activate') }}">
                              <i class="fas fa-check-circle">
                              </i>
                              Activate
                       </a>

                      @endif
                    </td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
                <div class="ss-pagination-links">
                {!! $study_academic_years->render() !!}
                </div>
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

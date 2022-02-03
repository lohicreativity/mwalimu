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
            <h1>{{ __('Fee Amounts') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Fee Amounts') }}</li>
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
                <h3 class="card-title">{{ __('Add amount') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $fee_in_tzs = [
                     'placeholder'=>'Amount in TZS',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $fee_in_usd = [
                     'placeholder'=>'Amount in USD',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'finance/program-fee/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Programme') !!}
                    <select name="program_id" class="form-control">
                      <option value="">Select Program</option>
                      @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Amount in TZS') !!}
                    {!! Form::text('amount_in_tzs',null,$fee_in_tzs) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Amount in USD') !!}
                    {!! Form::text('amount_in_usd',null,$fee_in_usd) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Fee item') !!}
                    <select name="fee_item_id" class="form-control">
                      <option value="">Select Fee Item</option>
                      @foreach($fee_items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  
                </div>
                <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control">
                      <option value="">Select Study Academic Year</option>
                      @foreach($study_academic_years as $year)
                        <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-2">
                    {!! Form::label('','Year of study') !!}
                    <select name="year_of_study" class="form-control">
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                    </select>
                  </div>
                  <div class="form-group col-2">
                    {!! Form::label('','Semester') !!}
                    <select name="semester_id" class="form-control">
                      <option value="">Select Semester</option>
                      @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-2">
                    {!! Form::label('','Is Approved') !!}
                    <select name="is_approved" class="form-control">
                      <option value="1">Yes</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Usage') !!}
                    <select name="category" class="form-control">
                      <option value="In Use">In Use</option>
                      <option value="Not In Use">Not In Use</option>
                    </select>
                  </div>
                </div>
              </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button amount="submit" class="btn btn-primary">{{ __('Add Fee Amount') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($fees) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Fee Amounts') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Amount in TZS</th>
                    <th>Amount in USD</th>
                    <th>Approved</th>
                    <th>Status</th>
                    <th>Academic Year</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($fees as $fee)
                  <tr>
                    <td>{{ $fee->program->name }}</td>
                    <td>{{ number_format($fee->amount_in_tzs,2) }}</td>
                    <td>{{ number_format($fee->amount_in_usd,2) }}</td>
                    <td>@if($fee->is_approved == 1) Yes @else No @endif</td>
                    <td>{{ $fee->status }}</td>
                    <td>{{ $fee->studyAcademicYear->academicYear->year }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-amount-{{ $fee->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>

                       <div class="modal fade" id="ss-edit-amount-{{ $fee->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Fee Amount</h4>
                              <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                 @php
                                      $fee_in_tzs = [
                                         'placeholder'=>'Amount in TZS',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $fee_in_usd = [
                                         'placeholder'=>'Amount in USD',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];
                                  @endphp
                                  {!! Form::open(['url'=>'finance/program-fee/update','class'=>'ss-form-processing']) !!}

                                    <div class="row">
                                      <div class="form-group col-3">
                                        {!! Form::label('','Programme') !!}
                                        <select name="program_id" class="form-control">
                                          <option value="">Select Program</option>
                                          @foreach($programs as $program)
                                            <option value="{{ $program->id }}" @if($program->id == $fee->program_id) selected="selected" @endif>{{ $program->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Amount in TZS') !!}
                                        {!! Form::text('amount_in_tzs',$fee->amount_in_tzs,$fee_in_tzs) !!}

                                        {!! Form::input('hidden','program_fee_id',$fee->id) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Amount in USD') !!}
                                        {!! Form::text('amount_in_usd',$fee->amount_in_usd,$fee_in_usd) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Fee item') !!}
                                        <select name="fee_item_id" class="form-control">
                                          <option value="">Select Fee Item</option>
                                          @foreach($fee_items as $item)
                                            <option value="{{ $item->id }}" @if($fee->fee_item_id == $item->id) selected="selected" @endif>{{ $item->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-3">
                                        {!! Form::label('','Study academic year') !!}
                                        <select name="study_academic_year_id" class="form-control">
                                          <option value="">Select Study Academic Year</option>
                                          @foreach($study_academic_years as $year)
                                            <option value="{{ $year->id }}" @if($year->id == $fee->study_academic_year_id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-2">
                                        {!! Form::label('','Year of study') !!}
                                        <select name="year_of_study" class="form-control">
                                          <option value="1" @if($fee->year_of_study == 1) selected="selected" @endif>1</option>
                                          <option value="2" @if($fee->year_of_study == 2) selected="selected" @endif>2</option>
                                          <option value="3" @if($fee->year_of_study == 3) selected="selected" @endif>3</option>
                                        </select>
                                      </div>
                                        <div class="form-group col-2">
                                          {!! Form::label('','Semester') !!}
                                          <select name="semester_id" class="form-control">
                                            <option value="">Select Semester</option>
                                            @foreach($semesters as $semester)
                                              <option value="{{ $semester->id }}" @if($semester->id == $fee->semester_id) selected="selected" @endif>{{ $semester->name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="form-group col-2">
                                          {!! Form::label('','Is Approved') !!}
                                          <select name="is_approved" class="form-control">
                                            <option value="1" @if($fee->is_approved == 1) selected="selected" @endif>Yes</option>
                                            <option value="0" @if($fee->is_approved == 0) selected="selected" @endif>No</option>
                                          </select>
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Usage') !!}
                                          <select name="category" class="form-control">
                                            <option value="In Use" @if($fee->category == 'In Use') selected="selected" @endif>In Use</option>
                                            <option value="Not In Use" @if($fee->category == 'Not In Use') selected="selected" @endif>Not In Use</option>
                                          </select>
                                        </div>
                                      </div>
                                      <div class="ss-form-actions">
                                       <button amount="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                      </div>
                                {!! Form::close() !!}

                            </div>
                            <div class="modal-footer justify-content-between">
                              <button amount="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->

                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-amount-{{ $fee->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-amount-{{ $fee->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to delete this fee amount from the list?</p>
                                       <div class="ss-form-controls">
                                         <button amount="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('finance/program-fee/'.$fee->id.'/destroy') }}" class="btn btn-danger">Delete</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button amount="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
                      <a class="btn btn-info btn-sm" href="{{ url('finance/program-fee/'.$fee->id.'/structure') }}">
                              <i class="fas fa-list-alt">
                              </i>
                              Fee Structure
                       </a>
                    </td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
                <div class="ss-pagination-links">
                {!! $fees->render() !!}
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

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
            <h1>{{ __('Programme Fees') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Programme Fees') }}</li>
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
            <div class="card">
              <div class="card-header">
                 <h3 class="card-title">Select Campus</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'finance/program-fees','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    @if(Auth::user()->hasRole('finance-officer'))
                    <select name="campus_id" class="form-control" required>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($staff->campus_id == $cp->id) selected="selected" @else disabled="true" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                    @else
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($request->get('campus_id') == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                    @endif
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control">
                      <option value="">Select Study Academic Year</option>
                      @foreach($study_academic_years as $year)
                        <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
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
            @can('add-programme-fee')
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Programme Fee') }}</h3>
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
                    <select name="campus_program_id[]" class="form-control ss-select-tags" required multiple="multiple">
                      @foreach($campus_programs as $program)
                        <option value="{{ $program->id }}">{{ $program->program->name }}</option>
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
                        <option value="{{ $item->id }}" @if(str_contains($item->name,'Tuition')) selected="selected" @else disabled="disabled" @endif>{{ $item->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  
                </div>
                <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control">
                      <option value="">Select Study Academic Year</option>
                      @foreach($study_academic_years as $year)
                        @if($ac_year->id == $year->id)
                        <option value="{{ $year->id }}" @if($ac_year->id == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Year of study') !!}
                    <select name="year_of_study" class="form-control">
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                    </select>
                  </div>
                </div>
              </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button amount="submit" class="btn btn-primary">{{ __('Add Programme Fee') }}</button>
                  @if(str_contains($ac_year->academicYear->year,(date('Y')-1)))
                  <a href="{{ url('finance/program-fee/store-as-previous') }}" class="btn btn-primary">Assign As Previous</a>
                  @endif
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($fees) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Programme Fees') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover ss-margin-top ss-paginated-table">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Amount in TZS</th>
                    <th>Amount in USD</th>
                    <th>Academic Year</th>
                    <th>Year of Study</th>
					@if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))					
						<th>Campus</th>
					@else	
						<th>Actions</th>
					@endif
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($fees as $fee)
                  <tr>
                    <td>{{ $fee->campusProgram->program->name }}</td>
                    <td>{{ number_format($fee->amount_in_tzs,2) }}</td>
                    <td>{{ number_format($fee->amount_in_usd,2) }}</td>
                    <td>{{ $fee->studyAcademicYear->academicYear->year }}</td>
                    <td>{{ $fee->year_of_study }}</td>
					@if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))					
						<td>
							@foreach($campuses as $campus)
								@if($fee->campusProgram->campus_id == $campus->id)
									{{ $campus->name }}
									@break
								@endif	
							@endforeach	
						</td>
					
					@endif					
                    <td>
                      @can('edit-programme-fee')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-amount-{{ $fee->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan
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
                                        <select name="campus_program_id" class="form-control">
                                          <option value="">Select Program</option>
                                          @foreach($campus_programs as $program)
                                            <option value="{{ $program->id }}" @if($program->id == $fee->campus_program_id) selected="selected" @endif>{{ $program->program->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Amount in TZS') !!}
                                        {!! Form::text('amount_in_tzs',$fee->amount_in_tzs,$fee_in_tzs) !!}

                                        {!! Form::input('hidden','program_fee_id',$fee->id) !!}
                                        {!! Form::input('hidden','year',$fee->studyAcademicYear->academicYear->year) !!}
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
                                            <option value="{{ $item->id }}" @if($fee->fee_item_id == $item->id) selected="selected" @else disabled="disabled" @endif>{{ $item->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-4">
                                        {!! Form::label('','Study academic year') !!}
                                        <select name="study_academic_year_id" class="form-control">
                                          <option value="">Select Study Academic Year</option>
                                          @foreach($study_academic_years as $year)
                                            <option value="{{ $year->id }}" @if($year->id == $fee->study_academic_year_id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Year of study') !!}
                                        <select name="year_of_study" class="form-control">
                                          <option value="1" @if($fee->year_of_study == 1) selected="selected" @endif>1</option>
                                          <option value="2" @if($fee->year_of_study == 2) selected="selected" @endif>2</option>
                                          <option value="3" @if($fee->year_of_study == 3) selected="selected" @endif>3</option>
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
                      
                      @can('delete-programme-fee')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-amount-{{ $fee->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                      @endcan
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
                                         <a href="{{ url('finance/program-fee/'.$fee->id.'/destroy?campus_program_id='.$fee->campusProgram->id.'&year='.$fee->studyAcademicYear->academicYear->year.'&year_of_study='.$fee->year_of_study.'&study_academic_year_id='.$fee->studyAcademicYear->id) }}" class="btn btn-danger">Delete</a>
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
                      <!-- <a class="btn btn-info btn-sm" href="{{ url('finance/program-fee/'.$fee->id.'/structure') }}">
                              <i class="fas fa-list-alt">
                              </i>
                              Fee Structure
                       </a> -->
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

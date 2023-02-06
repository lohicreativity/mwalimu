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
            <h1>{{ __('NACTE Payments') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('NACTE Payments') }}</li>
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
                <h3 class="card-title">{{ __('Add NACTE Payment') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
               @php
                  $amount = [
                     'placeholder'=>'Amount',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $reference_number = [
                     'placeholder'=>'Reference number',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'finance/nacte-payment/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                    @if(Auth::user()->hasRole('administrator'))
                    <div class="form-group col-3">
                    {!! Form::label('','Amount') !!}
                    {!! Form::text('amount',null,$amount) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Reference number') !!}
                    {!! Form::text('reference_number',null,$reference_number) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Campus') !!}
                    <select name="campus_id" class="form-control">
                      <option value="">Select Campus</option>
                      @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control">
                      <option value="">Select Study Academic Year</option>
                      @foreach($study_academic_years as $year)
                        <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
                      @endforeach
                    </select>
                  </div>
                    @elseif(Auth::user()->hasRole('admission-officer'))
                    <input type="hidden" name="campus_id" value="">
                    <div class="form-group col-4">
                    {!! Form::label('','Amount') !!}
                    {!! Form::text('amount',null,$amount) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Reference number') !!}
                    {!! Form::text('reference_number',null,$reference_number) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control">
                      <option value="">Select Study Academic Year</option>
                      @foreach($study_academic_years as $year)
                        <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
                      @endforeach
                    </select>
                  </div>
                    @endif
                  
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button amount="submit" class="btn btn-primary">{{ __('Add NACTE Payment') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($payments) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of NACTE Payments') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Reference Number</th>
                    <th>Amount</th>
                    <th>Campus</th>
                    <th>Academic Year</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($payments as $payment)
                  <tr>
                    <td>{{ $payment->reference_no }}</td>
                    <td>{{ number_format($payment->amount,2) }}</td>
                    <td>{{ $payment->campus->name }}</td>
                    <td>{{ $payment->studyAcademicYear->academicYear->year }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-payment-{{ $payment->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>

                       <div class="modal fade" id="ss-edit-payment-{{ $payment->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit NACTE Payment</h4>
                              <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                 @php
                                      $amount = [
                                         'placeholder'=>'Amount',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $reference_number = [
                                         'placeholder'=>'Reference number',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];
                                  @endphp
                                  {!! Form::open(['url'=>'finance/nacte-payment/update','class'=>'ss-form-processing']) !!}

                                      
                                    <div class="row">
                                      <div class="form-group col-3">
                                        {!! Form::label('','Amount') !!}
                                        {!! Form::text('amount',$payment->amount,$amount) !!}

                                        {!! Form::input('hidden','nacte_payment_id',$payment->id) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Reference number') !!}
                                        {!! Form::text('reference_number',$payment->reference_no,$reference_number) !!}
                                      </div>

                                      <div class="form-group col-3">
                                        {!! Form::label('','Campus') !!}
                                        <select name="campus_id" class="form-control">
                                          <option value="">Select Campus</option>
                                          @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}" @if($payment->campus_id == $campus->id) selected="selected" @endif>{{ $campus->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Study academic year') !!}
                                        <select name="study_academic_year_id" class="form-control">
                                          <option value="">Select Study Academic Year</option>
                                          @foreach($study_academic_years as $year)
                                            <option value="{{ $year->id }}" @if($year->id == $payment->study_academic_year_id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                                          @endforeach
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

                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-payment-{{ $payment->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-payment-{{ $payment->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this NACTE payment from the list?</p>
                                       <div class="ss-form-controls">
                                         <button amount="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('finance/nacte-payment/'.$payment->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                    </td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
                <div class="ss-pagination-links">
                {!! $payments->render() !!}
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

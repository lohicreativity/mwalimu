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
             @can('add-fee-amount')
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Amount') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $amount_in_tzs = [
                     'placeholder'=>'Amount in TZS',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $amount_in_usd = [
                     'placeholder'=>'Amount in USD',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'finance/fee-amount/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Amount in TZS') !!}
                    {!! Form::text('amount_in_tzs',null,$amount_in_tzs) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Amount in USD') !!}
                    {!! Form::text('amount_in_usd',null,$amount_in_usd) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Fee item') !!}
                    <select name="fee_item_id" class="form-control">
                      <option value="">Select Fee Item</option>
                      @foreach($fee_items as $item)
                        @if(!str_contains(strtolower($item->name),'tuition fee'))
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control">
                      <option value="">Select Study Academic Year</option>
                      @foreach($study_academic_years as $k=>$year)
                        <option value="{{ $year->id }}" @if($k == 0) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                      @endforeach
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
            @endcan

            @if(count($amounts) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Fee Amounts') }}</h3><br>
                <a href="{{ url('finance/fee-amount/assign-as-previous') }}" class="btn btn-primary">Assign As Previous</a>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Item</th>
                    <th>Amount in TZS</th>
                    <th>Amount in USD</th>
                    <th>Academic Year</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($amounts as $amount)
                  <tr>
                    <td>{{ $amount->feeItem->name }}</td>
                    <td>{{ number_format($amount->amount_in_tzs,2) }}</td>
                    <td>{{ number_format($amount->amount_in_usd,2) }}</td>
                    <td>{{ $amount->studyAcademicYear->academicYear->year }}</td>
                    <td>
                      @can('edit-fee-amount')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-amount-{{ $amount->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan
                       <div class="modal fade" id="ss-edit-amount-{{ $amount->id }}">
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
                                      $amount_in_tzs = [
                                         'placeholder'=>'Amount in TZS',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $amount_in_usd = [
                                         'placeholder'=>'Amount in USD',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];
                                  @endphp
                                  {!! Form::open(['url'=>'finance/fee-amount/update','class'=>'ss-form-processing']) !!}

                                    <div class="row">
                                      <div class="form-group col-3">
                                        {!! Form::label('','Amount in TZS') !!}
                                        {!! Form::text('amount_in_tzs',$amount->amount_in_tzs,$amount_in_tzs) !!}

                                        {!! Form::input('hidden','fee_amount_id',$amount->id) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Amount in USD') !!}
                                        {!! Form::text('amount_in_usd',$amount->amount_in_usd,$amount_in_usd) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Fee item') !!}
                                        <select name="fee_item_id" class="form-control">
                                          <option value="">Select Fee Item</option>
                                          @foreach($fee_items as $item)
                                            <option value="{{ $item->id }}" @if($amount->fee_item_id == $item->id) selected="selected" @endif>{{ $item->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Study academic year') !!}
                                        <select name="study_academic_year_id" class="form-control">
                                          <option value="">Select Study Academic Year</option>
                                          @foreach($study_academic_years as $year)
                                            <option value="{{ $year->id }}" @if($year->id == $amount->study_academic_year_id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
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
                      
                      @can('delete-fee-amount')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-amount-{{ $amount->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                       @endcan
                       <div class="modal fade" id="ss-delete-amount-{{ $amount->id }}">
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
                                         <a href="{{ url('finance/fee-amount/'.$amount->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                {!! $amounts->render() !!}
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

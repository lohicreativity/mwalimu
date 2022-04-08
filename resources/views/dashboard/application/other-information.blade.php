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
            <h1 class="m-0">Other Information</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Other Information</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
     <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Health Insurance Verification</h3>
              </div>
              <div class="card-footer">
                 <a href="#" data-toggle="modal" data-target="#ss-insurance-card" class="btn btn-primary">Verify Health Insurance</a>
              </div>
            </div>

            <div class="modal fade" id="ss-insurance-card">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title"> Insurance Cards</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-12">
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-nhif-form" id="ss-card-nhif"> NHIF
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-other-form" id="ss-card-other"> Other Insurers
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-none-form" id="ss-card-none"> Don't have Insurance
                        </label>
                        </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->
                     <div class="row" id="ss-card-nhif-form">
                      <div class="col-12">
                          {!! Form::open(['url'=>'application/update-insurance-status','class'=>'ss-form-processing']) !!}
                            <div class="form-group">
                              {!! Form::label('','Card number') !!}
                              {!! Form::text('card_number',null,['class'=>'form-control','placeholder'=>'Card number']) !!}

                              {!! Form::input('hidden','insurance_name','NHIF') !!}

                              {!! Form::input('hidden','insurance_status',1) !!}
                              {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                            </div>
                            <button type="submit" class="btn btn-primary">Verify</button>
                          {!! Form::close() !!}
                       </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->

                     <div class="row" id="ss-card-other-form">
                      <div class="col-12">
                          {!! Form::open(['url'=>'application/update-insurance-status','class'=>'ss-form-processing']) !!}
                            <div class="form-group">
                              {!! Form::label('','Insurance name') !!}
                              {!! Form::text('insurance_name',null,['class'=>'form-control','placeholder'=>'Insurance name']) !!}
                            </div>
                            <div class="form-group">
                              {!! Form::label('','Card number') !!}
                              {!! Form::text('card_number',null,['class'=>'form-control','placeholder'=>'Card number']) !!}

                              {!! Form::input('hidden','insurance_status',1) !!}
                              {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                            </div>
                            <div class="form-group">
                              {!! Form::label('','Expire date') !!}
                              {!! Form::text('expire_date',null,['class'=>'form-control ss-datepicker','placeholder'=>'Expire date']) !!}
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                          {!! Form::close() !!}
                       </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->

                     <div class="row" id="ss-card-none-form">
                      <div class="col-12">
                          {!! Form::open(['url'=>'application/update-insurance-status','class'=>'ss-form-processing']) !!}
                            {!! Form::input('hidden','insurance_status',0) !!}
                            {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                            <button type="submit" class="btn btn-primary">Request NHIF</button>
                          {!! Form::close() !!}
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

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Hostel Status Verification') }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'application/update-hostel-status']) !!}
              <div class="card-body">
                  {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                  <label class="radio-inline">
                    <input type="radio" name="hostel_status" value="1"> I require hostel accomodation
                  </label>
                  <label class="radio-inline">
                    <input type="radio" name="hostel_status" value="0"> I don't require hostel accomodation
                  </label>
              </div>
              <div class="card-footer">
              <button type="submit" class="btn btn-primary">{{ __('Update Hostel Status') }}</button>
            </div>
            {!! Form::close() !!}
            </div>

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

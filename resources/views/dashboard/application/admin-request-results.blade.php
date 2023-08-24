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
            <h1 class="m-0">Applicant Results</h1>
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
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Get O-Level NECTA Results') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $index_number = [
                     'placeholder'=>'S1000/0231/2015',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $year = [
                     'placeholder'=>'Year',
                     'class'=>'form-control',
                     'required'=>true
                  ];

              @endphp
              {!! Form::open(['url'=>'application/get-necta-results','class'=>'ss-form-processing-necta-admin']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Index number') !!}
                    {!! Form::text('index_number',null,$index_number) !!}
                  </div>
                  <div class="form-group col-6">

                    {!! Form::input('hidden','exam_id',1) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-results') !!}

                    {!! Form::input('hidden','results_container','#ss-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-results-confirmation-link') !!}
                    
                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Get O-Level NECTA Results') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Get A-Level NECTA Results') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $index_number = [
                     'placeholder'=>'S1000/0231/2015',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $year = [
                     'placeholder'=>'Year',
                     'class'=>'form-control',
                     'required'=>true
                  ];

              @endphp
              {!! Form::open(['url'=>'application/get-necta-results','class'=>'ss-form-processing-necta-admin']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Index number') !!}
                    {!! Form::text('index_number',null,$index_number) !!}
                  </div>
                  <div class="form-group col-6">

                    {!! Form::input('hidden','exam_id',2) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-results') !!}

                    {!! Form::input('hidden','results_container','#ss-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-results-confirmation-link') !!}
                   
                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Get A-Level NECTA Results') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            <div class="modal fade" id="ss-confirm-results">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">NECTA Results</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-12">
                            <div id="ss-results-container"></div>
                            </div>
                          </div>
                            <div class="row">
                              <div class="col-6">
                            <div id="ss-confirmation-container">

                             </div>
                             <div class="col-6">

          
                              </div><!-- end of ss-confirmation-container -->
                          </div><!-- end of col-md-12 -->
                       </div><!-- end of row -->
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Get NACTVET-AVN Results') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $avn = [
                     'placeholder'=>'19NA1030963ME',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'application/get-nacte-results','class'=>'ss-form-processing-nacte-admin']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','AVN') !!}
                    {!! Form::text('avn',null,$avn) !!}
                  </div>
                  <div class="form-group col-4">

                    {!! Form::input('hidden','display_modal','#ss-confirm-nacte-results') !!}

                    {!! Form::input('hidden','results_container','#ss-nacte-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-nacte-results-confirmation-link') !!}


                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Get NACTVET Results') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Confirm NACTVET Registration Number') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $nacte_reg_no = [
                     'placeholder'=>'NS0001/0002/2001',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'application/get-nacte-details','class'=>'ss-form-processing-nacte-reg-no-admin']) !!}
              <div class="card-body">


                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','NACTVET Reg mumber') !!}
                    {!! Form::text('nacte_reg_no', null, $nacte_reg_no) !!}
                  </div>
                  <div class="form-group col-4">

                    {!! Form::input('hidden','display_modal','#ss-confirm-nacte-results') !!}

                    {!! Form::input('hidden','results_container','#ss-nacte-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-nacte-results-confirmation-link') !!}


                  </div>
                </div>
              </div>
              <div class="card-footer">
              <button type="submit" class="btn btn-primary">{{ __('Get NACTVET Details') }}</button>
              </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->


            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Foundation Programmes (OUT) Results') }}</h3>
              </div>
              <!-- /.card-header -->
              @php
                  $out_reg_number = [
                     'placeholder'=>'N18-642-0000',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'application/get-out-results','class'=>'ss-form-processing-out-admin']) !!}
              <div class="card-body">

                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','OUT Reg mumber') !!}
                    {!! Form::text('reg_no',null,$out_reg_number) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-out-results') !!}

                    {!! Form::input('hidden','results_container','#ss-out-results-container') !!}

                  </div>
                  <div class="col-8">

                  </div>
                 </div>
              </div>
              <div class="card-footer">
             <button type="submit" class="btn btn-primary">{{ __('Get OUT Results') }}</button>
            </div>
            {!! Form::close() !!}
            </div>

            <div class="modal fade" id="ss-confirm-nacte-results">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">NACTE Results</h4>
                      
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>

                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-12">
                            <div id="ss-nacte-results-container"></div>
                          </div>
                        </div>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              <div class="modal fade" id="ss-confirm-out-results">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> OUT Results</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-12">
                            <div id="ss-out-results-container"></div>
                          </div>
                        </div>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->
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

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
            <h1 class="m-0">NECTA Results</h1>
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
            @if($applicant->payment_complete_status == 0)
            <div class="alert alert-warning">Payment section not completed</div>
            @else
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add O-Level NECTA Results') }}</h3>
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
              {!! Form::open(['url'=>'application/get-necta-results','class'=>'ss-form-processing-necta']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Index number') !!}
                    {!! Form::text('index_number',null,$index_number) !!}
                  </div>
                  <div class="form-group col-6">

                    {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                    {!! Form::input('hidden','exam_id',1) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-results') !!}

                    {!! Form::input('hidden','results_container','#ss-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-results-confirmation-link') !!}
                    <a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a>                               
                    @foreach($o_level_necta_results as $result)
                     <p class="ss-font-xs">Center Name: {{ $result->center_name }} <br>Division: {{ $result->division }} &nbsp; Points: {{ $result->points }} <i class="fa fa-check"></i></p>
                    @endforeach
                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add O-Level NECTA Results') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            
            @if($applicant->entry_mode == 'DIRECT' && !str_contains($applicant->programLevel->name,'Certificate'))
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add A-Level NECTA Results') }}</h3>
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
              {!! Form::open(['url'=>'application/get-necta-results','class'=>'ss-form-processing-necta']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Index number') !!}
                    {!! Form::text('index_number',null,$index_number) !!}
                  </div>
                  <div class="form-group col-6">

                    {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                    {!! Form::input('hidden','exam_id',2) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-results') !!}

                    {!! Form::input('hidden','results_container','#ss-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-results-confirmation-link') !!}

                    @foreach($a_level_necta_results as $result)
                     <p class="ss-font-xs">Center Name: {{ $result->center_name }} <br>Division: {{ $result->division }} &nbsp; Points: {{ $result->points }} <i class="fa fa-check"></i></p>
                    @endforeach
                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add A-Level NECTA Results') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endif

            <div class="modal fade" id="ss-confirm-results">
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
                            <div id="ss-results-container"></div>
                            <div id="ss-confirmation-container">
                               {!! Form::open(['url'=>'application/necta-result/decline','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','index_number',null) !!}
                               {!! Form::input('hidden','year',null) !!}
                               {!! Form::input('hidden','exam_id',null) !!}
                               {!! Form::input('hidden','necta_result_detail_id',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               <div class="ss-form-controls">
                                 <button class="btn btn-danger" id="ss-result-confirmation-link" type="submit"
                                >Decline Results</button>        
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}

                               {!! Form::open(['url'=>'application/necta-result/confirm','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','index_number',null) !!}
                               {!! Form::input('hidden','year',null) !!}
                               {!! Form::input('hidden','exam_id',null) !!}
                               {!! Form::input('hidden','necta_result_detail_id',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               <div class="ss-form-controls">       
                                 <button class="btn btn-primary" type="submit">Confirm Results</button>
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}
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

              @if($applicant->entry_mode == 'EQUIVALENT')

              <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add NACTE Results') }}</h3>
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
              {!! Form::open(['url'=>'application/get-nacte-results','class'=>'ss-form-processing-nacte']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','AVN') !!}
                    {!! Form::text('avn',null,$avn) !!}
                  </div>
                  <div class="form-group col-4">

                    {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-nacte-results') !!}

                    {!! Form::input('hidden','results_container','#ss-nacte-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-nacte-results-confirmation-link') !!}

                    @foreach($nacte_results as $result)
                     <p class="ss-font-xs">Institution: {{ $result->institution }} <br>Programme: {{ $result->programme }} <br> GPA: {{ $result->diploma_gpa }} <i class="fa fa-check"></i></p>
                    @endforeach

                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add NACTE Results') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endif

            @endif

            <div class="modal fade" id="ss-confirm-nacte-results">
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
                            <div id="ss-nacte-results-container"></div>
                            <div id="ss-nacte-confirmation-container">
                               {!! Form::open(['url'=>'application/nacte-result/decline','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','avn',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               {!! Form::input('hidden','nacte_result_detail_id',null) !!}
                               <div class="ss-form-controls">
                                 <button class="btn btn-danger" id="ss-result-confirmation-link" type="submit">Decline Results</button>
                            
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}

                               {!! Form::open(['url'=>'application/nacte-result/confirm','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','avn',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               {!! Form::input('hidden','nacte_result_detail_id',null) !!}
                               <div class="ss-form-controls">
                                 <button class="btn btn-primary" type="submit">Confirm Results</button>
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}
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

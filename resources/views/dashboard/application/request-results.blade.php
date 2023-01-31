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
            <h1 class="m-0">NECTA Results - {{ $campus->name }}</h1>
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
            @if($applicant->payment_complete_status == 0 && $applicant->is_transfered != 1)
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
                     'placeholder'=>'S0000/0000/2015',
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
                    {!! Form::text('index_number',$applicant->index_number,$index_number) !!}
                  </div>
                  <div class="form-group col-6">

                    {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                    {!! Form::input('hidden','exam_id',1) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-results') !!}

                    {!! Form::input('hidden','results_container','#ss-results-container') !!}

                    {!! Form::input('hidden','results_link','#ss-results-confirmation-link') !!}
                    @if(count($o_level_necta_results) != 0) 
                    <p class="ss-color-danger ss-italic">If you have more than one index number use the same box to request results</p>  
                    @endif                          
                    @foreach($o_level_necta_results as $result)
                     <p class="ss-font-xs">Center Name: {{ $result->center_name }} <br>Division: {{ $result->division }} &nbsp; Points: @if($result->points) {{ $result->points }} @else N/A @endif <i class="fa fa-check"></i></p>
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
            
            @if(!str_contains($applicant->programLevel->name,'Certificate'))
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">
                  @if($applicant->entry_mode == 'EQUIVALENT') {{ __('Add A-Level NECTA Results (If Applicable)') }} @else {{ __('Add A-Level NECTA Results') }} @endif</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $index_number = [
                     'placeholder'=>'S0000/0000/2015',
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
                    @if(count($a_level_necta_results) != 0) 
                    <p class="ss-color-danger ss-italic">If you have more than one index number use the same box to request results</p>  
                    @endif  

                    @foreach($a_level_necta_results as $result)
                     <p class="ss-font-xs">Center Name: {{ $result->center_name }} <br>Division: {{ $result->division }} &nbsp; Points: @if($result->points) {{ $result->points }} @else N/A @endif <i class="fa fa-check"></i></p>
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

            <div class="modal fade" id="ss-confirm-results" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                      <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button> -->
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-12">
                            <div id="ss-results-container"></div>
                            </div>
                          </div>
                          <div id="ss-confirmation-container">
                            <div class="row">
                              <div class="col-6">
                            
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

                             </div>
                             <div class="col-6">

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
                              
                          </div><!-- end of col-md-12 -->
                       </div><!-- end of row -->
                       </div><!-- end of ss-confirmation-container -->
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              @if($applicant->entry_mode == 'EQUIVALENT')

              @if(str_contains($applicant->programLevel->name,'Degree'))

              <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">@if($applicant->entry_mode == 'EQUIVALENT') {{ __('Add NACTE Results (If Applicable)') }} @else {{ __('Add NACTE Results') }} @endif </h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $avn = [
                     'placeholder'=>'19NA0000003ME',
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
                    @php
                       $gpa_less = false;
                       foreach($nacte_results as $res){
                          if($res->diploma_gpa < 3){
                           $gpa_less = true;
                        }
                       }
                    @endphp
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

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Diploma in Teacher Education') }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'application/update-teacher-certificate-status','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                  {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                  <label class="radio-inline">
                    <input type="radio" name="teacher_certificate_status" id="inlineRadio1" value="1" @if($applicant->teacher_certificate_status === 1) checked="checked" @endif> I have Diploma in Teacher Education
                  </label>
                  <label class="radio-inline">
                    <input type="radio" name="teacher_certificate_status" id="inlineRadio2" value="0" @if($applicant->teacher_certificate_status === 0) checked="checked" @endif> I do not have Diploma in Teacher Education
                  </label>
              </div>
              <div class="card-footer">
             <button type="submit" class="btn btn-primary">{{ __('Update Status') }}</button>
            </div>

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('VETA (NVA)') }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'application/update-veta-nva','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                  {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                  <label class="radio-inline">
                    <input type="radio" name="veta_nva" id="inlineRadio1" value="1" @if($applicant->teacher_certificate_status === 1) checked="checked" @endif> I have Veta NVA
                  </label>
                  <label class="radio-inline">
                    <input type="radio" name="veta_nva" id="inlineRadio2" value="0" @if($applicant->teacher_certificate_status === 0) checked="checked" @endif> I do not have Veta NVA
                  </label>
              </div>
              <div class="card-footer">
             <button type="submit" class="btn btn-primary">{{ __('Update Status') }}</button>
            </div>
            {!! Form::close() !!}
            </div>
            @endif
             
            @if(str_contains($applicant->programLevel->name,'Degree') && (( $gpa_less || count($a_level_necta_results) != 0) || ($applicant->teacher_certificate_status === 1)))
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
              {!! Form::open(['url'=>'application/get-out-results','class'=>'ss-form-processing-out']) !!}
              <div class="card-body">
                  {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','OUT Reg mumber') !!}
                    {!! Form::text('reg_no',null,$out_reg_number) !!}

                    {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                    {!! Form::input('hidden','display_modal','#ss-confirm-out-results') !!}

                    {!! Form::input('hidden','results_container','#ss-out-results-container') !!}

                  </div>
                  <div class="col-8">
                    @foreach($out_results as $result)
                     <p class="ss-font-xs">Reg No: {{ $result->reg_no }} <br>GPA: {{ $result->gpa }} <i class="fa fa-check"></i></p>
                    @endforeach
                  </div>
                 </div>
              </div>
              <div class="card-footer">
             <button type="submit" class="btn btn-primary">{{ __('Add OUT Results') }}</button>
            </div>
            {!! Form::close() !!}
            </div>
            @endif

            

            @if(str_contains($applicant->programLevel->name,'Diploma'))
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('NACTE Registration Number') }}</h3>
              </div>
              <!-- /.card-header -->
              @php
                  $nacte_reg_number = [
                     'placeholder'=>'NS0001/0002/2001',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'application/update-nacte-reg-no','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                  {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','NACTE Reg mumber') !!}
                    {!! Form::text('nacte_reg_no',null,$nacte_reg_number) !!}
                  </div>
                  <div class="col-8">
                    <br><br>
                    @foreach($nacte_results as $result)
                     <p class="ss-font-xs">Reg No: {{ $result->registration_number }} <br>GPA: {{ $result->gpa }} <i class="fa fa-check"></i></p>
                    @endforeach
                  </div>
                 </div>
              </div>
              <div class="card-footer">
             <button type="submit" class="btn btn-primary">{{ __('Add NACTE Registration Number') }}</button>
            </div>
            {!! Form::close() !!}
            </div>
            @endif
            @endif

            @endif

            <div class="modal fade" id="ss-confirm-nacte-results" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                      <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button> -->
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-12">
                            <div id="ss-nacte-results-container"></div>
                          </div>
                        </div>
                        <div id="ss-nacte-confirmation-container">
                        <div class="row">
                          <div class="col-6">
                               {!! Form::open(['url'=>'application/nacte-result/decline','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','avn',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               {!! Form::input('hidden','nacte_result_detail_id',null) !!}
                               <div class="ss-form-controls">
                                 <button class="btn btn-danger" id="ss-result-confirmation-link" type="submit">Decline Results</button>
                            
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}

                              </div>
                            </div>
                              <div class="col-6">

                               {!! Form::open(['url'=>'application/nacte-result/confirm','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','avn',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               {!! Form::input('hidden','nacte_result_detail_id',null) !!}
                               <div class="ss-form-controls">
                                 <button class="btn btn-primary" type="submit">Confirm Results</button>
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}
                              
                          </div><!-- end of col-md-12 -->
                       </div><!-- end of row -->
                       </div><!-- end of ss-confirmation-container -->
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->

              <div class="modal fade" id="ss-confirm-out-results" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                      <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button> -->
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-12">
                            <div id="ss-out-results-container"></div>
                          </div>
                        </div>
                        <div id="ss-out-confirmation-container">
                        <div class="row">
                          <div class="col-6">
                            
                               {!! Form::open(['url'=>'application/out-result/decline','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','reg_no',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               {!! Form::input('hidden','out_result_detail_id',null) !!}
                               <div class="ss-form-controls">
                                 <button class="btn btn-danger" id="ss-result-confirmation-link" type="submit">Decline Results</button>
                            
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}

                              </div>
                              <div class="col-6">

                               {!! Form::open(['url'=>'application/out-result/confirm','class'=>'ss-form-processing']) !!}
                               
                               {!! Form::input('hidden','reg_no',null) !!}
                               {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                               {!! Form::input('hidden','out_result_detail_id',null) !!}
                               <div class="ss-form-controls">
                                 <button class="btn btn-primary" type="submit">Confirm Results</button>
                               </div><!-- end of ss-form-controls -->
                               {!! Form::close() !!}
                          </div><!-- end of col-md-12 -->
                       </div><!-- end of row -->
                       </div><!-- end of ss-confirmation-container -->
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

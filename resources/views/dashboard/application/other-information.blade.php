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
            <h1 class="m-0">More Information</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">More Information</a></li>
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
                <h3 class="card-title">Health Insurance Status 
                  @if(count($applicant->insurances) > 0)
				  @if($applicant->insurance_status === 0 || $applicant->insurance_status === 1)
					  @if($applicant->insurances[0]->verification_status == 'VERIFIED')
						- <span class="badge badge-success">Valid</span>						  
					  @elseif($applicant->insurances[0]->verification_status == 'UNVERIFIED')
						- <span class="badge badge-danger">Invalid</span>					  
					  @else
						- <span class="badge badge-warning">Awaiting Verification</span>
					  @endif
            @endif
                  @else
					- <span class="badge badge-warning">Pending</span>
                  @endif
				</h3>
              </div>
			  @if($applicant->insurance_status === 0 || $applicant->insurance_status === 1)
			  	<div class="row card-footer">
				
				@if(count($applicant->insurances) != 0)

						<a href="#" data-toggle="modal" data-target="#ss-insurance-card-preview-{{ $applicant->id }}" class="btn btn-primary">Preview</a>

 
					<div class="modal fade" id="ss-insurance-card-preview-{{ $applicant->id }}">
					  <div class="modal-dialog modal-lg">
						<div class="modal-content">
						  <div class="modal-header">
							<h4 class="modal-title"> Insurance Cards</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span>
							</button>
						  </div>
						  <div class="modal-body">					

							<div class="row" id="ss-card-preview-other-form">
							  <div class="col-12">
								
									<div class="form-group">
									  {!! Form::label('','Insurance name') !!}
									  {!! Form::text('insurance_name',$applicant->insurances[0]->insurance_name,['class'=>'form-control','placeholder'=>'Insurance name','readonly'=>true]) !!}
									</div>
									<div class="form-group">
									  {!! Form::label('','Card number') !!}
									  {!! Form::text('card_number',$applicant->insurances[0]->membership_number,['class'=>'form-control','placeholder'=>'Card number','readonly'=>true]) !!}

									</div>
									
									  {!! Form::label('','Expire Date') !!}		
									  {!! Form::text('expire_date',$applicant->insurances[0]->expire_date,['class'=>'form-control','readonly'=>true]) !!}																  
									<br>
									@if(count($applicant->insurances) > 0 && $applicant->insurances[0]->card != null)

										<a href="{{ url('application/view-document?name=insurance') }}" target="_blank" class="btn btn-primary"><i class="fa fa-eye"></i> View Insurance Card</a>
<!--											<a href="{{ url('application/delete-document?name=insurance') }}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a>		 -->							

									@endif

							   </div><!-- end of preview -->
							</div>

						  </div>
						</div>
					  </div>
					</div>

					@endif
					<div class="col-4">
					  {!! Form::open(['url'=>'application/reset-insurance-status','class'=>'ss-form-processing']) !!}
					  {!! Form::input('hidden','applicant_id',$applicant->id) !!}
						<button type="submit" @if(count($applicant->insurances) > 0 && $applicant->insurances[0]->verification_status != null) disabled='false' @endif class="btn btn-primary">Reset</button>
					  {!! Form::close() !!}
					</div>
			  	</div>

			  @else
              <div class="card-footer">
                 <a href="#" data-toggle="modal" data-target="#ss-insurance-card" class="btn btn-primary">Health Insurance Status</a>
              </div>
			  @endif
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
                    
                     
                    
                    @if(count($applicant->insurances) == 0)
                    <div class="row">
                      <div class="col-12">
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-nhif-form" id="ss-card-nhif"> NHIF
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-other-form" id="ss-card-other"> Other Insurers
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-none-form" id="ss-card-none" @if(count($applicant->insurances) > 0 && $applicant->insurance_status === 0) checked="checked" @endif> Don't have Insurance
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
                          {!! Form::open(['url'=>'application/update-insurance-status','files'=>true,'class'=>'ss-form-processing']) !!}
                            <div class="form-group">
                              {!! Form::label('','Insurance name') !!}
                              {!! Form::text('insurance_name',null,['class'=>'form-control','placeholder'=>'Insurance name','required'=>true]) !!}
                            </div>
                            <div class="form-group">
                              {!! Form::label('','Card number') !!}
                              {!! Form::text('card_number',null,['class'=>'form-control','placeholder'=>'Card number','required'=>true]) !!}

                              {!! Form::input('hidden','insurance_status',1) !!}
                              {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                            </div>
							
                              {!! Form::label('','Expire Date') !!}		
													  
                            <div class="row form-group">
							   <div class="col-4">
								 <select name="expire_date" class="form-control" required>
								   <option value="">Expire Date</option>
								   @for($i = 1; $i <= 31; $i++)
								   <option value="{{ $i }}">{{ $i }}</option>
								   @endfor
								 </select>
							   </div>
							   <div class="col-4">
								 <select name="expire_month" class="form-control" required>
								   <option value="">Month</option>
								   @for($i = 1; $i <= 12; $i++)
								   <option value="{{ $i }}">{{ $i }}</option>
								   @endfor
								 </select>
							   </div>
							   <div class="col-4">
								 <select name="expire_year" class="form-control" required>
								   <option value="">Year</option>
								   @for($i = date('Y'); $i <= now()->addYears(20)->format('Y'); $i++)
								   <option value="{{ $i }}">{{ $i }}</option>
								   @endfor
								 </select>
							   </div>
							</div>
							{!! Form::label('','Upload Insurance Card') !!}
						    {!! Form::file('insurance_card',['class'=>'form-control','required'=>true]) !!}
							<br>
						
							<button type="submit" class="btn btn-primary">Save</button>

                          {!! Form::close() !!}
                       </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->

                     <div class="row" id="ss-card-none-form">
                      <div class="col-12">
                          {!! Form::open(['url'=>'application/update-insurance-status','class'=>'ss-form-processing']) !!}
                            {!! Form::input('hidden','insurance_status',0) !!}
                            {!! Form::input('hidden','insurance_name',0) !!}
                            {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                            <button class="btn btn-primary" @if(count($applicant->insurances) > 0 && ($applicant->insurance_status != 0 || count($applicant->insurances) != 0)) disabled="disabled" @else type="submit" @endif>Request NHIF</button>
                          {!! Form::close() !!}
                       </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->

                     @endif

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
                <h3 class="card-title">{{ __('Hostel Request') }}  
					@if($applicant->hostel_status >= 1)
					  @if($applicant->hostel_available_status === 1)
					   - <span class="badge badge-success">Room Allocated</span>
					  @elseif($applicant->hostel_available_status === 0)
					   - <span class="badge badge-warning"> No Room Allocated</span>
					  @else
					   - <span class="badge badge-warning">Waiting Room Allocation</span>
					  @endif
					@elseif($applicant->hostel_status === 0)
					  - <span class="badge badge-success">Submitted</span>	
					@else
					  - <span class="badge badge-warning">Pending</span>							
					@endif				
				
				</h3>
              </div>
              <!-- /.card-header -->
              
              {!! Form::open(['url'=>'application/update-hostel-status']) !!}
              <div class="card-body">

                  {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                  <label class="radio-inline">
                    <input type="radio" name="hostel_status" value="1" @if($applicant->hostel_status === 1) checked="checked" @endif> I require accommodation
{{--                  <label class="radio-inline">
                    <input type="radio" name="hostel_status" value="2" @if($applicant->hostel_status === 2) checked="checked" @endif> Off-campus accomodation --}}
                  </label>
{{--                 <label class="radio-inline">
                    <input type="radio" name="hostel_status" value="3" @if($applicant->hostel_status === 3) checked="checked" @endif> On campus or off-campus accomodation
                  </label>
--}}
                  <label class="radio-inline">
                    <input type="radio" name="hostel_status" value="0" @if($applicant->hostel_status === 0) checked="checked" @endif> I do not require accommodation
                  </label>
              </div>
              <div class="card-footer">
              <button class="btn btn-primary" @if($applicant->hostel_available_status === 1 || $applicant->hostel_available_status === 0) disabled="true" @endif>Submit Hostel Request</button>
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

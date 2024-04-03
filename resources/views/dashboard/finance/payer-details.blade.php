
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
            <h1>{{ __('Payer Search') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Payer Search') }}</li>
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

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Search for Payer</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $identifier = [
                         'class'=>'form-control',
                         'placeholder'=>'index number, registration number, or surname',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'finance/payer-details','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter keyword') !!}
                    {!! Form::text('keyword',null,$identifier) !!}
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
			
            <!-- /.card -->
			@if($payer && $category == 'student')
			<div style="margin-top:20px;" data-toggle="modal">
			  <div class="modal-content">
				<div class="modal-header">
<!--				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button> -->
				</div>
				<div class="modal-body">

					<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
					<!-- <div class="container bootstrap snippets bootdey"> -->
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-3 col-sm-3">
								<div class="text-center">
									<img class="profile-user-img img-fluid" src="{{ asset('uploads/'.$payer->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'" alt="Student Picture">
													 
								</div> <!-- /.thumbnail -->

							</div> <!-- /.col -->


							<div class="col-md-9 col-sm-9">
								<h2>{{ $payer->first_name }} {{ $payer->middle_name }} {{ $payer->surname }}</h2>
								<h6>{{ $payer->applicant->index_number }} &nbsp; | &nbsp; {{ $payer->registration_number }} &nbsp; | &nbsp; {{ $payer->campusProgram->code}} &nbsp; | &nbsp; Year {{ $payer->year_of_study }} &nbsp; | &nbsp; <span style="color:red">{{ $payer->studentshipStatus->name }} </span></h6>
								<hr>
								<ul style="list-style-type: none; inline">
									<li><i class="icon-li fa fa-envelope"></i> &nbsp; &nbsp;{{ $payer->email }}</li>
									<li><i class="icon-li fa fa-phone"></i> &nbsp; &nbsp;{{ $payer->phone }}</li>
								</ul>
								<hr>

								<div class="accordion" id="student-accordion">
									<div class="card">
									  <div class="card-header" id="ss-address">
										  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseAddress" aria-expanded="true" aria-controls="collapseAddress">
											&nbsp; More Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
										  </button>
									  </div>

									  <div id="collapseAddress" class="collapse" aria-labelledby="ss-address" data-parent="#student-accordion">
										<div class="card-body">

										  @if($payer->applicant)
											  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($payer->applicant->gender == 'M') Male @elseif($payer->applicant->gender == 'F') Female @else @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; @if(!empty($payer->applicant->birth_date)) {{ $payer->applicant->birth_date }} @else @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; @if(!empty($payer->applicant->nationality)) {{ $payer->applicant->nationality }}	@else @endif										  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; @if(!empty($payer->applicant->disabilityStatus->name)) {{ $payer->applicant->disabilityStatus->name }} @else @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; @if(!empty($payer->applicant->entry_mode)) {{ ucwords(strtolower($payer->applicant->entry_mode)) }} @else @endif	 												  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; @if(!empty($payer->applicant->address)) {{ $payer->applicant->address }} @else @endif	 	
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; @if(!empty($payer->applicant->ward->name)) {{ ucwords(strtolower($payer->applicant->ward->name)) }},&nbsp; @else @endif 
											  @if(!empty($payer->applicant->region->name)) {{ ucwords(strtolower($payer->applicant->region->name)) }},&nbsp; @else @endif 
											  @if(!empty($payer->applicant->country->name)) {{ $payer->applicant->country->name }} @else @endif	 	 
										  @endif
										</div>
									  </div>
									</div>
									
									<div class="card">
									  <div class="card-header" id="ss-payments">
										  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapsePayments" aria-expanded="true" aria-controls="collapsePayments">
											&nbsp; Payment Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
										  </button>
									  </div>
									  
									<div id="collapsePayments" class="collapse" aria-labelledby="ss-payments" data-parent="#student-accordion">

									  <div class="card-body">
										@if(Auth::user()->hasRole('finance-officer'))
											<a href="{{ url('finance/download-payments?keyword='.$payer->registration_number) }}" class="btn btn-primary">Download Payment Details</a>	
											<a href="{{ url('finance/show-control-number?keyword='.$payer->registration_number) }}" class="btn btn-primary">Request a Control Number</a> <br><br>								  									  

										@endif									  
									    <table class="table table-bordered ss-paginated-table" style="font-size:10pt">
											<thead>
											<tr>
											   <th>SN</th>					
											   <th>Date</th>
											   <th>Control#</th>
											   <th>Fee Type</th>										   
											   <th>Fee Amount (TZS)</th>
											   <th>Paid Amount (TZS)</th>
											   <th>Balance (TZS)</th>
											</tr>
										  </thead>
										  <tbody>
											@foreach($student_payments as $key=>$payments)
											<tr>
											   <td>{{ ($key+1) }}</td>
											   <td>{{ date('Y-m-d',strtotime($payments->created_at))}}</td>
											   <td title="Invoice#: {{ $payments->reference_no }}">{{ $payments->control_no }} @if($payments->gateway_payment_id == null)<a href="{{ url('student/reset-control-number?reference_no='.$payments->reference_no) }}"> &nbsp; &nbsp; <span style="color: red; font-style:italic">Reset</a> @endif</td> 
											   <td>{{ $payments->feeType->name }}</td> 											   
											   <td>
												@if(str_contains($payments->feeType->name,'Tuition'))
													@if($tuition_fee_loan > 0)
														@if($tuition_fee_loan >= $programme_fee)
															0.00 <span style="color: red">({{ number_format($tuition_fee_loan,2) }} from HESLB) </span>
														@else
															{{ number_format($payments->amount,2) }} <span style="color: red">({{ number_format($tuition_fee_loan,2) }} from HESLB) </span>
														@endif
													@else
														{{ number_format($payments->amount,2) }} 														
													@endif
												@else
													{{ number_format($payments->amount,2) }} 
												
												@endif
								
											   <td title="@foreach($paid_receipts as $receipt) @if($receipt->bill_id == $payments->reference_no)  TZS {{ number_format($receipt->paid_amount,2) }} paid on {{ date('Y-m-d',strtotime($receipt->created_at)) }} &#10;&#13; @endif @endforeach"> 
												@if ($payments->gatewayPayment)
													@if (str_contains($payments->feeType->name,'Tuition'))
														@foreach($total_paid_fee as $fee)
															@if($payments->reference_no == $fee['reference_no'])
																{{ number_format($fee['amount'],2) }}
															@endif
														@endforeach
													@else
														{{ number_format($payments->gatewayPayment->paid_amount,2) }} 
													@endif
												@else
													0.00 													
												@endif
		
											   </td>
											   <td>
												@if ($payments->gatewayPayment)
													@if (str_contains($payments->feeType->name,'Tuition'))
														@if($tuition_fee_loan >= $programme_fee)
														0.00
														@else
															@foreach($total_paid_fee as $fee)
																@if($payments->reference_no == $fee['reference_no'])
																	{{ number_format($payments->gatewayPayment->bill_amount-$fee['amount'],2) }} 
																@endif
															@endforeach
														@endif
													@else
														{{ number_format($payments->gatewayPayment->bill_amount-$payments->gatewayPayment->paid_amount,2) }}
													@endif
												@else
													{{ number_format($payments->amount,2) }}
												@endif  
											   </td>
											</tr>
											@endforeach
										  </tbody>
										 </table>
									  </div>

									  <div id="collapseNextOfKin" class="collapse" aria-labelledby="ss-next-of-kin" data-parent="#student-accordion">
										<div class="card-body">

										</div>
									  </div>
									</div>
								</div>                                  
							</div>
						</div>
					</div>
				</div>
			  </div>
			  <!-- /.modal-content -->
			</div> 
		  <!-- /.modal -->
		@elseif($payer && $category == 'applicant')
			<div style="margin-top:20px;" data-toggle="modal">
			  <div class="modal-content">
				<div class="modal-header">
<!--				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button> -->
				</div>
				<div class="modal-body">

					<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
					<!-- <div class="container bootstrap snippets bootdey"> -->
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-3 col-sm-3">
								<div class="text-center">
									<img class="profile-user-img img-fluid" src="{{ asset('uploads/'.$payer->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'" alt="Student Picture">
													 
								</div> <!-- /.thumbnail -->

							</div> <!-- /.col -->


							<div class="col-md-9 col-sm-9">
								<h2>{{ $payer->first_name }} {{ $payer->middle_name }} {{ $payer->surname }}</h2>
								<h6>{{ $payer->index_number }} &nbsp; | &nbsp; {{ $payer->programLevel->code}} &nbsp; | &nbsp; {{ $payer->intake->name }} Intake &nbsp; | &nbsp; 
									<span style="color:red">@if($payer->programs_complete_status == 1 || $payer->submission_complete_status == 1) @if($payer->status == null) Submitted @else {{ $payer->status }} @endif @else In progress @endif</span></h6>
								<hr>
								<ul style="list-style-type: none; inline">
									<li><i class="icon-li fa fa-envelope"></i> &nbsp; &nbsp;{{ $payer->email }}</li>
									<li><i class="icon-li fa fa-phone"></i> &nbsp; &nbsp;{{ $payer->phone }}</li>
								</ul>
								<hr>

								<div class="accordion" id="student-accordion">
									<div class="card">
									  <div class="card-header" id="ss-address">
										  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseAddress" aria-expanded="true" aria-controls="collapseAddress">
											&nbsp; More Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
										  </button>
									  </div>

									  <div id="collapseAddress" class="collapse" aria-labelledby="ss-address" data-parent="#student-accordion">
										<div class="card-body">

											  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Sex:</span> &nbsp; @if($payer->gender == 'M') Male @elseif($payer->gender == 'F') Female @else @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; @if(!empty($payer->birth_date)) {{ $payer->birth_date }} @else @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; @if(!empty($payer->nationality)) {{ $payer->nationality }}	@else @endif										  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; @if(!empty($payer->disabilityStatus->name)) {{ $payer->disabilityStatus->name }} @else @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; @if(!empty($payer->entry_mode)) {{ ucwords(strtolower($payer->entry_mode)) }} @else @endif	 												  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; @if(!empty($payer->address)) {{ $payer->address }} @else @endif	 	
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; @if(!empty($payer->ward->name)) {{ ucwords(strtolower($payer->ward->name)) }},&nbsp; @else @endif 
											  @if(!empty($payer->region->name)) {{ ucwords(strtolower($payer->region->name)) }},&nbsp; @else @endif 
											  @if(!empty($payer->country->name)) {{ $payer->country->name }} @else @endif	
										</div>
									  </div>
									</div>
									
									<div class="card">
									  <div class="card-header" id="ss-payments">
										  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapsePayments" aria-expanded="true" aria-controls="collapsePayments">
											&nbsp; Payment Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
										  </button>
									  </div>
									  
									<div id="collapsePayments" class="collapse" aria-labelledby="ss-payments" data-parent="#student-accordion">

									  <div class="card-body">
										<a href="{{ url('finance/download-payments?keyword='.$payer->index_number) }}" class="btn btn-primary">Download Payment Details</a>	<br><br>						  									  
									    <table class="table table-bordered ss-paginated-table" style="font-size:10pt">
										  <thead>
											<tr>
											   <th>SN</th>					
											   <th>Date</th>
											   <th>Control#</th>
											   <th>Fee Type</th>										   
											   <th>Fee Amount</th>
											   <th>Paid Amount</th>
											   <th>Balance</th>
											</tr>
										  </thead>
										  <tbody>
											@foreach($applicant_payments as $key=>$payments)
											<tr>
											   <td>{{ ($key+1) }}</td>
											   <td>{{ date('Y-m-d',strtotime($payments->created_at))}}</td>
											   <td title="Invoice#: {{ $payments->reference_no }}">{{ $payments->control_no }} @if($payments->gateway_payment_id == null)<a href="{{ url('student/reset-control-number?reference_no='.$payments->reference_no) }}"> &nbsp; &nbsp; <span style="color: red; font-style:italic">Reset</a> @endif</td> 
											   <td>{{ $payments->feeType->name }}</td> 											   
											   <td>
												@if(str_contains($payments->feeType->name,'Tuition'))
													@if($tuition_fee_loan > 0)
														{{ number_format($payments->amount,2) }} <span style="color: red">({{ number_format($tuition_fee_loan,2) }} from HESLB) </span>
													@else
														{{ number_format($payments->amount,2) }}
													@endif
												@else
													{{ number_format($payments->amount,2) }} 
												@endif
											   </td>
											   <td>
												@if ($payments->gatewayPayment)
													@if (str_contains($payments->feeType->name,'Tuition'))
														@foreach ($total_paid_fee as $tuition_fee)
															@if ($tuition_fee['reference_no'] == $payments->reference_no)
																{{ number_format($tuition_fee['amount'],2) }}
																@break	
															@endif
														@endforeach
													@else
														{{ number_format($payments->gatewayPayment->paid_amount,2) }}
													@endif
												@else
													0.00	
												@endif
											   </td>
											   <td>
												@if($payments->gatewayPayment)
													@if (str_contains($payments->feeType->name,'Tuition'))
														@foreach ($total_paid_fee as $tuition_fee)
															@if ($tuition_fee['reference_no'] == $payments->reference_no)
																{{ number_format($payments->gatewayPayment->bill_amount-$tuition_fee['amount'],2) }} 
																@break	
															@endif
														@endforeach
													@else
														{{ number_format($payments->gatewayPayment->bill_amount-$payments->gatewayPayment->paid_amount,2) }} 
													@endif
												@else
													{{ number_format($payments->amount,2) }}
												@endif
											   </td>
											</tr>
											@endforeach
										  </tbody>
										 </table>
									  </div>

									  <div id="collapseNextOfKin" class="collapse" aria-labelledby="ss-next-of-kin" data-parent="#student-accordion">
										<div class="card-body">

										</div>
									  </div>
									</div>
								</div>                                  
							</div>
						</div>
					</div>
				</div>
			  </div>
			  <!-- /.modal-content -->
			</div> 
		  <!-- /.modal -->			
		@endif
	   </div>
	 </div>
	 </section>
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
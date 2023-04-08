
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
                         'placeholder'=>'Surname or registration number',
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
			@if($payer && $category == 'student' && $category != 'applicant')
			<div style="margin-top:20px;" data-toggle="modal">
			  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button>
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
								<h6>{{ $payer->registration_number }} &nbsp; | &nbsp; {{ $payer->campusProgram->program->code}} &nbsp; | &nbsp; Year {{ $payer->year_of_study }} &nbsp; | &nbsp; <span style="color:red">{{ $payer->studentshipStatus->name }} </span></h6>
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
											  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($payer->applicant->gender == 'M') Male @elseif($payer->applicant->gender == 'F') Female @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; {{ $payer->applicant->birth_date }}
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $payer->applicant->nationality }}											  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; {{ $payer->applicant->disabilityStatus->name }}
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; {{ $payer->applicant->entry_mode }}	 												  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $payer->applicant->address }}	 	
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; {{ $payer->applicant->ward->name }},&nbsp; {{ $payer->applicant->region->name }},&nbsp; {{ $payer->applicant->country->name }}	 	 
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
									    <table class="table table-bordered ss-paginated-table" style="font-size:10pt">
											<thead>
											<tr>
											   <th>SN</th>					
											   <th>Date</th>
											   <th>Invoice#</th>
											   <th>Fee Type</th>										   
											   <th>Fee Amount</th>
											   <th>Paid Amount</th>
											   <th>Balance</th>
											</tr>
										  </thead>
										  <tbody>
											@foreach($student_payments as $key=>$payments)
											<tr>
											   <td>{{ ($key+1) }}</td>
											   <td>{{ date('Y-m-d',strtotime($payments->gatewayPayment->created_at))}}</td>
											   <td title="Control#: {{ $payments->control_no }}">{{ $payments->reference_no }}</td> 
											   <td>{{ $payments->feeType->name }}</td> 											   
											   <td>{{ number_format($payments->gatewayPayment->bill_amount,2) }} {{ $payments->gatewayPayment->ccy }}</td>
											   <td>
												  {{ number_format($payments->gatewayPayment->bill_amount,2) }} {{ $payments->gatewayPayment->ccy }}
											   </td>
											   <td>
												  {{ number_format($payments->gatewayPayment->bill_amount-$payments->gatewayPayment->paid_amount,2) }} {{ $payments->gatewayPayment->ccy }}
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
		@if($payer && $category == 'applicant' && $category != 'student')
			<div style="margin-top:20px;" data-toggle="modal">
			  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button>
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
								<h6>{{ $payer->index_number }} &nbsp; | &nbsp; {{ $payer->programLevel->name}} &nbsp; | &nbsp; {{ $payer->intake->name }} Intake &nbsp; | &nbsp; 
									<span style="color:red">@if($payer->submission_complete_status == 1) @if($payer->status == null) Submitted @else {{ $payer->status }} @endif @else In progress @endif</span></h6>
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

											  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($payer->gender == 'M') Male @elseif($payer->gender == 'F') Female @endif
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; {{ $payer->birth_date }}
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $payer->nationality }}											  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; {{ $payer->disabilityStatus->name }}
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; {{ $payer->entry_mode }}	 												  
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $payer->address }}	 	
											  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; {{ $payer->ward->name }},&nbsp; {{ $payer->region->name }},&nbsp; {{ $payer->country->name }}	 	 
										
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
									    <table class="table table-bordered ss-paginated-table" style="font-size:10pt">
											<thead>
											<tr>
											   <th>SN</th>					
											   <th>Date</th>
											   <th>Invoice#</th>
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
											   <td>{{ date('Y-m-d',strtotime($payments->gatewayPayment->created_at))}}</td>
											   <td title="Control#: {{ $payments->control_no }}">{{ $payments->reference_no }}</td> 
											   <td>{{ $payments->feeType->name }}</td> 											   
											   <td>{{ number_format($payments->gatewayPayment->bill_amount,2) }} {{ $payments->gatewayPayment->ccy }}</td>
											   <td>
												  {{ number_format($payments->gatewayPayment->bill_amount,2) }} {{ $payments->gatewayPayment->ccy }}
											   </td>
											   <td>
												  {{ number_format($payments->gatewayPayment->bill_amount-$payments->gatewayPayment->paid_amount,2) }} {{ $payments->gatewayPayment->ccy }}
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
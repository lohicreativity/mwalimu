
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
            <h1>{{ __('Student Search') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Student Search') }}</li>
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
                <h3 class="card-title">Search for Student</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $reg_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Index number, registration number or surname',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'academic/student-search','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter search keyword') !!}
                    {!! Form::text('keyword',null,$reg_number) !!}
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->


            @if($student)
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
                        <img class="profile-user-img img-fluid" src="{{ asset('uploads/'.$student->image) }}" onerror="this.src='{{ asset("img/user-avatar.png") }}'" alt="Student Picture">
                                 
                      </div> <!-- /.thumbnail -->
      
                    </div> <!-- /.col -->
      
      
                    <div class="col-md-9 col-sm-9">
                      <h2>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</h2>
                      <h6>{{ $student->applicant->index_number }} &nbsp; | &nbsp; {{ $student->registration_number }} &nbsp; | &nbsp; {{ $student->campusProgram->code}} &nbsp; | &nbsp; Year {{ $student->year_of_study }} &nbsp; | &nbsp; <span style="color:red">{{ $student->studentshipStatus->name }} </span></h6>
                      <hr>
                      <ul style="list-style-type: none; inline">
                        <li><i class="icon-li fa fa-envelope"></i> &nbsp; &nbsp;{{ $student->email }}</li>
                        <li><i class="icon-li fa fa-phone"></i> &nbsp; &nbsp;{{ $student->phone }}</li>
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
      
                            @if($student->applicant)
                              &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($student->applicant->gender == 'M') Male @elseif($student->applicant->gender == 'F') Female @else @endif
                              <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Date of Birth:</span> &nbsp; @if(!empty($student->applicant->birth_date)) {{ $student->applicant->birth_date }} @else @endif
                              <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; @if(!empty($student->applicant->nationality)) {{ $student->applicant->nationality }}	@else @endif										  
                              <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Disability:</span> &nbsp; @if(!empty($student->applicant->disabilityStatus->name)) {{ $student->applicant->disabilityStatus->name }} @else @endif
                              <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Entry Mode:</span> &nbsp; @if(!empty($student->applicant->entry_mode)) {{ $student->applicant->entry_mode }} @else @endif	 												  
                              <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; @if(!empty($student->applicant->address)) {{ $student->applicant->address }} @else @endif	 	
                              <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span> &nbsp; @if(!empty($student->applicant->ward->name)) {{ $student->applicant->ward->name }},&nbsp; @else @endif 
                              @if(!empty($student->applicant->region->name)) {{ $student->applicant->region->name }},&nbsp; @else @endif 
                              @if(!empty($student->applicant->country->name)) {{ $student->applicant->country->name }} @else @endif	 	 
                            @endif
                          </div>
                          </div>
                        </div>
                        
                        <div class="card">
												  <div class="card-header" id="ss-next-of-kin">
													  <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNextOfKin" aria-expanded="true" aria-controls="collapseNextOfKin">
														&nbsp; Next Of Kin Details &nbsp; <i class="fa fa-chevron-right list-group-chevron"></i>
													  </button>
												  </div>

												  <div id="collapseNextOfKin" class="collapse" aria-labelledby="ss-next-of-kin" data-parent="#student-accordion">
													<div class="card-body">

													  @if($student->applicant->nextOfKin)
														  &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Names:</span> &nbsp; {{ ucwords(strtolower($student->applicant->nextOfKin->first_name)) }} {{ ucwords(strtolower($student->applicant->nextOfKin->middle_name)) }} {{ ucwords(strtolower($student->applicant->nextOfKin->surname)) }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Gender:</span> &nbsp; @if($student->applicant->nextOfKin->gender == 'M') Male @elseif($student->applicant->nextOfKin->gender == 'F') Female @endif
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Relationship:</span> &nbsp; {{ $student->applicant->nextOfKin->relationship }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Nationality:</span> &nbsp; {{ $student->applicant->nextOfKin->nationality }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Phone:</span> &nbsp; {{ $student->applicant->nextOfKin->phone }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Postal Address:</span> &nbsp; {{ $student->applicant->nextOfKin->address }}
														  <br> &nbsp; &nbsp; &nbsp; <span style="font-style:italic">Physical Address:</span>
                               &nbsp; @if(!empty($student->applicant->nextOfKin->ward->name)) {{ ucwords(strtolower($student->applicant->nextOfKin->ward->name)) }},@endif
                               &nbsp; @if(!empty($student->applicant->nextOfKin->district->name)) {{ ucwords(strtolower($student->applicant->nextOfKin->district->name)) }}, @endif
                               &nbsp; @if(!empty($student->applicant->nextOfKin->region->name))   {{ ucwords(strtolower($student->applicant->nextOfKin->region->name)) }}, @endif
                               &nbsp; {{ ucwords(strtolower($student->applicant->nextOfKin->country->name)) }}

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
                               <td title="Control#: {{ $payments->control_no }}">{{ $payments->reference_no }}</td> 
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
                               <td>
                              @if ($payments->gatewayPayment)
                                @if (str_contains($payments->feeType->name,'Tuition'))
                                  {{ number_format($total_paid_fee,2) }}
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
                                  {{ number_format($payments->gatewayPayment->bill_amount-$total_paid_fee,2) }} 
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
                    <a href="{{ url('student/deceased?student_id='.$student->id) }}" class="btn btn-primary">Deceased</a> 
                    <a href="{{ url('student/reset-password?student_id='.$student->id) }}" class="btn btn-primary">Reset Password</a>
                    @if($invoice) <a href="{{ url('student/reset-control-number?student_id='.$student->id) }}" class="btn btn-primary">Reset Control Number</a> @endif
                  </div>
                </div>
              </div>
              </div>
              <!-- /.modal-content -->
            </div> 
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

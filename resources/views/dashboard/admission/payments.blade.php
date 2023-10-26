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
            <h1 class="m-0">Payments - {{ $campus->name }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">Home</li>
              <li class="breadcrumb-item active"><a href="#">{{ __('Payments') }}</a></li>
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

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Payments</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                    <tr>
                       <th>Fee Item</th>
                       <th>Fee Amount (TZS)</th>
                       <th>Control Number</th>
                       <th>Amount To Be Paid (TZS)</th>
                       <th>Amount Paid (TZS)</th>
                       <th>Balance (TZS)</th>
                    </tr>
                    <tr>
                       <td>Programme Fee</td>
                       <td>@if($program_fee_amount) {{ number_format($program_fee_amount,2) }}  @endif</td> <!-- Fee Amount -->
                       <td>@if($tuition_fee_loan >= $program_fee_amount)  N/A <!-- Control Number -->
                           @elseif($program_fee_invoice)
                            {{ $program_fee_invoice->control_no }} 
                                @if(!$program_fee_invoice->control_no)<a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a>@endif  
                           @endif
                       </td>
                       <td>@if($tuition_fee_loan >= $program_fee_amount) N/A                                                                              <!-- Amount to be Paid-->
                           @else 
                              @if($tuition_fee_loan > 0){{ number_format($program_fee_amount - $tuition_fee_loan,2) }} <span style="color: red">({{ number_format($tuition_fee_loan,2) }} from HESLB) </span>
                              @else {{ number_format($program_fee_amount,2) }}
                              @endif      
                           @endif
                       </td> 
                       <td>@if($tuition_fee_loan >= $program_fee_amount) N/A                                                                              <!-- Amount Paid -->
                           @else
                            @if($fee_paid_amount> 0 && isset($program_fee_invoice)) {{ number_format($fee_paid_amount,2) }} 
                            @else 0.0 @endif @endif </td>               
                       <td>@if($tuition_fee_loan > 0)                                                                                                     <!-- Balance -->
                              @if($tuition_fee_loan >= $program_fee_amount) N/A     @endif
                              @if(isset($program_fee_invoice))                                                                                                                            
                                @if($fee_paid_amount > 0) {{ number_format($program_fee_invoice->amount - $fee_paid_amount,2) }}
                                @else {{ number_format($program_fee_invoice->amount,2) }} 
                                @endif
                              @elseif($tuition_fee_loan != $program_fee_amount)                                                                                                                              
                                {{ number_format($program_fee_amount - ($tuition_fee_loan + $fee_paid_amount),2) }} 
                              @endif
                            @else
                              @if(isset($program_fee_invoice))
                                  {{ number_format($program_fee_invoice->amount - $fee_paid_amount,2) }}                          
                              @else
                                {{ number_format($program_fee_amount,2) }} 
                              @endif
                            @endif 
                        </td>
                    </tr>
                    @if($applicant->has_postponed != 1)
                    <tr>
                       <td>Other Fees</td>
                       <td>@if($other_fee_invoice) {{ number_format($other_fee_invoice->amount,2) }} {{ $other_fee_invoice->currency }} @else @if($other_fee_amount) {{ number_format($other_fee_amount,2) }} @endif  @endif</td>
                       <td>@if($other_fee_invoice) {{ $other_fee_invoice->control_no }} @if(!$other_fee_invoice->control_no)<a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a>@endif  @endif</td>
                       <td> {{ number_format($other_fee_amount,2) }} </td>
                       <td>
                         @if(isset($other_fee_invoice->gatewayPayment))
                            {{ number_format($other_fee_invoice->gatewayPayment->paid_amount,2) }} 
                         @else 0.0
                         @endif
                       </td>
                       <td>
                         @if(isset($other_fee_invoice->gatewayPayment))
                            {{ number_format($other_fee_invoice->gatewayPayment->bill_amount-$other_fee_invoice->gatewayPayment->paid_amount,2) }} 
                         @else
                            {{ number_format($other_fee_amount,2) }}
                         @endif
                       </td>

                    </tr>
                    {{--
                    @if($insurance_fee)
                    <tr>
                       <td>Insurance Fee</td>
                       @if(str_contains($applicant->nationality,'Tanzania'))
                       <td>{{ number_format($insurance_fee->amount_in_tzs,0) }} TZS</td>
                       @else
                       <td>{{ number_format($insurance_fee->amount_in_usd*$usd_currency->factor,0) }} TZS</td>
                       @endif
                       <td>@if($insurance_fee_invoice) {{ $insurance_fee_invoice->control_no }} @if(!$insurance_fee_invoice->control_no)<a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a>@endif  @endif</td>
                       <td></td>
                       <td></td>
                    </tr>
                    @endif
                    --}}
                    @if($hostel_fee)
                    <tr>
                       <td>Hostel Fee</td>
                       <td>@if($hostel_fee_invoice) {{ number_format($hostel_fee_invoice->amount,0) }} {{ $hostel_fee_invoice->currency }} @else @if($hostel_fee_amount) {{ number_format($hostel_fee_amount,2) }}  @endif @endif</td>
                       <td>@if($hostel_fee_invoice) {{ $hostel_fee_invoice->control_no }} @if(!$hostel_fee_invoice->control_no)<a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a>@endif @endif</td>
                       <td>
                         @if($hostel_fee_invoice)
                            {{ number_format($hostel_fee_invoice->amount,0) }}
                         @endif
                       </td>
                       <td>
                         @if($hostel_paid_amount>0 && isset($hostel_fee_invoice))
                            {{ number_format($hostel_paid_amount-$hostel_fee_invoice->gatewayPayment->paid_amount,0) }}
                         @endif
                       </td>
                       <td></td>
                    </tr>
                    @endif
                    @endif
                    @if(!$datediff)
                      @if($applicant->hostel_available_status == 1 && $applicant->has_postponed != 1)
                        @if(!$program_fee_invoice || !$other_fee_invoice || !$hostel_fee_invoice)
                        <tr>
                          <td>
                            {!! Form::open(['url'=>'admission/request-control-number','class'=>'ss-form-processing']) !!}
                              {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                              <button type="submit" class="btn btn-primary">Request Control Number</button>
                            {!! Form::close() !!}
                          </td>
                        </tr>
                        @endif
                      @else
                        @if(!$program_fee_invoice || !$other_fee_invoice)
                        <tr>
                          <td>
                            {!! Form::open(['url'=>'admission/request-control-number','class'=>'ss-form-processing']) !!}
                              {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                              <button type="submit" class="btn btn-primary">Request Control Number</button>
                            {!! Form::close() !!}
                          </td>
                        </tr>
                        @endif
                    @endif
                  @endif  
                 </table>
              </div>
            </div>
            <!-- / .card -->
          </div>
        </div>
        <!-- / .row -->
        
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  @include('layouts.footer')
  
  <script type="text/javascript">
   
	
	
		
	window.onload = function(){

     @if($program_fee_invoice)

   const progInterval = setInterval(function(){

      $.ajax({
        url:'/application/check-receipt?invoice_id={{ $program_fee_invoice->id }}',
        method:'GET'
      }).done(function(data){
        if(data.code == 200){
          clearInterval(progInterval);
          window.location.reload();
          
        }
      });
    },10000);
  @endif

    @if($other_fee_invoice)
		const otherInterval = setInterval(function(){

			$.ajax({
				url:'/application/check-receipt?invoice_id={{ $other_fee_invoice->id }}',
				method:'GET'
			}).done(function(data){
				if(data.code == 200){
          clearInterval(otherInterval);
					window.location.reload();
          
				}
			});
		},10000);
    @endif
    
    @if($hostel_fee_invoice)
    
    const hostelInterval = setInterval(function(){

      $.ajax({
        url:'/application/check-receipt?invoice_id={{ $hostel_fee_invoice->id }}',
        method:'GET'
      }).done(function(data){
        if(data.code == 200){
          clearInterval(hostelInterval);
          window.location.reload();
          
        }
      });
    },10000);
    @endif
	};
	
	
	
</script>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection

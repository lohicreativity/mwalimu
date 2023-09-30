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
      @if(($applicant->status == 0) && ($applicant->payment_complete_status == 0))  
        @if(count($full_programs) == count($available_progs))
        <div class="col-sm-12">  
        <div class="alert alert-danger alert-dismissible ss-messages-box position-absolute z-index-auto"  role="alert">
                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                  <h5>Unfortunately all programmes are full. Please try from other MNMA campuses.</h5>
              </div>
          </div>
        @else
        <div class='col-sm-7'>
        </div>  
         <div class="col-sm-5">
        @if(count($full_programs) > 0 && count($full_programs) < count($available_progs))
          <div class="alert alert-danger alert-dismissible ss-messages-box position-absolute" style="z-index: 7;"  role="alert">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
              <h4>Please note that the following programmes are full;</h4>
                @foreach($full_programs as $key=>$prog)
                  <p> {{ ($key+1) }}. {{ $prog->program->name }} </p>
                @endforeach
          </div><!-- end of ss-messages_box -->
            @elseif(count($full_programs) >= count($available_progs))
              <div class="alert alert-success alert-dismissible ss-messages-box position-absolute z-index-auto" style="z-index: 7;" role="alert">
                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                  <h4>Please note that only the following programmes are available;</h4>
                    @foreach($available_progs as $key=>$prog)
                         <p> {{ ($key+1) }}. {{ $prog->program->name }} </p>
                    @endforeach
              </div><!-- end of ss-messages_box -->
            @endif
        </div>

        @endif
      @endif
        </div>
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
                @if($gateway_payment)
                   <div class="alert alert-success">Payment Completed Successfully.</div>
                @endif
                @if($fee_amount)
                 <table class="table table-bordered">
                    <tr>
                       <td>Fee Item</td>
                       <td>Fee Amount</td>
                    </tr>
                    <tr>
                       <td>{{ $fee_amount->feeItem->feeType->name }}</td>
                       @if(str_contains($applicant->nationality,'Tanzania'))
                        <td>{{ number_format($fee_amount->amount_in_tzs,0) }} TZS</td>
                       @else
                        <td>{{ number_format($fee_amount->amount_in_usd*$usd_currency->factor,0) }} TZS</td>
                       @endif
                    </tr>
                    @if($invoice)
                    <tr>
                      <td>Control Number</td>
                      <td>{{ $invoice->control_no }} @if($invoice->control_no == null) <a href="#" onclick="window.location.reload();">Refresh</a>@else @if(!$gateway_payment)@endif @endif</td>
                    </tr>
                    @endif
                    @if(!$invoice)
                    <tr>
                      <td>
                        {!! Form::open(['url'=>'application/request-control-number','class'=>'ss-form-processing']) !!}
                          {!! Form::input('hidden','fee_amount_id',$fee_amount->id) !!}
                          {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                          <button type="submit" class="btn btn-primary">Request Control Number</button>
                        {!! Form::close() !!}
                      </td>
                    </tr>
                    @endif
                 </table>
                 @else
                  <p>No application fee amount set.</p>
                 @endif
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

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<script type="text/javascript">
    @if($invoice)
		
	window.onload = function(){
		const payInterval = setInterval(function(){
			$.ajax({
				url:'/application/check-receipt?invoice_id={{ $invoice->id }}',
				method:'GET'
			}).done(function(data){
				if(data.code == 200){
					window.location.href = "{{ url('application/results') }}"
				}
			});
		},1000);
	};
	@endif
</script>

@endsection

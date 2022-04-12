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
                       <td>Fee Item</td>
                       <td>Fee Amount</td>
                       <td>Control Number</td>
                       <td>Status</td>
                    </tr>
                    <tr>
                       <td>Programme Fee</td>
                       @if($applicant->country->code == 'TZ')
                       <td>{{ $program_fee->amount_in_tzs }} TZS</td>
                       @else
                       <td>{{ $program_fee->amount_in_usd }} USD</td>
                       @endif
                       <td></td>
                       <td></td>
                    </tr>
                    @if($insurance_fee)
                    <tr>
                       <td>Insurance Fee</td>
                       @if($applicant->country->code == 'TZ')
                       <td>{{ $insurance_fee->amount_in_tzs }} TZS</td>
                       @else
                       <td>{{ $insurance_fee->amount_in_usd }} USD</td>
                       @endif
                       <td></td>
                       <td></td>
                    </tr>
                    @endif
                    @if($hostel_fee)
                    <tr>
                       <td>Hostel Fee</td>
                       @if($applicant->country->code == 'TZ')
                       <td>{{ $hostel_fee->amount_in_tzs }} TZS</td>
                       @else
                       <td>{{ $hostel_fee->amount_in_usd }} USD</td>
                       @endif
                       <td></td>
                       <td></td>
                    </tr>
                    @endif
                    <tr>
                      <td>
                        {!! Form::open(['url'=>'admission/request-control-number','class'=>'ss-form-processing']) !!}
                          {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                          <button type="submit" class="btn btn-primary">Request Control Number</button>
                        {!! Form::close() !!}
                      </td>
                    </tr>
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

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection

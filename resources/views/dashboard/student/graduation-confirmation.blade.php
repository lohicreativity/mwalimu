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
            <h1 class="m-0">Graduation Confirmation</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Graduation Confirmation</a></li>
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

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Graduation Confirmation') }}</h3>
              </div>
              <!-- /.card-header -->
              
              {!! Form::open(['url'=>'student/confirm-graduation']) !!}
              <div class="card-body">
 <!--                 @if($graduant->status == 'GRADUATING')
                   <span class="badge-success">Approved for Graduation</span>
                  @else
                   <span class="badge badge-warning">Disapproved for Graduation</span>
                  @endif
-->
                  @if($graduant->attendance_status === 1 && $payment_status == 1)
                   <span class="badge-success">Attendance Confirmed</span>
				  @elseif($graduant->attendance_status === 1 && $payment_status == 0)
				   <span class="badge-warning">Pending Graduation Gown Payment</span>
                  @elseif($graduant->attendance_status === 0)
                   <span class="badge badge-warning">Not Attending</span>
                  @endif
                  <br><br><br>
                  {!! Form::input('hidden','graduant_id',$graduant->id) !!}

                  <label class="radio-inline">
                    <input type="radio" name="status" value="1" @if($graduant->attendance_status === 1) checked="checked" @endif> I will attend
                  </label>
                  <label class="radio-inline">
                    <input type="radio" name="status" value="0" @if($graduant->attendance_status === 0) checked="checked" @endif> I will not attend
                  </label>
              </div>
              <div class="card-footer">
              <button class="btn btn-primary" @if($graduant->attendance_status === null) type="submit" @else disabled="disabled" @endif>{{ __('Confirm') }}</button>
			  
				   <a class="btn btn-info btn-sm" href="dsdsdf" disabled="disabled">
                      <i class="fas fa-check-circle" ></i>
                        Confirm
                   </a>
					   
				   <a class="btn btn-info btn-sm" href="dsdsdf">
                      <i class="fas fa-check-circle"></i>
                        Reset
                   </a>
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

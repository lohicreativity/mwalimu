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
            <h1>{{ __('Loan Allocations') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Loan Allocations') }}</li>
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
                <h3 class="card-title">Upload Loan Allocations</h3>
				<a href="{{ url('finance/download-loan-allocation-template') }}" class="ss-right">Download Formatted CSV File?</a>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'finance/upload-loan-allocation','class'=>'ss-form-processing','files'=>true]) !!}
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Upload allocations file') !!}
                    {!! Form::file('allocations_file',['class'=>'form-control','required'=>true]) !!}
                  </div>
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  
                  </div>
                  @if(session('missallocations'))
                  <div class="alert alert-danger alert-dismissible ss-messages-box" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      The following beneficiaries are not our students. Please remove them from your CSV file.<br>
                    @foreach(session('missallocations') as $key=>$stud)
                       {{ ($key+1) }}. {{ $stud }}  <br>
                    @endforeach
                 </div><!-- end of ss-messages_box -->
                 @endif

                  @if(session('existing_beneficiaries'))
                  <div class="alert alert-danger alert-dismissible ss-messages-box" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      The following students have allocations in this batch. Please remove them from your CSV file or indicate their correct batch. <br>
                    @foreach(session('existing_beneficiaries') as $key=>$stud)
                       {{ ($key+1) }}. {{ $stud }} <br>
                    @endforeach
                 </div><!-- end of ss-messages_box -->
                 @endif
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
             

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

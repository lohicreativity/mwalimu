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
            <h1>{{ __('Internal Transfer') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Internal Transfer') }}</li>
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
                     $registration_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Registration number',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'registration/internal-transfer','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter student\'s registration number') !!}
                    {!! Form::text('registration_number',null,$registration_number) !!}
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
              <div class="card">
              <div class="card-header">
                <h3 class="card-title">Students' Internal Transfer</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'registration/submit-internal-transfer','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 <table class="table table-bordered">
                    <tr>
                       <td>Student:</td>
                       <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</td>
                    </tr>
                    <tr>
                       <td>Registration Number:</td>
                       <td>{{ $student->registration_number }}</td>
                    </tr>
                    <tr>
                       <td>Current Programme:</td>
                       <td>
                          {{ $student->campusProgram->program->name }}
                       </td>
                    </tr>
                 </table><br>
                 <div class="form-group ss-margin-top">
                   {!! Form::label('','Select new programme') !!}
                   <select name="campus_program_id" class="form-control" required>
                      <option value="">Select New Programme</option>
                      @foreach($campus_programs as $program)
                      @if($admitted_program_id != $program->id)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endif
                      @endforeach
                    </select>
                 </div>  
                 {!! Form::input('hidden','student_id',$student->id) !!}
              </div>
              <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Transfer the Student') }}</button>
              </div>
              {!! Form::close() !!}
            </div>
            @endif
            @if(count($transfers) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Internal Transfers</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
			       {!! Form::open(['url'=>'application/internal-transfers-submission','class'=>'ss-form-processing']) !!}
                   <table class="table table-bordered" id="ss-transfers">
                     <thead>
                       <tr>
					     <th>SN</th>
                         <th>Name</th>
                         <th>Previous Reg Number</th>
                         <th>Previous Programme</th>
						 <th>Current Reg Number</th>
                         <th>Current Programme</th>
                         <th>Date Transfered</th>
						 <th>Status</th>
                       </tr>
                     </thead>
                     <tbody>
                      @foreach($transfers as $key=>$transfer)
                       <tr>
					     <td>{{ ($key+1) }} </td>
                         <td>{{ $transfer->student->first_name }} {{ $transfer->student->middle_name }} {{ $transfer->student->surname }}</td>
                         <td>{{ $transfer->student->applicant->user->username }}</td>
                         <td>{{ $transfer->previousProgram->program->name }}</td>
						 <td>{{ $transfer->student->registration_number }}</td>
                         <td>{{ $transfer->currentProgram->program->name }}</td>
                         <td>{{ date('Y-m-d',strtotime($transfer->created_at)) }}</td>
                         <td>{{ $transfer->status }} {!! Form::input('hidden','transfer_'.$transfer->id,$transfer->id) !!}</td>
                       </tr>
                       @endforeach
					   <tr>
					     <td colspan="8"><button type="submit" class="btn btn-primary">Submit Transfers to Regulators</button></td>
					   </tr>
					   {!! Form::close() !!}
                     </tbody>
                   </table>

                   <div class="ss-pagination-links">
                      {!! $transfers->render() !!}
                   </div> 
              </div>
			  @else{
				@if(count($transfers) != 0)
				<div class="card">
				  <div class="card-header">
					<h3 class="card-title">Internal Transfers</h3>
				  </div>
				  <!-- /.card-header -->
				  <div class="card-body">
					   {!! Form::open(['url'=>'application/internal-transfers-submission','class'=>'ss-form-processing']) !!}
					   <table class="table table-bordered" id="ss-transfers">
						 <thead>
						   <tr>
							 <th>SN</th>
							 <th>Name</th>
							 <th>Previous Reg Number</th>
							 <th>Previous Programme</th>
							 <th>Current Reg Number</th>
							 <th>Current Programme</th>
							 <th>Date Transfered</th>
							 <th>Status</th>
						   </tr>
						 </thead>
						 <tbody>
						  @foreach($transfers as $key=>$transfer)
						   <tr>
							 <td>{{ ($key+1) }} </td>
							 <td>{{ $transfer->student->first_name }} {{ $transfer->student->middle_name }} {{ $transfer->student->surname }}</td>
							 <td>{{ $transfer->student->applicant->user->username }}</td>
							 <td>{{ $transfer->previousProgram->program->name }}</td>
							 <td>{{ $transfer->student->registration_number }}</td>
							 <td>{{ $transfer->currentProgram->program->name }}</td>
							 <td>{{ date('Y-m-d',strtotime($transfer->created_at)) }}</td>
							 <td>{{ $transfer->status }} {!! Form::input('hidden','transfer_'.$transfer->id,$transfer->id) !!}</td>
						   </tr>
						   @endforeach
						   <tr>
							 <td colspan="8"><button type="submit" class="btn btn-primary">Submit Transfers to Regulators</button></td>
						   </tr>
						   {!! Form::close() !!}
						 </tbody>
					   </table>

					   <div class="ss-pagination-links">
						  {!! $transfers->render() !!}
					   </div> 
				  </div>
				</div>
				@endif
				  
			  }
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

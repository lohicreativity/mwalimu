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
            <h1 class="m-0">TAMISEMI Applicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">TAMISEMI Applicants</a></li>
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
                 <h3 class="card-title">{{ __('Retrieve TAMISEMI Applicants') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/get-tamisemi-applicants','class'=>'ss-form-processing']) !!}
                    <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Application Window') !!}
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} </option>
                        @endforeach
                     </select>
                   </div>
                   <div class="form-group col-6">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                      <option value="">Select Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                  </div>
                 </div>
                   <div class="ss-form-actions">
                    <input type="submit" name="action" class="btn btn-primary" value="Retrieve From NACTVET">
					<input type="submit" name="action" class="btn btn-primary" value="Search Qualified">
					<input type="submit" name="action" class="btn btn-primary" value="Search Unqualified">
                   </div>
 
                  {!! Form::close() !!}
               </div>
             </div>
             <!-- /.card -->     
			 
			 @if(count($applicants) != 0)
			 <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('TAMISEMI Applicants') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
			      <table class="table table-bordered ss-paginated-table">
				     <thead>
					     <tr>
						    <th>Name</th>
							<th>Sex</th>
							<th>Index Number</th>
							<th>Programme</th>
							<th>Campus</th>
							<th>Phone</th>
						 </tr>
					 </thead>
					 <tbody>
					     @foreach($applicants as $applicant)
						 <tr>
						    <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
							<td>{{ $applicant->gender }}</td>
							<td>{{ $applicant->index_number }}</td>
							<td>@if(count($applicant->selections) != 0) {{ $applicant->selections[0]->campusProgram->program->name }} @else N/A @endif</td>
							<td>{{ $applicant->campus->name }}</td>
							<td>{{ $applicant->phone }}</td>
						 </tr>
						 @endforeach
					 <tbody>
				  </table>
			   </div>
			  </div>
			 @endif

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

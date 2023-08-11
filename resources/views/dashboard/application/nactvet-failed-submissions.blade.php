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
            <h1 class="m-0">NACTVET Error Cases</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">NACTVET Error Cases</a></li>
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
                 <h3 class="card-title">{{ __('Retrieve Error Cases') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/get-nactvet-error-cases','class'=>'ss-form-processing']) !!}
                    <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Programme Level') !!}
                     <select name="programme_level_id" class="form-control" required>
                        <option value="">Select Programme Level</option>
                        @foreach($awards as $award)
                        <option value="{{ $award->id }}" @if($request->get('programme_level_id') == $award->id) selected="selected" @endif>{{ $award->name }} </option>
                        @endforeach
                     </select>
                   </div>
                   <div class="form-group col-6">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                      <option value="">Select Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->code }}</option>
                      @endforeach
                    </select>
                  </div>
                 </div>
                   <div class="ss-form-actions">
                    <input type="submit" name="action" class="btn btn-primary" value="Retrieve Error Cases">
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
						    <th>SN</th>
                            <th>Name</th>
							<th>Sex</th>
							<th>Index Number</th>
							<th>Phone</th>
                            <th>Award</th>            
							<th>Programme</th>
                            <th>Reason</th>            
							<th>Action</th>
						 </tr>
					 </thead>
					 <tbody>
					     @foreach($applicants as $key => $applicant)
						 <tr>
                            <td>{{ ($key + 1) }}</td>
						    <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
							<td>{{ $applicant->gender }}</td>
							<td>{{ $applicant->index_number }}</td>
                            <td>{{ $applicant->phone }}</td>
                            <td>{{ $applicant->programLevel->name }}</td>
							<td>{{ $applicant->campusProgram->code }}</td>
                            <td>{{ $error->remark }}</td>
							<td> Hii bado </td>

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

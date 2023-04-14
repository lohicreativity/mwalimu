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
            <h1 class="m-0">Special Registration</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Registered Students</a></li>
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
                 <h3 class="card-title">{{ __('Search Student') }}</h3>
               </div>
              <!-- /.card-header -->
               <div class="card-body">
                 @php
                     $ac_yr = [
                         'class'=>'form-control',
                         'placeholder'=>'',
                         'required'=>true
                     ];
                     $semster = [
                         'class'=>'form-control',
                         'placeholder'=>'',
                         'required'=>true
                     ];
                     $keyword = [
                         'class'=>'form-control',
                         'placeholder'=>'index number, registration number or surname',
                         'required'=>true
                     ];					 
                 @endphp			   
                  {!! Form::open(['url'=>'application/special-registration','class'=>'ss-form-processing','method'=>'GET']) !!}
					<div class="row">
					 <div class="form-group col-4">
						{!! Form::label('','Academic year') !!}
						{!! Form::text('ac_yr',$ac_year->academicYear->year,$ac_yr) !!}
					  </div>
					  <div class="form-group col-4">
						{!! Form::label('','Semester') !!}
						{!! Form::text('semester',$semester->name,$semster) !!}
					  </div>
					  <div class="form-group col-4">
						{!! Form::label('','Search Keyword') !!}
						{!! Form::text('keyword',null,$keyword) !!}
					  </div>						  
					</div>
                    <div class="ss-form-actions">
						<button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
					</div>
                  {!! Form::close() !!} 
               </div>
             </div>
             <!-- /.card -->
             		   
            @if($student || $applicant)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Register Student') }}</h3><br>
               </div>
               <!-- /.card-header -->
               <div class="card-body">

                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>Phone</th>						  
						  <th>Index# </th>					  
                          <th>Registration#</th>
                          <th>Programme</th>
                          <th>Status</th>						  
                          <th>Action</th>						  
                        </tr>
                    </thead>
                    <tbody>
				@if($student)
                   <tr>
					  <td>{{ ucwords(strtolower($student->first_name)) }} {{ ucwords(strtolower($student->middle_name)) }} {{ ucwords(strtolower($student->surname)) }}</td>
                      <td>{{ $student->gender }}</td>
                      <td>{{ $student->phone }}</td>					  
					  <td>{{ $student->applicant->index_number }}</td>
                      <td>{{ $student->registration_number }}</td>					  
                      <td>{{ $student->campusProgram->program->code }}</td>
                      <td>{{ ucwords(strtolower($student->academicStatus->name)) }}</td>					  
					  <td><a href="{{ url('application/manual-registration?type=student&keyword='.$student->id) }}" class="btn btn-primary">Register</a></td>
                   </tr>
				@elseif($applicant)   
                   <tr>
					  <td>{{ ucwords(strtolower($applicant->first_name)) }} {{ ucwords(strtolower($applicant->middle_name)) }} {{ ucwords(strtolower($applicant->surname)) }}</td>
                      <td>{{ $applicant->gender }}</td>
                      <td>{{ $applicant->phone }}</td>					  
					  <td>{{ $applicant->index_number }}</td>
                      <td> NA </td>					  
                      <td>
						@foreach($applicant->selections as $selection)
							@if($selection->status == 'SELECTED')
								{{ $selection->campusProgram->program->code }}
							@endif
						@endforeach
					  </td>
					  <td>{{ ucwords(strtolower($applicant->status)) }}</td>					  
					  <td><a href="{{ url('application/manual-registration?type=applicant&keyword='.$applicant->id) }}" class="btn btn-primary">Register</a></td>					  
                   </tr>
				@endif
                   </tbody>
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

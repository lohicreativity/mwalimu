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
            <h1 class="m-0">Clearance List</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
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
        
        <!-- Main row -->
        <div class="row">
          <div class="col-12">

             <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Select Study Academic Year') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/clearance','class'=>'ss-form-processing','method'=>'GET']) !!}
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                    </div>
                    <div class="form-group col-6">
                    {!! Form::label('','Programme level') !!}
                    <select name="award_id" class="form-control" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                       <option value="{{ $award->id }}">{{ $award->name }}</option>
                       @endif
                      @endforeach
                    </select>
                  </div>
                   </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
            
            @if($study_academic_year)
            <div class="card">
              <div class="card-header">
                 <h3 class="card-title">Clearance List</h3>
              </div>
              <div class="card-body">
                
                  {!! Form::open(['url'=>'academic/clearance','method'=>'GET']) !!}

                  {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for student name or registration number">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!} 

                {!! Form::open(['url'=>'academic/bulk-clearance','class'=>'ss-form-processing']) !!}

                {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                 <table class="table table-bordered ss-paginated-table">
                   <thead>
                    <tr>
                      <th>Name</th>
                      <th>Reg. No.</th>
                      <th>Mobile Phone </th>
                      <th>Programme</th>
                      <th>Status</th>
                      @if(Auth::user()->hasRole('finance-officer'))
                      <th>Action</th>
                      @endif
                      @if(Auth::user()->hasRole('librarian'))
                      <th>Action</th>
                      @endif
                      @if(Auth::user()->hasRole('dean-of-students'))
                      <th>Action</th>
                      @endif
                      @if(Auth::user()->hasRole('hod'))
                      <th>Action</th>
                      @endif
                    </tr>
                   </thead>
                   <tbody>
                    @foreach($clearances as $clearance)
                     <tr>
                       <td>{{ $clearance->student->first_name }} {{ $clearance->student->middle_name }} {{ $clearance->student->surname }}</td>
                       <td>{{ $clearance->student->registration_number }}</td>
					   <td>{{ $clearance->student->phone }}</td>
                       <td>{{ $clearance->student->campusProgram->program->code }}</td>

                       @if(Auth::user()->hasRole('examination-officer') || Auth::user()->hasRole('arc') || Auth::user()->hasRole('administrator'))
                       <td>@if($clearance->library_status === 1 && $clearance->hostel_status === 1 && $clearance->finance_status === 1 && $clearance->hod_status === 1 ) 
								<span class="badge badge-success">
									Cleared
								</span>
							@else
								<span class="badge badge-danger">
									Pending
								</span>
                       
                       @endif </td>
					   @endif
					   
                       @if(Auth::user()->hasRole('finance-officer'))
                       <td>@if($clearance->finance_status === 0) 
						   	<span class="badge badge-danger">
								Not Cleared
							</span>
						   @elseif($clearance->finance_status === null)
								<span class="badge badge-success">
									Pending
								</span>
						   @endif</td>
                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id,true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','finance-officer') !!}
                       </td>
                       @endif
                       @if(Auth::user()->hasRole('librarian'))
                       <td>@if($clearance->library_status === 0) 
						   	<span class="badge badge-danger">
								Not Cleared
							</span>
						   @elseif($clearance->library_status === null)
								<span class="badge badge-success">
									Pending
								</span>
						   @endif</td>
                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id,true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','librarian') !!}
                       </td>
                       @endif
                       @if(Auth::user()->hasRole('dean-of-students'))
                      <td>@if($clearance->hostel_status === 0) 
						   	<span class="badge badge-danger">
								Not Cleared
							</span>
						   @elseif($clearance->hostel_status === null)
								<span class="badge badge-success">
									Pending
								</span>
						   @endif</td>

                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id, true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','dean-of-students') !!}
                       </td>
                       @endif
                      @if(Auth::user()->hasRole('hod'))
                      <td>@if($clearance->hod_status === 0) 
						   	<span class="badge badge-danger">
								Not Cleared
							</span>
						   @elseif($clearance->hod_status === null)
								<span class="badge badge-success">
									Pending
								</span>
						   @endif</td>

                       <td>
                        {!! Form::checkbox('clearance_'.$clearance->id,$clearance->id, true) !!}
                        {!! Form::input('hidden','clear_'.$clearance->id,$clearance->id) !!}
                        {!! Form::input('hidden','group','hod') !!}
                       </td>
                       @endif
                       
                     </tr>
                    @endforeach
                      <tr>
                      <td colspan="8">
					  @if(Auth::user()->hasRole('hod') || Auth::user()->hasRole('dean-of-students') || Auth::user()->hasRole('librarian') || Auth::user()->hasRole('finance-officer'))
                        <button type="submit" class="btn btn-primary">Clear All Selected</button>

					  @endif
					  </td>
                    </tr> 
                   </tbody>
                 </table>
                {!! Form::close() !!}
              </div>
              
            </div>
          </div>
           @endif
        </div>
        <!-- /.row (main row) -->
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

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
            <h1>{{ __('Graduants List') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Graduants List') }}</li>
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
                <ul class="nav nav-tabs">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/run-graduants?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Run Graduants') }}</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ url('academic/graduants?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Graduants List') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('academic/excluded-graduants?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Excluded List') }}</a></li>
                </ul>
              </div>
              <!-- /.card-header -->
            </div>

            <div class="card">
                 <div class="card-body">
                 {!! Form::open(['url'=>'academic/graduants','class'=>'ss-form-processing','method'=>'GET']) !!}

                  @if(Auth::user()->hasRole('arc'))
                   <div class="row">
                   <div class="form-group col-4">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Programme level') !!}
                    <select name="program_level_id" class="form-control" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                      <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($request->get('campus_id') == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  </div>
                   @else
                   <div class="row">
                   <div class="form-group col-6">
				    {!! Form::input('hidden','campus_id',$request->get('campus_id')) !!}
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
                    <select name="program_level_id" class="form-control" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                      <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>
                  </div>
                  @endif
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>
				  
                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($graduants) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Graduants - {{ $study_academic_year->academicYear->year }}</h3><br>
				@foreach($graduants as $graduant)
					@if($graduant->status == 'GRADUATING')
						<a href="{{ url('academic/download-graduant-list?study_academic_year_id='.$study_academic_year->id.'&program_level_id='.$request->get('program_level_id').'&campus_id='.$request->get('campus_id')) }}" class="btn btn-primary">Download Book List</a>
						<a href="{{ url('academic/download-graduant-list-cert?study_academic_year_id='.$study_academic_year->id) }}" class="btn btn-primary">Download Certificates List</a>
					@endif
					@break
				@endforeach

              </div>
              <!-- /.card-header -->
              <div class="card-body">
                {!! Form::open(['url'=>'academic/graduants','method'=>'GET']) !!}
                {!! Form::input('hidden','study_academic_year_id',$request->get('study_academic_year_id')) !!}

                {!! Form::input('hidden','campus_id',$request->get('campus_id')) !!}

                {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for graduant name">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!}

                {!! Form::open(['url'=>'academic/approve-graduants','class'=>'ss-form-processing']) !!}

                {!! Form::input('hidden','study_academic_year_id',$request->get('study_academic_year_id')) !!}

                {!! Form::input('hidden','campus_id',$request->get('campus_id')) !!}

                {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                <table id="example2" class="table table-bordered table-hover ss-margin-top">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Reg. No.</th>
                    <th>Names</th>
                    <th>Sex</th>
                    <th>Phone</th>
                    <th>Programme</th>
                    <th>Status</th>
                    <th>GPA</th>
                    @if(Auth::user()->hasRole('arc'))
                    <th>Action</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>
				  @if(Auth::user()->hasRole('arc'))
					{ $key = 1 }
					@foreach($graduants as $graduant)
						@if($graduant->status != 'GRADUATING')
							<tr>
							  <td>{{ ($key++) }}</td>
							  <td>{{ $graduant->student->registration_number }}</td>
							  <td>{{ $graduant->student->first_name }} {{ $graduant->student->middle_name }} {{ $graduant->student->surname }}</td>
							  <td>{{ $graduant->student->gender }}</td>
							  <td>{{ $graduant->student->phone }}</td>
							  <td>{{ $graduant->student->campusProgram->program->code }}</td>
							  <td>@if($graduant->reason == 'Disapproved') Disapproved @elseif($graduant->status == 'PENDING') Pending @endif</td>
							  <td>{{ bcdiv($graduant->student->overallRemark->gpa,1,1) }}</td>
							  <td>
								@if($graduant->status == 'PENDING')
								   {!! Form::checkbox('graduant_'.$graduant->id,$graduant->id,true) !!}
								@elseif($graduant->reason == 'Disapproved')
								   {!! Form::checkbox('graduant_'.$graduant->id,$graduant->id,false) !!}
								@endif
								{!! Form::input('hidden','grad_'.$graduant->id,$graduant->id) !!}
							  </td>
							</tr>
						@endif
                    @endforeach 
					@if(count($graduants) > 0)
                    <tr>
                      <td colspan="9">
                        <button type="submit" class="btn btn-primary">Save Approvals</button>
                      </td>
                    </tr>
					@endif
				@else
					@foreach($graduants as $key=>$graduant)
						<tr>
						  <td>{{ ($key+1) }}</td>
						  <td>{{ $graduant->student->registration_number }}</td>
						  <td>{{ $graduant->student->first_name }} {{ $graduant->student->middle_name }} {{ $graduant->student->surname }}</td>
						  <td>{{ $graduant->student->gender }}</td>
						  <td>{{ $graduant->student->phone }}</td>
						  <td>{{ $graduant->student->campusProgram->program->code }}</td>
						  <td>@if($graduant->status == 'GRADUATING') Approved @elseif($graduant->status == 'PENDING') Pending @elseif($graduant->status == 'Disapproved') Disapproved @endif</td>
						  <td>{{ bcdiv($graduant->student->overallRemark->gpa,1,1) }}</td>
						</tr>
					@endforeach 
                @endif         
                  </tbody>
                </table>
                {!! Form::close()!!}
                <div class="ss-pagination-links">
                    {!! $graduants->appends($request->except('page'))->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Graduants Obtained') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
              </div>
            </div>
            <!-- /.card -->
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

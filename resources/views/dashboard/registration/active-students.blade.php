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
            <h1 class="m-0">Registered Students - {{ $semester->name }}</h1>
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
                 <h3 class="card-title">{{ __('Select Academic Year') }}</h3>
               </div>
              <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'registration/active-students','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id || $year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                   <div class="form-group col-6">
                    {!! Form::label('','Programme Level') !!}
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
                   <div class="ss-form-actions">
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                   </div>
 
                  {!! Form::close() !!} 
               </div>
             </div>
             <!-- /.card -->
             		   
            @if(count($active_students) != 0)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Active Students') }}</h3><br>
				 <a href="{{ url('registration/download-active-students') }}" class="btn btn-primary">Download List</a>
               </div>
               <!-- /.card-header -->
               <div class="card-body">

                  <table class="table table-bordered ss-margin-top ss-paginated-table">
                    <thead>
                        <tr>
                          <th>SN</th>
                          <th>Name</th>
                          <th>Gender</th>
						  <th>Form IV Index No. </th>
						  <th>Form VI Index No./AVN </th>						  
                          <th>Registration Number</th>
                          <th>Programme</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($active_students as $key=>$reg)
                   <tr>
					  <td>{{($key+1)}} </td>
                      <td>{{ $reg->student->first_name }} {{ $reg->student->middle_name }} {{ $reg->student->surname }}</td>
                      <td>{{ $reg->student->gender }}</td>
					  <td>{{ $reg->student->applicant->index_number }}</td>
					  <td>
						@php($fiv_index = null)
						@php($avn = null)
						
						@foreach($reg->student->applicant->nectaResultDetails as $detail)
							@if($detail->exam_id == 2) @php ($fiv_index = $detail->index_number) @endif
						@endforeach
						@foreach($reg->student->applicant->nacteResultDetails as $detail)
							 @php ($avn = $detail->avn)
						@endforeach 
						
						@if(!empty($fiv_index) && empty($avn)) {{ $fiv_index }}
						@elseif(empty($fiv_index) && !empty($avn)) {{ $avn }}
						@elseif(!empty($fiv_index) && !empty($avn)) {{ $fiv_index}}; <br>{{ $avn}}
						@endif
					  </td>
                      <td>{{ $reg->student->registration_number }}</td>
                      <td>{{ $reg->student->campusProgram->program->code }}</td>
                   </tr>
                 @endforeach
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

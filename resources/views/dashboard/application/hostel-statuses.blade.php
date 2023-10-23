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
            <h1 class="m-0">Hostel Status</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Hostel Status</a></li>
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
                 <h3 class="card-title">{{ __('Select Application Window') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/hostel-statuses','class'=>'ss-form-processing','method'=>'GET']) !!}
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
                    {!! Form::label('','Program Level') !!}
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
             
             @if(count($applicants) != 0)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Applicants with Hostel Status') }}</h3><br>
                 <a href="{{ url('application/download-hostel-status?application_window_id='.$request->get('application_window_id').'&program_level_id='.$request->get('program_level_id')) }}" class="btn btn-primary">Download Hostel Status</a>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                   {!! Form::open(['url'=>'application/insurance-statuses','method'=>'GET']) !!}
                    {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                     {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for applicant name">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!}
                   {!! Form::open(['url'=>'application/update-hostel-status-admin','class'=>'ss-form-processing']) !!}
                     {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                     {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                   <table class="table table-bordered table-condensed">
                     <thead>
                       <tr>
                         <th>Applicant</th>
                         <th>Sex</th>
                         <th>Programme</th>
                         <th>Category</th>
                         <th>Status</th>
                         <th>Action</th>
                       </tr>
                     </thead>
                     <tbody>
                       @foreach($applicants as $applicant)
                       <tr>
                         <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                         <td>{{ $applicant->gender }}</td>
                         <td>{{ $applicant->selections[0]->campusProgram->program->name }}</td>
                         <td>
                             @if($applicant->hostel_status === 1)
                                On Campus
                             @elseif($applicant->hostel_status === 2)
                                Off Campus
                             @elseif($applicant->hostel_status == 3)
                                Any
                             @else
                                None
                             @endif
                         </td>
                         <td>
                             @if($applicant->hostel_available_status === 1)
                                <span style="font-size: 11pt" class="badge badge-success">Allocated</span>
                             @elseif($applicant->hostel_available_status === 0)
                                <span style="font-size: 11pt" class="badge badge-danger">Not Allocated</span>
							 @else
								<span style="font-size: 11pt" class="badge badge-warning">Pending</span>
                             @endif
                         </td>
                         <td>
 
                          &nbsp; &nbsp; &nbsp;{!! Form::checkbox('applicant_'.$applicant->id,$applicant->id) !!}

							            {!! Form::input('hidden','app_'.$applicant->id,$applicant->id) !!}
                         </td>
                       </tr>
                       @endforeach
                       <tr>
                         <td colspan="6"><button type="submit" class="btn btn-primary">Save</button></td>
                       </tr>
                     </tbody>
                   </table>
                   {!! Form::close() !!}
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

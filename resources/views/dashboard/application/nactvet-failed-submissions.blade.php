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
            @if ($flag == 'initial')
            <h1 class="m-0">Regulator Failed Cases</h1>
            @elseif($flag == 'NACTVET')
            <h1 class="m-0">NACTVET Error Cases</h1>
            @elseif($flag=='TCU')
            <h1 class="m-0">TCU Correction Cases</h1>
            @endif
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              @if($flag == 'initial')
              <li class="breadcrumb-item active"><a href="#">Regulator Failed Cases</a></li>
              @elseif($flag == 'NACTVET')
              <li class="breadcrumb-item active"><a href="#">NACTVET Error Cases</a></li>
              @elseif($flag=='TCU')
              <li class="breadcrumb-item active"><a href="#">TCU Correction Cases</a></li>
              @endif
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
         @if($flag != 'TCU')
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Retrieve Error Cases') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                 @if ($flag == 'initial')
                 {!! Form::open(['url'=>'application/regulator-failed-case/get','class'=>'ss-form-processing']) !!}
                 <div class="row">
                 <div class="form-group col-6">
                   {!! Form::label('','Application Window') !!}
                  <select name="window_id" class="form-control" required>
                     <option value="">Application Window</option>
                     @foreach($windows as $window)
                             <option value="{{ $window->id }}">{{ $window->begin_date }} - {{ $window->end_date }}  </option>
                     @endforeach
                  </select>
                </div>
                <div class="form-group col-6">
                 {!! Form::label('','Regulator cases') !!}
                 <select name="regulator_case" class="form-control" required>
                   <option value="">Regulator cases</option>
                   @foreach($failed_cases as $case)
                   <option value="{{ $case }}">{{ $case }}</option>
                   @endforeach
                 </select>
               </div>
              </div>
                <div class="ss-form-actions">
                 <input type="submit" name="action" class="btn btn-primary" value="Search Failed Cases">
                </div>

               {!! Form::close() !!}
                 @elseif($flag == 'NACTVET')
                  {!! Form::open(['url'=>'application/nactvet-error-cases/get','class'=>'ss-form-processing']) !!}
                    <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Programme Level') !!}
                     <select name="program_level_id" class="form-control" required>
                        <option value="">Select Programme Level</option>
                        @foreach($awards as $award)
                            @if($award->id <= 2 || $award->id == 4)
                                <option value="{{ $award->id }}">{{ $award->name }} </option>
                            @endif
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
                  @endif
               </div>
             </div>
             @endif
             <!-- /.card -->

			 @if(count($applicants) != 0)
			 <div class="card">
               <div class="card-header">
                @if($flag == 'NACTVET')
                 <h3 class="card-title">{{ __('NACTVET Error Cases') }}</h3>
                @elseif($flag == 'TCU')
                <h3 class="card-title">{{ __('TCU Error Cases') }}</h3>
                @endif
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
                @if($flag == 'NACTVET')
                <th>Reason</th>
                <th>Status</th>
                @endif
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
                    <td>@if($applicant->program_level_id == 1) BTC @elseif($applicant->program_level_id == 2) OD @else BD @endif</td>
                    @if($flag != 'TCU')
                    <td>{{ $applicant->remarks }}</td>
                    <td> @if(empty($applicant->submission_status)) PENDING @else {{$applicant->submission_status}} @endif </td>
                    @endif
                    <td>
                      @if($flag == 'NACTVET')
                      {!! Form::open(['url'=>'application/resubmit-nactvet-error-cases','class'=>'ss-form-processing']) !!}
                        @if($applicant->program_level_id == 1 || $applicant->program_level_id == 2)
                          {!! Form::checkbox('applicant_ids[]',$applicant->id,true) !!}
                        @endif </td>
                </tr>
                @elseif($flag == 'TCU')
                {!! Form::open(['url'=>'application/resubmit-tcu-correction-cases','class'=>'ss-form-processing']) !!}
                @if($applicant->program_level_id == 4)
                  {!! Form::checkbox('applicant_ids[]',$applicant->id,true) !!}
                @endif </td>
        </tr>
        @endif
						 @endforeach

                @if($errors_status  > 0)
                <tr>
                  <td colspan="9"><button type="submit" class="btn btn-primary">Re-submit</button></td>
                </tr>
                @endif
					 <tbody>
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

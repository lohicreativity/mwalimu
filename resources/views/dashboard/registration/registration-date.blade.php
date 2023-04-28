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
            <h1>{{ __('Registration Deadline') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Registration Deadline') }}</li>
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
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'registration/registration-deadline','class'=>'ss-form-processing','method'=>'GET']) !!}
                  @php                
                   $campus_id = [
                      'class'=>'form-control',
                      'placeholder'=>'Campus name',
                      'readonly'=>true
                   ];
                  @endphp                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>
                          {{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))                  
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @else
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @else disabled='disabled' @endif>
                       {{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @endif
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
             
            @if($study_academic_year && $campus)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Registration deadline for {{ $campus->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              @if(!$registration_date)
              {!! Form::open(['url'=>'registration/store-registration-deadline','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                @php
                if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator')){                 
                   $date = [
                      'placeholder'=>'Registration deadline',
                      'class'=>'form-control ss-datepicker',
                      'required'=>true
                   ];
                  }else{
                  $date = [
                      'placeholder'=>'Registration deadline',
                      'class'=>'form-control',
                      'readonly'=>true,
                      'required'=>true
                   ];
                  }
                @endphp
                   
                <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','New registration deadline') !!}
                    {!! Form::text('registration_date',null,$date) !!}

                    {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                    {!! Form::input('hidden','campus_id',$campus->id) !!}
                    {!! Form::input('hidden','name','New Registration Period') !!}
                  </div>
                  </div>
                
              </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Create Registration Deadline') }}</button>
                </div>
              {!! Form::close() !!}

              @else
               {!! Form::open(['url'=>'registration/update-registration-deadline','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                @php
                  if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator')){                 
                   $date = [
                      'placeholder'=>'Registration deadline',
                      'class'=>'form-control ss-datepicker',
                      'required'=>true
                   ];
                  }else{
                  $date = [
                      'placeholder'=>'Registration deadline',
                      'class'=>'form-control',
                      'readonly'=>true,
                      'required'=>true
                   ];
                  }
                @endphp
                   
                <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Registration deadline') !!}
                    {!! Form::text('registration_date',App\Utils\DateMaker::toStandardDate($registration_date->date),$date) !!}

                    {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                    {!! Form::input('hidden','campus_id',$campus->id) !!}
                    {!! Form::input('hidden','special_date_id',$registration_date->id) !!}
                    {!! Form::input('hidden','name','New Registration Period') !!}
                  </div>
                  </div>
                
              </div>
              @if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator'))              
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
              @endif  
              {!! Form::close() !!}
              @endif
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

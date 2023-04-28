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
            <h1>{{ __('Orientation Date') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Orientation Date') }}</li>
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
                 {!! Form::open(['url'=>'registration/orientation-date','class'=>'ss-form-processing','method'=>'GET']) !!}
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
                  <div class="form-group col-6">
                  
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @else disabled="disabled" @endif>{{ $cp->name }}</option>
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
             
            @if($study_academic_year && $campus)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Orientation date for {{ $campus->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              @if(!$orientation_date)
              {!! Form::open(['url'=>'registration/store-orientation-date','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                @php
                   $date = [
                      'placeholder'=>'Orientation date',
                      'class'=>'form-control ss-datepicker',
                      'required'=>true
                   ];
                @endphp
                   
                <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Orientation date') !!}
                    {!! Form::text('orientation_date',null,$date) !!}

                    {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                    {!! Form::input('hidden','campus_id',$campus->id) !!}
                    {!! Form::input('hidden','name','Orientation') !!}
                  </div>
                  </div>
                
              </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Create Orientation Date') }}</button>
                </div>
              {!! Form::close() !!}

              @else
               {!! Form::open(['url'=>'registration/update-orientation-date','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                @php
                   $date = [
                      'placeholder'=>'Orientation date',
                      'class'=>'form-control ss-datepicker',
                      'required'=>true
                   ];
                @endphp
                   
                <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Orientation date') !!}
                    {!! Form::text('orientation_date',App\Utils\DateMaker::toStandardDate($orientation_date->date),$date) !!}

                    {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                    {!! Form::input('hidden','campus_id',$campus->id) !!}
                    {!! Form::input('hidden','special_date_id',$orientation_date->id) !!}
                    {!! Form::input('hidden','name','Orientation') !!}
                  </div>
                  </div>
                
              </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
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

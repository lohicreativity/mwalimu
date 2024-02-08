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
            <h1>{{ __('Departments') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Departments') }}</li>
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

            @can('add-department')
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Department') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $abbreviation = [
                     'placeholder'=>'Abbreviation',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $description = [
                     'placeholder'=>'Description',
                     'class'=>'form-control',
                     'rows'=>2
                  ];
              @endphp
              {!! Form::open(['url'=>'academic/department/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                    <div class="form-group col-8">
                      {!! Form::label('','Name') !!}
                      {!! Form::text('name',null,$name) !!}
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Abbreviation') !!}
                      {!! Form::text('abbreviation',null,$abbreviation) !!}
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-12">
                      {!! Form::label('','Description') !!}
                      {!! Form::textarea('description',null,$description) !!}
                    </div>
                  </div>

                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))
                  <div class="row">
                  <div class="form-group col-4">
                      {!! Form::label('','Campus') !!}
                      <!-- <select name="campuses[]" class="form-control ss-select-tags" multiple="multiple"> -->
                      <select name="campus_id" class="form-control" id="campuses" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                      <option value="">Select Campus</option>
                      @foreach($campuses as $cp)
                      <option value="{{ $cp->id }}" @if($staff->campus_id == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
                      @endforeach
                      </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Type') !!}
                      <select name="unit_category_id" class="form-control" id="unit-categories" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                        <option value="">Select Type</option>
                        @foreach($unit_categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>

                        @endforeach
                      </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Parent',array('id' => 'parent-label')) !!}
                      <div id="parent_input"></div>
                      <select name="parent_id" id="parents" class="form-control">
                        <option value="">Select Parent</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    {!! Form::input('hidden','decoy_id',$staff->campus_id,['id'=>'campus_id']) !!}
                  </div>
                  @elseif(Auth::user()->hasRole('admission-officer'))
                  <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Type') !!}
                      <select name="unit_category_id" class="form-control" id="unit-categories" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                        <option value="">Select Type</option>
                        @foreach($unit_categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Parent',array('id' => 'parent-label')) !!}
                      <div id="parent_input"></div>
                      <select name="parent_id" id="parents" class="form-control">
                        <option value="">Select Parent</option>
                        @foreach($all_departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    {!! Form::input('hidden','campus_id',$staff->campus_id,['id'=>'campus_id']) !!}
                  </div>
                  @endif
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Department') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @livewire('general-settings.departments')

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

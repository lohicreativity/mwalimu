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
            <h1>{{ __('Add Staff') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Add Staff') }}</li>
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

            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Staff') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $first_name = [
                     'placeholder'=>'First name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $middle_name = [
                     'placeholder'=>'Middle name',
                     'class'=>'form-control',
                  ];

                  $surname = [
                     'placeholder'=>'Surname',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $block = [
                     'placeholder'=>'Block',
                     'class'=>'form-control'
                  ];

                  $floor = [
                     'placeholder'=>'Floor',
                     'class'=>'form-control'
                  ];

                  $room = [
                     'placeholder'=>'Room',
                     'class'=>'form-control'
                  ];

                  $vote_number = [
                     'placeholder'=>'Vote number',
                     'class'=>'form-control'
                  ];

                  $check_number = [
                     'placeholder'=>'Check number',
                     'class'=>'form-control'
                  ];

                  $pf_number = [
                     'placeholder'=>'PF number',
                     'class'=>'form-control'
                  ];

                  $nin = [
                     'placeholder'=>'NIN',
                     'class'=>'form-control'
                  ];

                  $address = [
                     'placeholder'=>'Address',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $email = [
                     'placeholder'=>'Email',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $phone = [
                     'placeholder'=>'Phone',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $street = [
                     'placeholder'=>'Street',
                     'class'=>'form-control'
                  ];

                  $birth_date = [
                     'placeholder'=>'Birth date',
                     'class'=>'form-control ss-datepicker',
                     'required'=>true
                  ];

                  $staff_title = [
                     'placeholder'=>'Title',
                     'class'=>'form-control'
                  ];

              @endphp
              {!! Form::open(['url'=>'staff/staff/store','class'=>'ss-form-processing','files'=>true]) !!}
                <div class="card-body">
                
                <fieldset>
                  <legend>Personal Details</legend>
                  <div class="row">
                     <div class="form-group col-1">
                       {!! Form::label('','Title') !!}
                       <select name="title" class="form-control">
                         <option value="Mr.">Mr.</option>
                         <option value="Mrs.">Mrs.</option>
                         <option value="Ms.">Ms.</option>
                         <option value="Dr.">Dr.</option>
                         <option value="Prof.">Prof.</option>
                       </select>
                    </div>
                     <div class="form-group col-4">
                       {!! Form::label('','First name') !!}
                       {!! Form::text('first_name',null,$first_name) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Middle name (Optional)') !!}
                       {!! Form::text('middle_name',null,$middle_name) !!}
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Surname') !!}
                       {!! Form::text('surname',null,$surname) !!}
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Email') !!}
                       {!! Form::email('email',null,$email) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Phone') !!}
                       {!! Form::text('phone',null,$phone) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Birth date') !!}
                       {!! Form::text('birth_date',null,$birth_date) !!}
                    </div>
                  </div>
                   <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Gender') !!}
                       <select name="gender" class="form-control" required>
                         <option value="">Select Gender</option>
                         <option value="M">Male</option>
                         <option value="F">Female</option>
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Disability status') !!}
                       <select name="disability_status_id" class="form-control" required>
                         <option value="">Disability Status</option>
                         @foreach($disabilities as $status)
                         <option value="{{ $status->id }}">{{ $status->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                </fieldset>
                <fieldset>
                  <legend>Contact Details</legend>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Address') !!}
                       {!! Form::text('address',null,$address) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Country') !!}
                       <select name="country_id" class="form-control" required id="ss-select-countries" data-target="#ss-select-regions" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-regions') }}">
                         <option value="">Select Country</option>
                         @foreach($countries as $country)
                         <option value="{{ $country->id }}">{{ $country->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Region') !!}
                       <select name="region_id" class="form-control" required id="ss-select-regions" data-target="#ss-select-districts" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-districts') }}">
                         <option value="">Select Region</option>
                         @foreach($regions as $region)
                         <option value="{{ $region->id }}">{{ $region->name }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','District') !!}
                       <select name="district_id" class="form-control" required id="ss-select-districts" data-target="#ss-select-wards" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-wards') }}">
                         <option value="">Select District</option>
                         @foreach($districts as $district)
                         <option value="{{ $district->id }}">{{ $district->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Ward') !!}
                       <select name="ward_id" class="form-control" required id="ss-select-wards" data-token="{{ session()->token() }}">
                         <option value="">Select Ward</option>
                         @foreach($wards as $ward)
                         <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                         @endforeach
                       </select>
                    </div>
                     <div class="form-group col-6">
                       {!! Form::label('','Street (Optional)') !!}
                       {!! Form::text('street',null,$street) !!}
                    </div>
                  </div>
                </fieldset>
                <fieldset>
                  <legend>Work Details</legend>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Campus') !!}
                       <select name="campus_id" class="form-control" required>
                         <option value="">Select Campus</option>
                         @foreach($campuses as $campus)
                         <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Block (Optional)') !!}
                       {!! Form::text('block',null,$block) !!}
                    </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Floor (Optional)') !!}
                       {!! Form::text('floor',null,$floor) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Room (Optional)') !!}
                       {!! Form::text('room',null,$room) !!}
                    </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','NIN (Optional)') !!}
                       {!! Form::text('nin',null,$nin) !!}
                    </div>
                     <div class="form-group col-6">
                       {!! Form::label('','Designation') !!}
                       <select name="designation_id" class="form-control" required>
                         <option value="">Select Designation</option>
                         @foreach($designations as $designation)
                         <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','PF number (Optional)') !!}
                       {!! Form::text('pf_number',null,$pf_number) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Employment Type') !!}
                       <select name="schedule" class="form-control" required>
                         <option value="">Select Schedule</option>
                         <option value="FULLTIME">FULL TIME</option>
                         <option value="PARTTIME">PART TIME</option>
                       </select>
                    </div>
                  </div>
                  <div class="row">
                  <div class="form-group col-6">
                       {!! Form::label('','Staff category') !!}
                       <select name="category" class="form-control" required>
                         <option value="">Select Category</option>
                         <option value="ACADEMIC">ACADEMIC</option>
                         <option value="NON-ACADEMIC">NON-ACADEMIC</option>
                       </select>
                    </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Upload Staff Image (Optional)') !!}
                    {!! Form::file('image',['class'=>'form-control']) !!}
                  </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Department') !!}
                       <select name="department_id" class="form-control" required>
                         <option value="">Select Department</option>
                         @foreach($departments as $department)
                         <option value="{{ $department->id }}">{{ $department->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                </fieldset>
                  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Staff') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            
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

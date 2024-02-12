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
            <h1>{{ __('Edit Staff') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Edit Staff') }}</li>
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
                <h3 class="card-title">{{ __('Edit Staff') }}</h3>
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
                     'class'=>'form-control'
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
                     'class'=>'form-control',
                     'required'=>true
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
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $birth_date = [
                     'placeholder'=>'Birth date',
                     'class'=>'form-control ss-datepicker',
                     'required'=>true
                  ];

                  $edit_staff_title = [
                     'placeholder'=>'Title',
                     'class'=>'form-control'
                  ];
              @endphp
              {!! Form::open(['url'=>'staff/staff/update','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                
                <fieldset>
                  <legend>Personal Details</legend>
                  <div class="row">
                     <div class="form-group col-1">
                       {!! Form::label('','Title') !!}
                       <select name="title" class="form-control">
                         <option value="Mr." @if($edit_staff->title == 'Mr.') selected="selected" @endif>Mr.</option>
                         <option value="Mrs." @if($edit_staff->title == 'Mrs.') selected="selected" @endif>Mrs.</option>
                         <option value="Ms." @if($edit_staff->title == 'Ms.') selected="selected" @endif>Ms.</option>
                         <option value="Dr." @if($edit_staff->title == 'Dr.') selected="selected" @endif>Dr.</option>
                         <option value="Prof." @if($edit_staff->title == 'Prof.') selected="selected" @endif>Prof.</option>
                       </select>
                    </div>
                     <div class="form-group col-4">
                       {!! Form::label('','First name') !!}
                       {!! Form::text('first_name',$edit_staff->first_name,$first_name) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Middle name') !!}
                       {!! Form::text('middle_name',$edit_staff->middle_name,$middle_name) !!}
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Surname') !!}
                       {!! Form::text('surname',$edit_staff->surname,$surname) !!}

                       {!! Form::input('hidden','staff_id',$edit_staff->id) !!}
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Email') !!}
                       {!! Form::email('email',$edit_staff->email,$email) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Phone') !!}
                       {!! Form::text('phone',$edit_staff->phone,$phone) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Birth date') !!}
                       {!! Form::text('birth_date',App\Utils\DateMaker::toStandardDate($edit_staff->birth_date),$birth_date) !!}
                    </div>
                  </div>
                   <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Gender') !!}
                       <select name="gender" class="form-control" required>
                         <option value="">Select Gender</option>
                         <option value="M" @if($edit_staff->gender == 'M') selected="selected" @endif>Male</option>
                         <option value="F" @if($edit_staff->gender == 'F') selected="selected" @endif>Female</option>
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Disability status') !!}
                       <select name="disability_status_id" class="form-control" required>
                         <option value="">Disability Status</option>
                         @foreach($disabilities as $status)
                         <option value="{{ $status->id }}" @if($edit_staff->disability_status_id == $status->id) selected="selected" @endif>{{ $status->name }}</option>
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
                       {!! Form::text('address',$edit_staff->address,$address) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Country') !!}
                       <select name="country_id" class="form-control" required id="ss-select-countries" data-target="#ss-select-regions" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-regions') }}">
                         <option value="">Select Country</option>
                         @foreach($countries as $country)
                         <option value="{{ $country->id }}" @if($edit_staff->country_id == $country->id) selected="selected" @endif>{{ $country->name }}</option>
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
                         <option value="{{ $region->id }}" @if($edit_staff->region_id == $region->id) selected="selected" @endif>{{ $region->name }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','District') !!}
                       <select name="district_id" class="form-control" required id="ss-select-districts" data-target="#ss-select-wards" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-wards') }}">
                         <option value="">Select District</option>
                         @foreach($districts as $district)
                         <option value="{{ $district->id }}" @if($edit_staff->district_id == $district->id) selected="selected" @endif>{{ $district->name }}</option>
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
                         <option value="{{ $ward->id }}" @if($edit_staff->ward_id == $ward->id) selected="selected" @endif>{{ $ward->name }}</option>
                         @endforeach
                       </select>
                    </div>
                     <div class="form-group col-6">
                       {!! Form::label('','Street') !!}
                       {!! Form::text('street',$edit_staff->street,$street) !!}
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
                         <option value="{{ $campus->id }}" @if($edit_staff->campus_id == $campus->id) selected="selected" @endif>{{ $campus->name }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Block') !!}
                       {!! Form::text('block',$edit_staff->block,$block) !!}
                    </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Floor') !!}
                       {!! Form::text('floor',$edit_staff->floor,$floor) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Room') !!}
                       {!! Form::text('room',$edit_staff->room,$room) !!}
                    </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','NIN') !!}
                       {!! Form::text('nin',$edit_staff->nin,$nin) !!}
                    </div>
                     <div class="form-group col-6">
                       {!! Form::label('','Designation') !!}
                       <select name="designation_id" class="form-control" required>
                         <option value="">Select Designation</option>
                         @foreach($designations as $designation)
                         <option value="{{ $designation->id }}" @if($edit_staff->designation_id == $designation->id) selected="selected" @endif>{{ $designation->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','PF number') !!}
                       {!! Form::text('pf_number',$edit_staff->pf_number,$pf_number) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Work schedule') !!}
                       <select name="schedule" class="form-control" required>
                         <option value="">Select Schedule</option>
                         <option value="FULLTIME" @if($edit_staff->schedule == 'FULLTIME') selected="selected" @endif>FULL TIME</option>
                         <option value="PARTTIME" @if($edit_staff->schedule == 'PARTTIME') selected="selected" @endif>PART TIME</option>
                       </select>
                    </div>
                  </div>
                  <div class="row">
                  <div class="form-group col-6">
                       {!! Form::label('','Staff category') !!}
                       <select name="category" class="form-control" required>
                         <option value="">Select Category</option>
                         <option value="ACADEMIC" @if($edit_staff->category == 'ACADEMIC') selected="selected" @endif>ACADEMIC</option>
                         <option value="NON-ACADEMIC" @if($edit_staff->category == 'NON-ACADEMIC') selected="selected" @endif>NON-ACADEMIC</option>
                       </select>
                    </div>
                    <div class="form-group col-6">
                    {!! Form::label('','Upload staff image') !!}
                    {!! Form::file('image',['class'=>'form-control']) !!}
                  </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Department') !!}
                       <select name="department_id" class="form-control" required>
                         <option value="">Select Department</option>
                         @foreach($departments as $department)
                         <option value="{{ $department->id }}" @if($department->id == $edit_staff->department_id) selected="selected" @endif>{{ $department->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                </fieldset>
                  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
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

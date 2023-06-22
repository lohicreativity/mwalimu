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
            <h1>{{ __('Next Of Kin') }} - {{ $campus->name }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Next Of Kin') }}</li>
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
             
           @if($next_of_kin)
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Next of Kin') }}</h3>
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

                  $nationality = [
                     'placeholder'=>'Nationality',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $nin = [
                     'placeholder'=>'NIN',
                     'class'=>'form-control'
                  ];

                  $address = [
                     'placeholder'=>'1234',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $email = [
                     'placeholder'=>'nextofkin@live.com',
                     'class'=>'form-control',
                  ];

                  $phone = [
                     'placeholder'=>'0739000000',
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

              @endphp
              {!! Form::open(['url'=>'application/next-of-kin/update','class'=>'ss-form-processing','files'=>true]) !!}
                <div class="card-body">
                
                <fieldset>
                  <legend>Personal Details</legend>
                  <div class="row">
                     <div class="form-group col-4">
                       {!! Form::label('','First name') !!}
                       {!! Form::text('first_name',$next_of_kin->first_name,$first_name) !!}

                       {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                       {!! Form::input('hidden','next_of_kin_id',$next_of_kin->id) !!}
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Middle name (Optional)') !!}
                       {!! Form::text('middle_name',$next_of_kin->middle_name,$middle_name) !!}
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Surname') !!}
                       {!! Form::text('surname',$next_of_kin->surname,$surname) !!}
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Email (Optional)') !!}
                       {!! Form::email('email',$next_of_kin->email,$email) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Phone') !!}
					   @php
						  $next_of_kin_phone = null;
						  if($next_of_kin->phone != null){
							$next_of_kin_phone = "0".substr($next_of_kin->phone,3);  
						  }
					   @endphp					   
                       {!! Form::text('phone',$next_of_kin_phone,$phone) !!}
                    </div>
                  </div>
                   <div class="row">
                    <div class="form-group col-4">
                       {!! Form::label('','Gender') !!}
                       <select name="gender" class="form-control" required>
                         <option value="">Select Gender</option>
                         <option value="M" @if($next_of_kin->gender == 'M') selected="selected" @endif>Male</option>
                         <option value="F" @if($next_of_kin->gender == 'F') selected="selected" @endif>Female</option>
                       </select>
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Nationality') !!}
                       <select name="nationality" class="form-control" required>
                         <option value="">Select Nationality</option>
                         @foreach($countries as $country)
                         <option value="{{ $country->nationality }}" @if($next_of_kin->nationality == $country->nationality) selected="selected" @endif>{{ $country->nationality }}</option>
                         @endforeach					 
                       </select>
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Relationship') !!}
                       <select name="relationship" class="form-control" required>
                         <option value="">Select Relationship</option>
                         <option value="Father" @if($next_of_kin->relationship == 'Father') selected="selected" @endif>Father</option>
                         <option value="Mother" @if($next_of_kin->relationship == 'Mother') selected="selected" @endif>Mother</option>
                         <option value="Uncle" @if($next_of_kin->relationship == 'Uncle') selected="selected" @endif>Uncle</option>
                         <option value="Aunt" @if($next_of_kin->relationship == 'Aunt') selected="selected" @endif>Aunt</option>
                         <option value="Brother" @if($next_of_kin->relationship == 'Brother') selected="selected" @endif>Brother</option>
                         <option value="Sister" @if($next_of_kin->relationship == 'Sister') selected="selected" @endif>Sister</option>
                         <option value="Guardian" @if($next_of_kin->relationship == 'Guardian') selected="selected" @endif>Guardian</option>
                         @if(str_contains(strtolower($applicant->programLevel->name),'masters')) <option value="Spouse" @if($next_of_kin->relationship == 'Spouse') selected="selected" @endif>Spouse</option> @endif
                       </select>
                    </div>
                  </div>
                </fieldset>
                <fieldset>
                  <legend>Contact Details</legend>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Address') !!}
					   @php
						  $next_of_kin_address = null;
						  if($next_of_kin->address != null){
							$next_of_kin_address = substr($next_of_kin->address, 9);  
						  }
					   @endphp					   
                       {!! Form::text('address',$next_of_kin_address,$address) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Country') !!}
                       <select name="country_id" class="form-control" required id="ss-select-countries" data-target="#ss-select-regions" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-regions') }}">
                         <option value="">Select Country</option>
                         @foreach($countries as $country)
                         <option value="{{ $country->id }}" @if($next_of_kin->country_id == $country->id) selected="selected" @endif>{{ $country->name }}</option>
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
                         <option value="{{ $region->id }}" @if($next_of_kin->region_id == $region->id) selected="selected" @endif>{{ $region->name }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','District') !!}
                       <select name="district_id" class="form-control" required id="ss-select-districts" data-target="#ss-select-wards" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-wards') }}">
                         <option value="">Select District</option>
                         @foreach($districts as $district)
                         <option value="{{ $district->id }}" @if($next_of_kin->district_id == $district->id) selected="selected" @endif>{{ $district->name }}</option>
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
                         <option value="{{ $ward->id }}" @if($next_of_kin->ward_id == $ward->id) selected="selected" @endif>{{ $ward->name }}</option>
                         @endforeach
                       </select>
                    </div>
                     <div class="form-group col-6">
                       {!! Form::label('','Street (Optional)') !!}
                       {!! Form::text('street',$next_of_kin->street,$street) !!}
                    </div>
                  </div>
                </fieldset>               
                  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @else
              <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Basic Information') }}</h3>
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

                  $nationality = [
                     'placeholder'=>'Nationality',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $nin = [
                     'placeholder'=>'NIN',
                     'class'=>'form-control'
                  ];

                  $address = [
                     'placeholder'=>'1234',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $email = [
                     'placeholder'=>'Email',
                     'class'=>'form-control',
                  ];

                  $phone = [
                     'placeholder'=>'0739000000',
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

              @endphp
              {!! Form::open(['url'=>'application/next-of-kin/store','class'=>'ss-form-processing','files'=>true]) !!}
                <div class="card-body">
                
                <fieldset>
                  <legend>Personal Details</legend>
                  <div class="row">
                     <div class="form-group col-4">
                       {!! Form::label('','First name') !!}
                       {!! Form::text('first_name',null,$first_name) !!}

                       {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                    </div>
                    <div class="form-group col-4">
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
                       {!! Form::label('','Email (Optional)') !!}
                       {!! Form::email('email',null,$email) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Phone') !!}					   
                       {!! Form::text('phone',null,$phone) !!}
                    </div>
                  </div>
                   <div class="row">
                    <div class="form-group col-4">
                       {!! Form::label('','Gender') !!}
                       <select name="gender" class="form-control" required>
                         <option value="">Select Gender</option>
                         <option value="M">Male</option>
                         <option value="F">Female</option>
                       </select>
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Nationality') !!}
                       <select name="nationality" class="form-control" required>
                         <option value="">Select Nationality</option>
                         @foreach($countries as $country)
                         <option value="{{ $country->nationality }}">{{ $country->nationality }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Relationship') !!}
                       <select name="relationship" class="form-control" required>
                         <option value="">Select Relationship</option>
                         <option value="Father">Father</option>
                         <option value="Mother">Mother</option>
                         <option value="Uncle">Uncle</option>
                         <option value="Aunt">Aunt</option>
                         <option value="Brother">Brother</option>
                         <option value="Sister">Sister</option>
                         <option value="Guardian">Guardian</option>
                       </select>
                    </div>
                  </div>
                </fieldset>
                <fieldset>
                  <legend>Contact Details</legend>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Mailing Address') !!}
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
                  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
              {!! Form::close() !!}
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

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
          <div class="col-sm-8">
            <h3>{{ __('Basic Information') }} - {{ $campus->name }} - {{ $applicant->index_number }}</h3>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Basic Information') }}</li>
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

            <!-- Need to add a filter to prevent applicants from viewing selection status after applicants have been retrieved from a regulator -->
            @if($regulator_selection && $selection_released_status->selection_released == 1)

              @if($check_selected_applicant)
                  @if($check_selected_applicant->selections[0]->status == 'PENDING' && $applicant->status == 'NOT SELECTED')
                    <div class="alert alert-danger">
                      <h3 class="text-white" style="font-size: 18px!important;">
                        <i class="fa fa-times-circle"></i> 
                        Sorry, you have not been selected this round. Please <a href="{{ url('application/select-programs?other_attempt=true') }}">click here</a> to select a new programme for the next round.
                      </h3>
                    </div>
                  @elseif($applicant->confirmation_status != 'CANCELLED' && $applicant->status == 'SELECTED')
                      <div class="alert alert-success">
                        <h3 class="text-white" style="font-size: 18px!important;"><i class="fa fa-check-circle"></i> 
                        Congratulations! You have been successfully selected for {{ $check_selected_applicant->selections[0]->campusProgram->program->name }} programme. @if($applicant->multiple_admissions === null || $applicant->multiple_admissions === 0) Please wait for admission package OR <a href="{{ url('application/admission-confirmation') }}">Cancel Admission</a> @elseif($applicant->confirmation_status === null) Please <a href="{{ url('application/admission-confirmation') }}">click here</a> to confirm with us.@endif</h3>
                      </div>
                  @elseif($applicant->status == 'ADMITTED' && !$student)
                      <div class="alert alert-success">

                      @if($applicant->confirmation_status != 'CANCELLED')
                      <h3 class="text-white" style="font-size: 18px!important;"><i class="fa fa-check-circle"></i> 
                        Congratulations! You have been successfully admitted to {{ $check_selected_applicant->selections[0]->campusProgram->program->name }} programme.
                        OR
                      </h3> 
                      {!! Form::open(['url'=>'application/cancel-admission','class'=>'ss-form-processing']) !!}
                              {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                                <button type="submit" class="btn btn-danger">{{ __('Cancel Admission') }}</button>
                      {!! Form::close() !!}
                      @else
                      <h3 class="text-white" style="font-size: 18px!important;"><i class="fa fa-check-circle"></i> 
                        Congratulations! You have been successfully cancelled from joining to {{ $check_selected_applicant->selections[0]->campusProgram->program->name }} programme.
                        
                      </h3> 
                      {!! Form::open(['url'=>'application/restore-cancelled-admission','class'=>'ss-form-processing']) !!}

                      {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                         <button type="submit" class="btn btn-primary">{{ __('Restore Cancelled Admission') }}</button>
                      {!! Form::close() !!}
                      @endif
                      </div>
                  @elseif($student)
                      <div class="alert alert-success">
                        <h3 class="text-white" style="font-size: 18px!important;"><i class="fa fa-check-circle"></i> 
                        Congratulations for a successful registration. Your registration number is <strong>{{ $student->registration_number }}</strong>. You MUST change your password by <a href="{{ url('change-password') }}">clicking here to access your student account.</a> </h3>
                      </div>               
                  @endif
              @else
                  <div class="alert alert-danger">
                    <h3 class="text-white" style="font-size: 18px!important;">
                      <i class="fa fa-times-circle"></i> 
                      Sorry, you have not been selected in this round. Please <a href="{{ url('application/select-programs?other_attempt=true') }}">click here</a> to select a new programme for the next round.
                    </h3>
                  </div> 					   
              @endif
            @elseif($applicant->status == 'SUBMITTED')
            <div class="alert alert-success">
                    <h3 class="text-white" style="font-size: 18px!important;">
                      <i class="fa fa-check-circle"></i> 
                      Your application has been received and we are finalizing selection process. Please bear with us.
                    </h3>
                    </div> 	
            @endif

            
            
           

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
                     'readonly'=>App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)? true : null,
                     'required'=>true
                  ];

                  $middle_name = [
                     'placeholder'=>'Middle name',
                     'class'=>'form-control',
                     'readonly'=>App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)? true : null,
                  ];

                  $surname = [
                     'placeholder'=>'Surname',
                     'class'=>'form-control',
                     'readonly'=>App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)? true : null,
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
                     'placeholder'=>'3918',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $email = [
                     'placeholder'=>'Email',
                     'class'=>'form-control',
                     'required'=>true
                  ];
                  
                  if($applicant->status == 'ADMITTED'){
                      $first_name = [
                         'placeholder'=>'First name',
                         'class'=>'form-control',
                         'readonly'=>true,
                         'required'=>true
                      ];

                      $middle_name = [
                         'placeholder'=>'Middle name',
                         'class'=>'form-control',
                         'readonly'=>true,
                      ];

                      $surname = [
                         'placeholder'=>'Surname',
                         'class'=>'form-control',
                         'readonly'=>true,
                         'required'=>true
                      ];
                      $phone = [
                         'placeholder'=>'0789000000',
                         'class'=>'form-control',
                         'required'=>true
                      ];
                      
                  } else if ($applicant->status == 'SELECTED') {

                    $phone = [
                         'placeholder'=>'0789000000',
                         'class'=>'form-control',
                         'required'=>true
                      ];

                  } else {
                      $first_name = [
                         'placeholder'=>'First name',
                         'class'=>'form-control',
                         'readonly'=>App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)? true : null,
                         'required'=>true
                      ];

                      $middle_name = [
                         'placeholder'=>'Middle name',
                         'class'=>'form-control',
                         'readonly'=>App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)? true : null,
                      ];

                      $surname = [
                         'placeholder'=>'Surname',
                         'class'=>'form-control',
                         'readonly'=>App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)? true : null,
                         'required'=>true
                      ];
                      $phone = [
                         'placeholder'=>'0789000000',
                         'class'=>'form-control',
                         'readonly'=>App\Domain\Application\Models\Applicant::hasRequestedControlNumber($applicant)? true : null,
                         'required'=>true
                      ];
                      
                  }

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
              {!! Form::open(['url'=>'application/update-basic-info','class'=>'ss-form-processing','files'=>true]) !!}
                <div class="card-body">
                @if($applicant->is_tcu_verified != 1 && str_contains(strtolower($applicant->programLevel->name),'degree') 
                    && !str_contains(strtolower($applicant->programLevel->name),'master')  && $applicant->is_transfered != 1)
                <div class="alert alert-warning">
                   You cannot proceed with this application because it seems you have admission with another institution. Please contact TCU for clarification.
                </div>
                @endif
				        @if($applicant->is_tcu_verified === 1 && str_contains(strtolower($applicant->programLevel->name),'degree') 
                    && !str_contains(strtolower($applicant->programLevel->name),'master')  && $applicant->is_transfered == 1)
                <div class="alert alert-warning">
                   You cannot proceed with this transfer because it seems you do not have admission with another institution. Please contact TCU for clarification.
                </div>
                @endif
                <fieldset>
                  <legend>Personal Details</legend>
                  <div class="row">
                     <div class="form-group col-4">
                       {!! Form::label('','First name') !!}
                       {!! Form::text('first_name',$applicant->first_name,$first_name) !!}

                       {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Middle name (Optional)') !!}
                       {!! Form::text('middle_name',$applicant->middle_name,$middle_name) !!}
                    </div>
                    <div class="form-group col-4">
                       {!! Form::label('','Surname') !!}
                       {!! Form::text('surname',$applicant->surname,$surname) !!}
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Email') !!}
                       {!! Form::email('email',$applicant->email,$email) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Phone') !!}
                    @php
                      $applicant_phone = null;
                      if($applicant->phone != null){
                        $applicant_phone = "0".substr($applicant->phone,3);  
                      }
                    @endphp
                       {!! Form::text('phone', $applicant_phone,$phone) !!}
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Birth date') !!}
                      <div class="row">
                       <div class="col-4">
                         <select name="date" class="form-control" required>
                           <option value="">Date</option>
                           @for($i = 1; $i <= 31; $i++)
                           <option value="{{ $i }}" @if(Carbon\Carbon::parse($applicant->birth_date)->format('d') == $i && $applicant->birth_date !== null) selected="selected" @else @if($applicant->status == 'ADMITTED') disabled="disabled" @endif @endif>{{ $i }}</option>
                           @endfor
                         </select>
                       </div>
                       <div class="col-4">
                         <select name="month" class="form-control" required>
                           <option value="">Month</option>
                           @for($i = 1; $i <= 12; $i++)
                           <option value="{{ $i }}" @if(Carbon\Carbon::parse($applicant->birth_date)->format('m') == $i && $applicant->birth_date !== null) selected="selected" @else @if($applicant->status == 'ADMITTED') disabled="disabled" @endif @endif>{{ $i }}</option>
                           @endfor
                         </select>
                       </div>
                       <div class="col-4">
                         <select name="year" class="form-control" required>
                           <option value="">Year</option>
                           @for($i = 2008; $i >= 1960; $i--)
                           <option value="{{ $i }}" @if(Carbon\Carbon::parse($applicant->birth_date)->format('Y') == $i && $applicant->birth_date !== null) selected="selected" @else @if($applicant->status == 'ADMITTED') disabled="disabled" @endif @endif>{{ $i }}</option>
                           @endfor
                         </select>
                       </div>
                     </div>
                    </div>
                  </div>
                   <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Gender') !!}
                       <select name="sex" class="form-control" @if($applicant->status == 'ADMITTED' || App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)) disabled="true" @endif  required>
                         <option value="">Select Gender</option>
                         <option value="M" @if($applicant->gender == 'M') selected="selected" @else @if($applicant->status == 'ADMITTED') disabled="disabled" @endif @endif>Male</option>
                         <option value="F" @if($applicant->gender == 'F') selected="selected" @else @if($applicant->status == 'ADMITTED') disabled="disabled" @endif @endif>Female</option>
                       </select>

                       @if($applicant->status == 'ADMITTED' || App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant))
                       {!! Form::input('hidden','sex',$applicant->gender) !!}
                       @endif
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Disability status') !!}
                       <select name="disability_status_id" class="form-control" required>
                         <option value="">Disability Status</option>
                         @foreach($disabilities as $status)
                         <option value="{{ $status->id }}" @if($status->id == $applicant->disability_status_id) selected="selected" @endif>{{ $status->name }}</option>
                         @endforeach
                       </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                       {!! Form::label('','Nationality') !!}
                       <select name="nationality" class="form-control" required>
                         <option value="">Select Nationality</option>
                         @foreach($countries as $country)
                         <option value="{{ $country->nationality }}" @if($applicant->nationality == $country->nationality) selected="selected" @else @if(App\Domain\Application\Models\Applicant::hasRequestedControlNumber($applicant) || $applicant->payment_complete_status == 1 || $applicant->status == 'SELECTED' || $applicant->status == 'ADMITTED') disabled="disabled" @endif @endif>{{ $country->nationality }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','National Identification Number (Optional)') !!}
                       {!! Form::text('nin',$applicant->nin,$nin) !!}
                    </div>
                  </div><!-- end of row -->
                </fieldset>
                <fieldset>
                  <legend>Contact Details</legend>
                  <div class="row">
                     <div class="form-group col-6">
                       {!! Form::label('','Postal Address') !!}
					   @php
						  $applicant_address = null;
						  if($applicant->address != null){
							$applicant_address = substr($applicant->address, 9);  
						  }
					   @endphp					   
                       {!! Form::text('address', $applicant_address, $address) !!}
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','Country') !!}
                       <select name="country_id" class="form-control" required id="ss-select-countries" data-target="#ss-select-regions" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-regions') }}">
                         <option value="">Select Country</option>
                         @foreach($countries as $country)
                         <option value="{{ $country->id }}" @if($applicant->country_id == $country->id) selected="selected" @endif>{{ $country->name }}</option>
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
                         <option value="{{ $region->id }}" @if($applicant->region_id == $region->id) selected="selected" @endif>{{ $region->name }}</option>
                         @endforeach
                       </select>
                    </div>
                    <div class="form-group col-6">
                       {!! Form::label('','District') !!}
                       <select name="district_id" class="form-control" required id="ss-select-districts" data-target="#ss-select-wards" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-wards') }}">
                         <option value="">Select District</option>
                         @foreach($districts as $district)
                         <option value="{{ $district->id }}" @if($applicant->district_id == $district->id) selected="selected" @endif>{{ $district->name }}</option>
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
                         <option value="{{ $ward->id }}" @if($applicant->ward_id == $ward->id) selected="selected" @endif>{{ $ward->name }}</option>
                         @endforeach
                       </select>
                    </div>
                     <div class="form-group col-6">
                       {!! Form::label('','Street (Optional)') !!}
                       {!! Form::text('street',$applicant->street,$street) !!}
                    </div>
                  </div>
                  
                </fieldset>               
                  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button @if($applicant->is_tcu_verified != 1 && str_contains($applicant->programLevel->name,'Degree') && !str_contains(strtolower($applicant->programLevel->name),'master')  && $applicant->is_transfered != 1) disabled="disabled" @elseif($applicant->is_tcu_verified == 1 && str_contains($applicant->programLevel->name,'Degree') && $applicant->is_transfered == 1) disabled="disabled" @else type="submit" @endif class="btn btn-primary">{{ __('Save') }}</button>
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

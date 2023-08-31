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
            <h1>{{ __('Edit Applicant Details') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Edit Applicant Search') }}</li>
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
                <h3 class="card-title">Search for Applicant</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $index_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Index number',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'application/edit-applicant-details','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter applicant\'s index number') !!}
                    {!! Form::text('index_number',null,$index_number) !!}
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->


            @if($applicant)
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Edit Applicant - {{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }} 
                  <span style="color:red">({{ $applicant->index_number }})</span></h4>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                    @php
                      $index_number = [
                         'placeholder'=>'Form IV Index Number',
                         'class'=>'form-control',
                         'readonly'=> !Auth::user()->hasRole('administrator') && App\Domain\Application\Models\Applicant::hasConfirmedResults($applicant)? true : null,
                         'required'=>true
                      ];

                      $dob = [
                        'placeholder'=>'Date of Birth',
                        'class'=>'form-control ss-datepicker',
                        'readonly'=> !Auth::user()->hasRole('administrator') && App\Domain\Application\Models\Applicant::hasRequestedControlNumber($applicant)? true : null,
                        'required'=>true					  
                      ];
                      $nationality = [
                         'placeholder'=>'Nationality',
                         'class'=>'form-control',
                         'required'=>true
                      ];
                      $email = [
                         'placeholder'=>'Email',
                         'class'=>'form-control',
                         'required'=>true
                      ];					  
                      $phone = [
                         'placeholder'=>'255739000000',
                         'class'=>'form-control',
                         'required'=>true
                      ];
                   @endphp

              {!! Form::open(['url'=>'application/update-applicant-details','class'=>'ss-form-processing']) !!}
                   <div class="row">
                   <div class="form-group col-3">
                       {!! Form::label('','Index Number') !!}
                       {!! Form::text('index_number',$applicant->index_number,$index_number) !!}
                    </div>
                    <div class="form-group col-3">
                        {!! Form::label('','Date of Birth') !!}
                        {!! Form::text('dob',App\Utils\DateMaker::toStandardDate($applicant->birth_date),$dob) !!}					
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Nationality') !!}
                       <select name="nationality" class="form-control" 									
                        @if($applicant->status == null) 
                          @if((App\Domain\Application\Models\Applicant::hasRequestedControlNumber($applicant) || 
                              $applicant->payment_complete_status == 1 || $applicant->submission_complete_status == 1) && !Auth::user()->hasRole('administrator'))
                            disabled="disabled" 
                          @endif 
                        @elseif(!Auth::user()->hasRole('administrator'))
                            disabled="disabled"
                        @endif required>
                        <option value="">Select Nationality</option>
                        @foreach($countries as $country)
                          <option value="{{ $country->nationality }}" 
                            @if($applicant->nationality == $country->nationality) selected="selected" @endif> {{ $country->nationality }}
                          </option>
                         @endforeach
                       </select>
                       @if($applicant->status == null || !Auth::user()->hasRole('administrator')) 
                          @if(App\Domain\Application\Models\Applicant::hasRequestedControlNumber($applicant) || $applicant->payment_complete_status == 1) 
                          {!! Form::input('hidden','citizenship',$applicant->nationality) !!}
                          @endif 
                        @endif
                    </div>
                    <div class="form-group col-3">
                       {!! Form::label('','Email') !!}
                       {!! Form::email('email',$applicant->email,$email) !!}
                    </div>					
                    <div class="form-group col-3">
                       {!! Form::label('','Phone') !!}
                       {!! Form::text('phone',$applicant->phone,$phone) !!}
                    </div>
                     <div class="form-group col-3">
                      {!! Form::label('','Programme level') !!}
                      <select name="program_level_id" class="form-control" 
                      @if($applicant->status != null && !Auth::user()->hasRole('administrator')) disabled="true" @endif required>
                      <option value="">Select Program Level</option>
                        @foreach($awards as $award)
                          @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                            <option value="{{ $award->id }}" @if($applicant->program_level_id == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                          @endif
                        @endforeach
                      </select>
                      </div>
                        <div class="form-group col-3">
                          {!! Form::label('','Entry mode') !!}
                          <select name="entry_mode" class="form-control" 
                          @if(($applicant->status != null || $applicant->submission_complete_status == 1) && !Auth::user()->hasRole('administrator')) disabled="true" @endif required>
                            <option value="">Select Highest Qualification</option>
                            <option value="DIRECT" @if($applicant->entry_mode == 'DIRECT') selected="selected" @endif>Form IV or VI (Direct)</option>
                            <option value="EQUIVALENT" @if($applicant->entry_mode == 'EQUIVALENT') selected="selected" @endif>Certificate or Diploma (Equivalent)</option>
                          </select>
                        </div>
                      </div>
                      {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                    @if($applicant->status == null && !Auth::user()->hasRole('administrator')) 
                        @if(App\Domain\Application\Models\Applicant::hasRequestedControlNumber($applicant) || $applicant->payment_complete_status == 1) 
                            {!! Form::input('hidden','nationality',$applicant->nationality) !!}
                        @endif 
                    @endif
                    @if($applicant->status != null && !Auth::user()->hasRole('administrator'))
                        {!! Form::input('hidden','entry_mode',$applicant->entry_mode) !!}
                        {!! Form::input('hidden','program_level_id',$applicant->program_level_id) !!}				
                    @endif
                        <div class="ss-form-actions">
                        @if($applicant->campus_id != 0)
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                        @endif
                        @if($applicant->programs_complete_status != 0 || $applicant->results_complete_status != 0)
                          <a href="{{ url('application/reset-applicant-results?applicant_id='.$applicant->id) }}" class="btn btn-primary">Reset Results</a>
                        @endif
                        <a href="{{ url('application/reset-applicant-password-default?user_id='.$applicant->user_id.'&applicant_id='.$applicant->id) }}" class="btn btn-primary">Reset Password</a>
                        @if($invoice)
                          <a href="#" id="ss-reset-control-number" data-token="{{ session()->token() }}" data-applicant-id="{{ $applicant->id }}" class="btn btn-primary">Reset Control Number</a>
                        </div>
                        @endif
                        {!! Form::close() !!}
                    @endif

              </div>
            </div>

            



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

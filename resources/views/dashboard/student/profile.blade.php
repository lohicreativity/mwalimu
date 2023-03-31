@extends('layouts.app')

@section('content')

<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/logo.png') }}" alt="{{ Config::get('constants.SITE_NAME') }}" height="60" width="60">
  </div>

  @include('layouts.auth-header')

  @include('layouts.sidebar')

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{ __('Student Details') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Student Details') }}</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-4">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle"
                       src="{{ asset('avatars/'.$student->image) }}"
                       onerror="this.src='{{ asset("img/user-avatar.png") }}'">
                </div>

                <h3 class="profile-username text-center">{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</h3>

                <p class="text-muted text-center">{{ $student->registration_number }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Address</b> <a class="float-right">{{ $student->applicant->address }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Email</b> <a class="float-right">{{ $student->email }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Phone</b> <a class="float-right">{{ $student->phone }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Date of Birth</b> <a class="float-right">{{ $student->birth_date }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Gender</b> <a class="float-right">{{ $student->gender }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Disability Status</b> <a class="float-right">{{ $student->disabilityStatus->name }}</a>
                  </li>
                </ul>
                
       
                <a href="#" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ss-edit-student-profile"><b>Edit Profile</b></a>


                <div class="modal fade" id="ss-edit-student-profile">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit Profile</h4>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">

                          @php

                             $address = [
                                'class'=>'form-control',
                                'placeholder'=>'Address',
                                'required'=>true
                             ];

                             $phone = [
                                'class'=>'form-control',
                                'placeholder'=>'Phone',
                                'required'=>true
                             ];

                          @endphp

                          {!! Form::open(['url'=>'student/edit-details','class'=>'ss-form-processing','files'=>true]) !!}

                          <div class="form-group">
                             {!! Form::label('','Phone') !!}
                             {!! Form::text('phone',$student->phone,$phone) !!}
                          </div>

                          <div class="form-group">
                             {!! Form::label('','Address') !!}
                             {!! Form::text('address',$student->address,$address) !!}
                          </div>

                          <div class="form-group">
                             {!! Form::label('','Email Address') !!}
                             {!! Form::text('email',$student->email,$email) !!}

                             {!! Form::input('hidden','student_id',$student->id) !!}
                          </div>

                          <div class="ss-form-controls">
                            <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                          </div>

                          {!! Form::close() !!}
                          
                        </div>
                        <div class="modal-footer justify-content-between">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                      </div>
                      <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                  </div>
                  <!-- /.modal -->
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
           
          <div class="col-4">
          <!-- About Me Box -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Contact Details</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Country</strong>
                @if($student->applicant->country)
                <p class="text-muted">
                  {{ $student->applicant->country->name }}
                </p>
                @endif

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Region</strong>
                @if($student->applicant->region)
                <p class="text-muted">{{ $student->applicant->region->name }}</p>
                @endif

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> District</strong>
                 @if($student->applicant->district)
                <p class="text-muted">{{ $student->applicant->district->name }}</p>
                 @endif
                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Ward</strong>
                 @if($student->applicant->ward)
                <p class="text-muted">{{ $student->applicant->ward->name }}</p>
                @endif

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Street</strong>

                <p class="text-muted">{{ $student->street }}</p>

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Campus</strong>

                <p class="text-muted">
                  {{ $student->campusProgram->campus->name }}
                </p>

                <hr>

            
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->

          <div class="col-4">
          
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
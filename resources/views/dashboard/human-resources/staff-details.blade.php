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
            <h1>{{ __('Staff Details') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Staff Details') }}</li>
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
                       src="{{ asset('avatars/'.$profile_staff->image) }}"
                       onerror="this.src='{{ asset("img/user-avatar.png") }}'">
                </div>

                <h3 class="profile-username text-center">{{ $profile_staff->title }} {{ $profile_staff->first_name }} {{ $profile_staff->middle_name }} {{ $profile_staff->surname }}</h3>

                <p class="text-muted text-center">{{ $profile_staff->designation->name }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Address</b> <a class="float-right">{{ $profile_staff->address }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Email</b> <a class="float-right">{{ $profile_staff->email }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Phone</b> <a class="float-right">{{ $profile_staff->phone }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Date of Birth</b> <a class="float-right">{{ $profile_staff->birth_date }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Gender</b> <a class="float-right">{{ $profile_staff->gender }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Disability Status</b> <a class="float-right">{{ $profile_staff->disabilityStatus->name }}</a>
                  </li>
                </ul>
                
                @if($profile_staff->id == $staff->id)
                <a href="#" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ss-edit-staff-profile"><b>Edit Profile</b></a>
                @endif


                <div class="modal fade" id="ss-edit-staff-profile">
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

                          {!! Form::open(['url'=>'staff/staff/update-details','class'=>'ss-form-processing','files'=>true]) !!}

                          <div class="form-group">
                             {!! Form::label('','Phone') !!}
                             {!! Form::text('phone',$profile_staff->phone,$phone) !!}
                          </div>

                          <div class="form-group">
                             {!! Form::label('','Address') !!}
                             {!! Form::text('address',$profile_staff->address,$address) !!}
                          </div>

                          <div class="form-group">
                             {!! Form::label('','Upload profile picture') !!}
                             {!! Form::file('image',['class'=>'form-control']) !!}

                             {!! Form::input('hidden','staff_id',$profile_staff->id) !!}
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

                <p class="text-muted">
                  {{ $profile_staff->country->name }}
                </p>

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Region</strong>

                <p class="text-muted">{{ $profile_staff->region->name }}</p>

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> District</strong>

                <p class="text-muted">{{ $profile_staff->district->name }}</p>

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Ward</strong>

                <p class="text-muted">{{ $profile_staff->ward->name }}</p>

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Street</strong>

                <p class="text-muted">{{ $profile_staff->street }}</p>

                <hr>

            
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->

          <div class="col-4">
          <!-- About Me Box -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Work Details</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Campus</strong>

                <p class="text-muted">
                  {{ $profile_staff->campus->name }}
                </p>

                <hr>

                <strong><i class="fas fa-building mr-1"></i> Department</strong>

                <p class="text-muted">{{ $profile_staff->department->name }}</p>

                <hr>

                <strong><i class="fas fa-building mr-1"></i> Block</strong>

                <p class="text-muted">{{ $profile_staff->block }}</p>

                <hr>

                <strong><i class="fas fa-building mr-1"></i> Floor</strong>

                <p class="text-muted">{{ $profile_staff->floor }}</p>

                <hr>

                <strong><i class="fas fa-building mr-1"></i> Room</strong>

                <p class="text-muted">{{ $profile_staff->room }}</p>

                <hr>

                <strong><i class="fas fa-id-card mr-1"></i> PF Number</strong>

                <p class="text-muted">{{ $profile_staff->pf_number }}</p>

                <hr>

                <strong><i class="fas fa-id-card mr-1"></i> National Identification Number</strong>

                <p class="text-muted">{{ $profile_staff->nin }}</p>

                <hr>

                <strong><i class="fas fa-list-alt mr-1"></i> Staff Category</strong>

                <p class="text-muted">{{ $profile_staff->category }}</p>

                <hr>

                <strong><i class="fas fa-clock mr-1"></i> Work Schedule</strong>

                <p class="text-muted">{{ $profile_staff->schedule }}</p>

                <hr>

            
              </div>
              <!-- /.card-body -->
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

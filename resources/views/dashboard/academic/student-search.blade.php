
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
            <h1>{{ __('Student Search') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Student Search') }}</li>
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
                <h3 class="card-title">Search for Student</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $reg_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Index number, registration number or surname',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'academic/student-search','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter search keyword') !!}
                    {!! Form::text('keyword',null,$reg_number) !!}
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->


            @if($student)
              <div class="card">
              <div class="card-header">
                <h3 class="card-title">Search Results</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                   <table class="table table-bordered table-condensed">
                     <tr>
                       <td>First name: </td>
                       <td>{{ $student->first_name }}</td>
                     </tr>
                     <tr>
                       <td>Middle name: </td>
                       <td>{{ $student->middle_name }}</td>
                     </tr>
                     <tr>
                       <td>Surname: </td>
                       <td>{{ $student->surname }}</td>
                     </tr>
                     <tr>
                       <td>Gender: </td>
                       <td>{{ $student->gender }}</td>
                     </tr>
                     <tr>
                       <td>Phone: </td>
                       <td>{{ $student->phone }}</td>
                     </tr>
                     <tr>
                       <td>Address: </td>
                       <td>{{ $student->address }}</td>
                     </tr>
                     <tr>
                       <td><!-- <a href="{{ url('academic/student-profile?registration_number='.$student->registration_number) }}" class="btn btn-primary">View Profile</a>  --><a href="{{ url('student/deceased?student_id='.$student->id) }}" class="btn btn-primary">Deceased</a> <a href="{{ url('student/reset-password?student_id='.$student->id) }}" class="btn btn-primary">Reset Password</a> <a href="{{ url('student/reset-control-number?student_id='.$student->id) }}" class="btn btn-primary">Reset Control Number</a></td>
                       <td></td>
                     </tr>
                   </table>


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

                          {!! Form::open(['url'=>'student/update-details','class'=>'ss-form-processing','files'=>true]) !!}

                          <div class="form-group">
                             {!! Form::label('','Phone') !!}
                             {!! Form::text('phone',$student->phone,$phone) !!}
                          </div>

                          <div class="form-group">
                             {!! Form::label('','Address') !!}
                             {!! Form::text('address',$student->address,$address) !!}
                          </div>

                          <div class="form-group">
                             {!! Form::label('','Studentship Status') !!}
                             <select name="studentship_status_id" class="form-control">
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                             </select>
                          </div>

                          <div class="form-group">
                             {!! Form::label('','Upload profile picture') !!}
                             {!! Form::file('image',['class'=>'form-control']) !!}

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

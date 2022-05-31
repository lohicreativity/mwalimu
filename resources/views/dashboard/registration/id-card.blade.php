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
                         'placeholder'=>'Registration number',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'registration/print-id-card','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter student\'s registration number') !!}
                    {!! Form::text('registration_number',null,$reg_number) !!}
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
                <h3 class="card-title">Search Results</h3><br>
                <a href="{{ url('registration/show-id-card?registration_number='.$student->registration_number) }}" class="btn btn-primary">Preview ID</a>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                   <table class="table table-bordered">
                     <tr>
                         <td>
                           <div id="crop_wrapper">
                              <img src="{{ asset('img/user-avatar.png') }}">
                              <div id="crop_div">
                              </div>
                            </div>
                         </td>
                         <td>
                           <form method="post" action="{{ url('registration/crop-student-image') }}" onsubmit="return crop();">
                              @csrf
                              <input type="hidden" value="" id="top" name="top">
                              <input type="hidden" value="" id="left" name="left">
                              <input type="hidden" value="" id="right" name="right">
                              <input type="hidden" value="" id="bottom" name="bottom">
                              <input type="submit" name="crop_image" value="Crop Image">
                            </form>
                         </td>
                     </tr>
                     <tr>
                       <td>
                         <div id="ss-my-camera"></div>
                         <input type=button value="Configure" onClick="configure()">
                         <input type=button value="Take Snapshot" onClick="take_snapshot()">
                         <input type=button value="Save Snapshot" onClick="saveSnap()">
                       </td>
                       <td>
                         <div id="ss-camera-results"></div>
                       </td>
                     </tr>
                     <tr>
                         <td>First name:</td>
                         <td>{{ $student->first_name }}</td>
                     </tr>
                     <tr>
                         <td>Middle name:</td>
                         <td>{{ $student->middle_name }}</td>
                     </tr>
                     <tr>
                         <td>Surname:</td>
                         <td>{{ $student->surname }}</td>
                     </tr>
                   </table>
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

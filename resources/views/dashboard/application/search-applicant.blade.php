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
            <h1>{{ __('Applicant Search') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Applicant Search') }}</li>
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
                 {!! Form::open(['url'=>'application/search-for-applicant','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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
                <h3 class="card-title">Search Results</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                   <table class="table table-bordered table-condensed">
                     <tr>
                       <td>First name: </td>
                       <td>{{ $applicant->first_name }}</td>
                     </tr>
                     <tr>
                       <td>Middle name: </td>
                       <td>{{ $applicant->middle_name }}</td>
                     </tr>
                     <tr>
                       <td>Surname: </td>
                       <td>{{ $applicant->surname }}</td>
                     </tr>
                     <tr>
                       <td>Gender: </td>
                       <td>{{ $applicant->gender }}</td>
                     </tr>
                     <tr>
                       <td>Phone: </td>
                       <td>{{ $applicant->phone }}</td>
                     </tr>
                     <tr>
                       <td>Address: </td>
                       <td>{{ $applicant->address }}</td>
                     </tr>
                     <tr>
                       <td><a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-reset-password">Reset Password</a> </td>
                       <td></td>
                     </tr>
                   </table>

                   <div class="modal fade" id="ss-reset-password">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Reset Password</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @php
                                 $password = [
                                   'class'=>'form-control',
                                   'placeholder'=>'Password',
                                   'required'=>true
                                 ];

                                 $password_confirmation = [
                                   'class'=>'form-control',
                                   'placeholder'=>'Password confirmation',
                                   'required'=>true
                                 ];
                              @endphp
                              {!! Form::open(['url'=>'application/reset-applicant-password','class'=>'ss-form-processing']) !!}
                              <div class="row">
                                  <div class="form-group col-12">
                                     {!! Form::label('','') !!}
                                     {!! Form::password('password',$password) !!}
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
                               <div class="row">
                                  <div class="form-group col-12">
                                     {!! Form::label('','') !!}
                                     {!! Form::password('password_confirmation',$password_confirmation) !!}

                                     {!! Form::input('hidden','user_id',$applicant->user_id) !!}
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
                               <div class="ss-form-actions">
                                  <button type="submit" class="btn btn-primary">Reset Password</button>
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

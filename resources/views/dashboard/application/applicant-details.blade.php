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
            <h1>{{ __('Applicant Details') }}</h1>
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
                 {!! Form::open(['url'=>'application/applicant-details','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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
                <h3 class="card-title">Basic Information Details</h3>
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
                </table>
              </div>
            </div>
            
            @if($applicant->nextOfKin)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Next of Kin Details</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered table-condensed">
                  <tr>
                    <td>First name: </td>
                    <td>{{ $applicant->nextOfKin->first_name }}</td>
                  </tr>
                  <tr>
                    <td>Middle name: </td>
                    <td>{{ $applicant->nextOfKin->middle_name }}</td>
                  </tr>
                  <tr>
                    <td>Surname: </td>
                    <td>{{ $applicant->nextOfKin->surname }}</td>
                  </tr>
                  <tr>
                    <td>Gender: </td>
                    <td>{{ $applicant->nextOfKin->gender }}</td>
                  </tr>
                  <tr>
                    <td>Phone: </td>
                    <td>{{ $applicant->nextOfKin->phone }}</td>
                  </tr>
                  <tr>
                    <td>Address: </td>
                    <td>{{ $applicant->nextOfKin->address }}</td>
                  </tr>
                </table>
              </div>
            </div>
            @endif

            @if($applicant->payment)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Payment Details</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered table-condensed">
                  <tr>
                    <td>Status: </td>
                    <td>
                      @if($applicant->payment_complete_status == 1)
                        <button class="btn btn-success">PAID</button>
                      @else 
                        <button class="btn btn-danger">NOT PAID</button>
                      @endif
                    </td>
                  </tr>
                </table>
              </div>
            </div>
            @endif         

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Examination Results Details</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered table-condensed">
                  <tr>
                    <td>Form IV Index Number</td>
                    <td>{{ $applicant->index_number }}</td>
                  </tr>
                  @if($a_level)
                  <tr>
                    <td>
                      Form VI Index Number
                    </td>
                    <td>{{ $a_level->index_number }}</td>
                  </tr>
                  @endif
                  @if($applicant->nacte_reg_no)
                  <tr>
                    <td>Nacte Reg No</td>
                    <td>{{ $applicant->nacte_reg_no }}</td>
                  </tr>
                  @endif
                  @if($avn)
                  <tr>
                    <td>AVN</td>
                    <td>{{ $avn->avn }}</td>
                  </tr>
                  @endif
                  @if($out)
                  <tr>
                    <td>OUT Number</td>
                    <td>{{ $out->reg_no }}</td>
                  </tr>
                  @endif
                  @if($applicant->veta_status == 1)
                  <tr>
                    <td>Veta Certificate</td>
                    <td>Yes</td>
                  </tr>
                  @endif
                  @if($applicant->teacher_certificate_status == 1)
                  <tr>
                    <td>Teacher Diploma Certificate</td>
                    <td>Yes</td>
                  </tr>
                  @endif
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

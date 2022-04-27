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
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Upload Documents - {{ $campus->name }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Upload Documents</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-12">
            @if($applicant->payment_complete_status == 0)
            <div class="alert alert-warning">Payment section not completed</div>
            @else
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Upload Documents') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'application/upload-documents','files'=>true,'class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                    <div class="form-group col-4">
                    {!! Form::label('','Select document') !!}
                    <select name="document_name" class="form-control" required>
                      <option value="">Select Document</option>
                      <option value="passport">Passport Size Picture</option>
                      <option value="birth_certificate">Birth Certificate</option>
                      <option value="o_level_certificate">O-Level Certificate</option>
                      @if($applicant->entry_mode == 'DIRECT' && str_contains($applicant->programLevel->name,'Bachelor'))
                      <option value="a_level_certificate">A-Level Certificate</option>
                      @elseif($applicant->entry_mode == 'EQUIVALENT')
                      <option value="diploma_certificate">Diploma Certificate</option>
                      <option value="nva_certificate">NVA Certificate</option>
                      @endif
                    </select>
                    </div>
                  </div>
                  <div class="row">
                     <div class="form-group col-4">
                     {!! Form::label('','Upload document') !!}
                     {!! Form::file('document',['class'=>'form-control','required'=>true]) !!}

                     {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                   </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Upload Document') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Uploaded Documents') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
                <div class="card-body">
                  <table class="table table-bordered">
                    <thead>
                       <tr>
                         <th>Document</th>
                         <th>Action</th>
                       </tr>
                    </thead>
                    <tbody>
                      @if($applicant->birth_certificate)
                      <tr>
                        <td>Birth Certificate</td>
                        <td><a href="{{ url('application/delete-document?name=birth_certificate') }}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td>
                      </tr>
                      @endif
                      @if($applicant->o_level_certificate)
                      <tr>
                        <td>O-Level Certificate</td>
                        <td><a href="{{ url('application/delete-document?name=o_level_certificate') }}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td>
                      </tr>
                      @endif
                      @if($applicant->a_level_certificate)
                      <tr>
                        <td>A-Level Certificate</td>
                        <td><a href="{{ url('application/delete-document?name=a_level_certificate') }}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td>
                      </tr>
                      @endif
                      @if($applicant->diploma_certificate)
                      <tr>
                        <td>Diploma Certificate</td>
                        <td><a href="{{ url('application/delete-document?name=diploma_certificate') }}" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</a></td>
                      </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
              @endif
          </div>
        </div>
        
      </div><!-- /.container-fluid -->
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

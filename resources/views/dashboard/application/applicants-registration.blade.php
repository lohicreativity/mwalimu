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
            <h1 class="m-0">Admitted Applicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Admitted Applicants</a></li>
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

             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">Admitted Applicants</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/applicants-registration','method'=>'GET']) !!}
                  <div class="input-group">
                   <input type="text" name="query" placeholder="Search for applicant name" class="form-control">
                   <input type="text" name="index_number" placeholder="Search for applicant index number" class="form-control">
                   <span class="input-group-btn">
                     <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                   </span>
                  </div>
                  {!! Form::close() !!}
                  <br>
                
                 @if(count($applicants) != 0)
                  <table class="table table-bordered ss-margin-top">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>Programme</th>
                          <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                 @foreach($applicants as $applicant)
                   <tr>
                      <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                      <td>{{ $applicant->gender }}</td>
                      <td>@foreach($applicant->selections as $selection)
                           @if($selection->status == 'SELECTED')
                           {{ $selection->campusProgram->program->name }}
                           @endif
                          @endforeach
                      </td>
                      <td>@foreach($applicant->selections as $selection)
                           @if($selection->status == 'SELECTED')
                           <span class="badge badge-warning">{{ $selection->status }}</span> <a href="{{ url('application/admit-applicant/'.$applicant->id.'/'.$selection->id) }}" class="btn btn-primary">Register</a>
                           @endif
                          @endforeach
                      </td>
                   </tr>
                 @endforeach
                   </tbody>
                  </table>
                  @endif
               </div>
            </div>

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

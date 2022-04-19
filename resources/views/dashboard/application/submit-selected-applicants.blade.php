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
            <h1 class="m-0">Submit Selected Applicants</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Submit Selected Applicants</a></li>
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
                 <h3 class="card-title">{{ __('Submit Selected Applicants') }}</h3>
               </div>

               <!-- /.card-header -->
               <div class="card-body">
                  <table class="table table-bordered ss-margin-top">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Gender</th>
                          <th>Programme</th>
                          <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                  {!! Form::open(['url'=>'application/submit-selected-applicants-tcu']) !!}
                    {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                    {!! Form::input('hidden','program_level_id',$request->get('program_level_id')) !!}
                 @foreach($selected_applicants as $applicant)
                   <tr>
                      <td>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</td>
                      <td>{{ $applicant->gender }}</td>
                      <td>@foreach($applicant->selections as $selection)
                           @if($selection->status == 'APPROVING')
                           {{ $selection->campusProgram->program->name }}
                           @endif
                          @endforeach
                      </td>
                      <td>{!! Form::checkbox('applicant_'.$applicant->id,$applicant->id,true) !!}</td>
                   </tr>
                 @endforeach
                   <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>
                        <button type="submit" class="btn btn-primary">Submit To TCU</button>
                      </td>
                   </tr>
                   {!! Form::close() !!}
                   </tbody>
                  </table>
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

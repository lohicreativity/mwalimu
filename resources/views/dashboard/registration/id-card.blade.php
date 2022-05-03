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
                <h3 class="card-title">Search Results</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                   <div id="ss-id-card" class="ss-id-card" style="width: 750px; height: 450px; background-image: url({{ asset('img/mnma-id-bg.png') }}); padding: 20px;">
                     <div class="row">
                        <div class="col-3 ss-center" style="text-align: center;">
                          <img src="{{ asset('dist/img/logo.png')}}" class="ss-logo" style="width: 100px; text-align: center;">
                        </div>
                        <div class="col-9">
                           <h1>THE MWALIMU NYERERE MEMORIAL ACADEMY</h1>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-3" style="text-align: center;">
                          <img src="{{ asset('img/user-avatar.png')}}" class="ss-logo" style="text-align: center; width: 150px;">
                          @if($semester->name == 'Semester 1')
                          <h3>Semester One</h3>
                          @else
                          <h3>Semester Two</h3>
                          @endif
                        </div>
                        <div class="col-9">
                           <h3 style="margin-top: 20px;">REGNO: {{ $student->registration_number }}</h3>
                           <h3>NAME: {{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</h3>
                           <h3>MOBILE: {{ $student->phone }}</h3>
                           <h3>VALID TO: {{ $study_academic_year->end_date }}</h3>
                        </div>
                     </div>
                     <div class="row">
                     <div class="col-8"></div>
                     <div class="col-4"><h3>{{ $student->campusProgram->campus->name }}</h3></div>
                     </div>
                   </div>
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

  <script type="text/javascript">
    document.getElementById('ss-id-card').onclick = function(e){
        // document.getElementById('ss-id-card').print();
        w = window.open();
        w.document.write(document.getElementById('ss-id-card').innerHTML);
        w.print();
        w.close();
    }
  </script>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection

@extends('layouts.app')

@section('content')

<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/logo.png') }}" alt="{{ Config::get('constants.SITE_NAME') }}" height="60" width="60">
  </div>

  <!-- Content Wrapper. Contains page content -->
  <!-- Content Wrapper. Contains page content -->

   

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          @foreach($students as $student)
          <div class="col-12">
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
                          <img src="{{ asset('img/user-avatar.png')}}" class="ss-avatar" style="text-align: center; width: 150px;">
                          @if($semester->name == 'Semester 1')
                          <h3>Semester One</h3>
                          @else
                          <h3>Semester Two</h3>
                          @endif
                        </div>
                        <div class="col-9">
                           <h3 style="margin-top: 20px;">REGNO: {{ $student->student->registration_number }}</h3>
                           <h3>NAME: {{ $student->student->first_name }} {{ $student->student->middle_name }} {{ $student->student->surname }}</h3>
                           <h3>MOBILE: {{ $student->student->phone }}</h3>
                           <h3>VALID TO: {{ $study_academic_year->end_date }}</h3>
                        </div>
                     </div>
                     <div class="row">
                     <div class="col-8"></div>
                     <div class="col-4"><h3>{{ $student->student->campusProgram->campus->name }}</h3></div>
                     </div>
                   </div>
                   <pagebreak>
                   <div id="ss-id-card" class="ss-id-card" style="width: 750px; height: 450px; background-color: #FFF; padding: 20px;">
                     <div class="row">
                        <div class="col-12">
                           <h1>CAUTION</h1>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-9" style="text-align: left;">
                          <p>This identity card is the property of</p>
                          <h3>THE MWALIMU NYERERE MEMORIAL ACADEMY</h3>
                          <p>1. Use of this card is subject to the card holder agreement</p>
                          <p>2. Card should be returned at the beginning of each semester</p>
                          
                        </div>
                        <div class="col-3">
                           
                        </div>
                     </div>
                     <div class="row">
                     <div class="col-8"></div>
                     <div class="col-4"><h3>{{ $student->student->campusProgram->campus->name }}</h3></div>
                     </div>
                   </div>
                   <pagebreak>
          </div>
          @endforeach
          <!-- /.col -->
        </div>
        <!-- /.row -->

      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->


  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection

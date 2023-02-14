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
            <h1 class="m-0">View Applicant Documents - {{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">View Applicant Documents</a></li>
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
               <div class="card-body">

                <div class="accordion" id="accordionExample-2">
                  @if($applicant->diploma_certificate)
                    <div class="card">
                      <div class="card-header" id="ss-diploma-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseDiplomaCertificate" aria-expanded="true" aria-controls="collapseDiplomaCertificate">
                            Diploma Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseDiplomaCertificate" class="collapse" aria-labelledby="ss-diploma-certificate" data-parent="#accordionExample-2">
                        <div class="card-body">

                          @if(explode('.',$applicant->diploma_certificate)[1] == 'pdf')
                          <iframe
                                src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->diploma_certificate) }}#toolbar=0&scrollbar=0"
                                frameBorder="0"
                                scrolling="auto"
                                height="400px"
                                width="100%"
                            ></iframe>
                          @else
                            <img src="{{ asset('uploads/'.$applicant->diploma_certificate) }}" height="400px" width="100%">
                          @endif
                          
                        </div>
                      </div>
                    </div>
                  @endif
                </div>


                    
                 
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

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
              <li class="breadcrumb-item active"><a href="#">Applicant Certificates</a></li>
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
                @if($applicant->o_level_certificate)
                    <div class="card">
                      <div class="card-header" id="ss-o-level-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOLevel" aria-expanded="true" aria-controls="collapseOLevel">
                            O Level Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseOLevel" class="collapse" aria-labelledby="ss-o-level-certificate" data-parent="#accordionExample-2">
                        <div class="card-body">
                          @if(explode('.',$applicant->o_level_certificate)[1] == 'pdf')
                            <iframe
                                  src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->o_level_certificate) }}#toolbar=0&scrollbar=0"
                                  frameBorder="0"
                                  scrolling="auto"
                                  height="400px"
                                  width="100%"
                              ></iframe>
                          @else
                            <img src="{{ asset('uploads/'.$applicant->o_level_certificate) }}" height="400px" width="100%">
                          @endif
                        </div>
                      </div>
                    </div>
                  @endif

                  @if($applicant->a_level_certificate)
                    <div class="card">
                      <div class="card-header" id="ss-a-level-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseALevel" aria-expanded="true" aria-controls="collapseALevel">
                            A Level Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseALevel" class="collapse" aria-labelledby="ss-a-level-certificate" data-parent="#accordionExample-2">
                        <div class="card-body">
                          @if(explode('.',$applicant->a_level_certificate)[1] == 'pdf')
                            <iframe
                                  src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->a_level_certificate) }}#toolbar=0&scrollbar=0"
                                  frameBorder="0"
                                  scrolling="auto"
                                  height="400px"
                                  width="100%"
                              ></iframe>
                          @else
                            <img src="{{ asset('uploads/'.$applicant->a_level_certificate) }}" height="400px" width="100%">
                          @endif
                        </div>
                      </div>
                    </div>
                  @endif

                  @if(str_contains($applicant->nacte_reg_no,'.pdf'))
                    <div class="card">
                      <div class="card-header" id="ss-basic-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseBasicCertificate" aria-expanded="true" aria-controls="collapseBasicCertificate">
                            Basic Technician Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseBasicCertificate" class="collapse" aria-labelledby="ss-basic-certificate" data-parent="#accordionExample-2">
                        <div class="card-body">
                          @if(explode('.',$applicant->nacte_reg_no)[1] == 'pdf')
                            <iframe
                                  src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->nacte_reg_no) }}#toolbar=0&scrollbar=0"
                                  frameBorder="0"
                                  scrolling="auto"
                                  height="400px"
                                  width="100%"
                              ></iframe>
                          @else
                            <img src="{{ asset('uploads/'.$applicant->nacte_reg_no) }}" height="400px" width="100%">
                          @endif
                        </div>
                      </div>
                    </div>
                  @endif

                  @if($applicant->diploma_certificate)
                    <div class="card">
                      <div class="card-header" id="ss-diploma-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseDiploma" aria-expanded="true" aria-controls="collapseDiploma">
                            Diploma Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseDiploma" class="collapse" aria-labelledby="ss-diploma-certificate" data-parent="#accordionExample-2">
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

                  @if($applicant->teacher_diploma_certificate)
                    <div class="card">
                      <div class="card-header" id="ss-teacher-diploma-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseTeacherDiploma" aria-expanded="true" aria-controls="collapseTeacherDiploma">
                            Teacher Diploma Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseTeacherDiploma" class="collapse" aria-labelledby="ss-teacher-diploma-certificate" data-parent="#accordionExample-2">
                        <div class="card-body">
                          @if(explode('.',$applicant->teacher_diploma_certificate)[1] == 'pdf')
                            <iframe
                                  src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->teacher_diploma_certificate) }}#toolbar=0&scrollbar=0"
                                  frameBorder="0"
                                  scrolling="auto"
                                  height="400px"
                                  width="100%"
                              ></iframe>
                          @else
                            <img src="{{ asset('uploads/'.$applicant->teacher_diploma_certificate) }}" height="400px" width="100%">
                          @endif
                        </div>
                      </div>
                    </div>
                  @endif

                  @if($applicant->veta_certificate)
                    <div class="card">
                      <div class="card-header" id="ss-veta-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseVeta" aria-expanded="true" aria-controls="collapseVeta">
                            Veta Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseVeta" class="collapse" aria-labelledby="ss-veta-certificate" data-parent="#accordionExample-2">
                        <div class="card-body">
                          @if(explode('.',$applicant->veta_certificate)[1] == 'pdf')
                            <iframe
                                  src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->veta_certificate) }}#toolbar=0&scrollbar=0"
                                  frameBorder="0"
                                  scrolling="auto"
                                  height="400px"
                                  width="100%"
                              ></iframe>
                          @else
                            <img src="{{ asset('uploads/'.$applicant->veta_certificate) }}" height="400px" width="100%">
                          @endif
                        </div>
                      </div>
                    </div>
                  @endif

                  @if($applicant->degree_certificate)
                    <div class="card">
                      <div class="card-header" id="ss-degree-certificate">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseDegreeCertificate" aria-expanded="true" aria-controls="collapseDegreeCertificate">
                            Bachelor Degree Certificate
                          </button>
                        </h2>
                      </div>

                      <div id="collapseDegreeCertificate" class="collapse" aria-labelledby="ss-degree-certificate" data-parent="#accordionExample-2">
                        <div class="card-body">
                          @if(explode('.',$applicant->degree_certificate)[1] == 'pdf')
                            <iframe
                                  src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->degree_certificate) }}#toolbar=0&scrollbar=0"
                                  frameBorder="0"
                                  scrolling="auto"
                                  height="400px"
                                  width="100%"
                              ></iframe>
                          @else
                            <img src="{{ asset('uploads/'.$applicant->degree_certificate) }}" height="400px" width="100%">
                          @endif
                        </div>
                      </div>
                    </div>
                  @endif

                </div>

                {!! Form::open(['url'=>'application/select-applicant','class'=>'ss-form-processing','method'=>'POST']) !!}
                <input type="hidden" name="applicant_id" value="{{ $request->get('applicant_id') }}">
                <input type="hidden" name="application_window_id" value="{{ $request->get('application_window_id') }}">
                
                <div>
                  <label for="">Select Program</label>
                </div>

                @foreach($program_codes as $code)
                  <div class="form-check form-check-inline">
                    <input required class="form-check-input" type="radio" name="program_code" id="program-radio-{{ $code }}" value="{{ $code }}">
                    <label class="form-check-label" for="program-radio-{{ $code }}">
                      {{ $code }}
                    </label>
                  </div>
                @endforeach


                <div class="mt-3">
                  <button type="submit" class="btn btn-primary">Select Applicant</button>
                  <a href="{{ url('application/other-applicants') }}" class="btn btn-danger">Decline Applicant</a>
                </div>

                
                {!! Form::close() !!} 



                    
                 
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

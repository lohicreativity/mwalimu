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
            <h1 class="m-0">Applicant Registration</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Applicant Registration</a></li>
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
          <div class="col-4">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle"
                       src="{{ asset('uploads/'.$applicant->passport_picture) }}"
                       onerror="this.src='{{ asset("img/user-avatar.png") }}'">
                </div>

                <h3 class="profile-username text-center">{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->surname }}</h3>

                <p class="text-muted text-center">{{ $applicant->index_number }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Address</b> <a class="float-right">{{ $applicant->address }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Email</b> <a class="float-right">{{ $applicant->email }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Phone</b> <a class="float-right">{{ $applicant->phone }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Date of Birth</b> <a class="float-right">{{ $applicant->birth_date }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Gender</b> <a class="float-right">{{ $applicant->gender }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Disability Status</b> <a class="float-right">{{ $applicant->disabilityStatus->name }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Campus</b> <a class="float-right">{{ $applicant->campus->name }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Programme</b> <a class="float-right">{{ $selection->campusProgram->program->code }}</a>
                  </li>
                </ul>
                
       
                <!-- <a href="#" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ss-edit-applicant-profile"><b>Edit Profile</b></a> -->
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <div class="col-8">
             @if($applicant->has_postponed == 1)
             <div class="alert alert-warning">Applicant has postponed.</div>
             @endif
             <div class="accordion" id="accordionExample-2">
                @if($applicant->birth_certificate)
                <div class="card">
                  <div class="card-header" id="ss-birth-certificate">
                    <h2 class="mb-0">
                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseBirth" aria-expanded="true" aria-controls="collapseBirth">
                        Birth Certificate
                      </button>
                    </h2>
                  </div>

                  <div id="collapseBirth" class="collapse" aria-labelledby="ss-birth-certificate" data-parent="#accordionExample-2">
                    <div class="card-body">
                      @if(explode('.',$applicant->birth_certificate)[1] == 'pdf')
                         <iframe
                              src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->birth_certificate) }}#toolbar=0&scrollbar=0"
                              frameBorder="0"
                              scrolling="auto"
                              height="400px"
                              width="100%"
                          ></iframe>
                      @else
                         <img src="{{ asset('uploads/'.$applicant->birth_certificate) }}" height="400px" width="100%">
                      @endif
                    </div>
                  </div>
                </div>
                @endif
                @if($applicant->o_level_certificate)
                <div class="card">
                  <div class="card-header" id="ss-o-level-certificate">
                    <h2 class="mb-0">
                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOlevel" aria-expanded="true" aria-controls="collapseOlevel">
                        O-Level Certificate
                      </button>
                    </h2>
                  </div>

                  <div id="collapseOlevel" class="collapse" aria-labelledby="ss-o-level-certificate" data-parent="#accordionExample-2">
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
                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseAlevel" aria-expanded="true" aria-controls="collapseAlevel">
                        A-Level Certificate
                      </button>
                    </h2>
                  </div>

                  <div id="collapseAlevel" class="collapse" aria-labelledby="ss-a-level-certificate" data-parent="#accordionExample-2">
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
                @if($applicant->avn_certificate)
                <div class="card">
                  <div class="card-header" id="ss-avn-certificate">
                    <h2 class="mb-0">
                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseAvn" aria-expanded="true" aria-controls="collapseAvn">
                        AVN Certificate
                      </button>
                    </h2>
                  </div>

                  <div id="collapseAvn" class="collapse" aria-labelledby="ss-avn-certificate" data-parent="#accordionExample-2">
                    <div class="card-body">
                      @if(explode('.',$applicant->avn_certificate)[1] == 'pdf')
                         <iframe
                              src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->avn_certificate) }}#toolbar=0&scrollbar=0"
                              frameBorder="0"
                              scrolling="auto"
                              height="400px"
                              width="100%"
                          ></iframe>
                      @else
                         <img src="{{ asset('uploads/'.$applicant->avn_certificate) }}" height="400px" width="100%">
                      @endif
                    </div>
                  </div>
                </div>
                @endif
                @if($applicant->postponement_letter)
                <div class="card">
                  <div class="card-header" id="ss-postponement-letter">
                    <h2 class="mb-0">
                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapsePostLetter" aria-expanded="true" aria-controls="collapsePostLetter">
                        Postponement Letter
                      </button>
                    </h2>
                  </div>

                  <div id="collapsePostLetter" class="collapse" aria-labelledby="ss-postponement-letter" data-parent="#accordionExample-2">
                    <div class="card-body">
                      @if(explode('.',$applicant->postponement_letter)[1] == 'pdf')
                         <iframe
                              src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$applicant->postponement_letter) }}#toolbar=0&scrollbar=0"
                              frameBorder="0"
                              scrolling="auto"
                              height="400px"
                              width="100%"
                          ></iframe>
                      @else
                         <img src="{{ asset('uploads/'.$applicant->postponement_letter) }}" height="400px" width="100%">
                      @endif
                    </div>
                  </div>
                </div>
                @endif
              </div><!-- end of accordian -->
              
             <div class="accordion" id="accordionExample">
                <div class="card">
                  @foreach($applicant->nectaResultDetails as $key=>$detail)
                  <div class="card-header" id="headingTwo">
                    <h2 class="mb-0">
                      <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#ss-detail-{{ $detail->id}}" aria-expanded="true" aria-controls="collapseOne-{{ $detail->id }}">
                        @if($detail->exam_id == 1)
                          Form IV Results
                        @else
                          Form VI Results
                        @endif
                      </button>
                    </h2>
                  </div>

                  <div id="ss-detail-{{ $detail->id }}" class="collapse @if($key == 0) --show @endif" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                       <table class="table table-bordered">
                         <tr><td>Index Number:</td><td>{{ $detail->index_number }}</td></tr>
                         <tr><td>Division:</td><td>{{ $detail->division }}</td></tr>
                         <tr><td>Points:</td><td>{{ $detail->points }}</td></tr>
                         @foreach($detail->results as $result)
                            <tr><td>{{ $result->subject_name }}</td><td>{{ $result->grade}}</td></tr>
                         @endforeach
                         </table>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>

              

              <div class="card">
                 {!! Form::open(['url'=>'application/register-applicant','class'=>'ss-form-processing']) !!}

                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                 <div class="card-body">
                    <div class="row">
                       <div class="col-4">
                          <div class="form-group">
                            <div class="custom-control custom-checkbox">
                              <input class="custom-control-input" name="academic_results_check" type="checkbox" id="academic-results-check" value="1">
                              <label for="academic-results-check" class="custom-control-label">Academic Results</label>
                            </div>
                          </div>
                       </div>
                       <div class="col-4">
                          <div class="form-group">
                            <div class="custom-control custom-checkbox">
                              <input class="custom-control-input" name="personal_info_check" type="checkbox" id="personal-info-check" value="1">
                              <label for="personal-info-check" class="custom-control-label">Personal Details</label>
                            </div>
                          </div>
                       </div>
                       <div class="col-4">
                          <div class="form-group">
                            <div class="custom-control custom-checkbox">
                              <input class="custom-control-input" name="medical_form_check" type="checkbox" id="medical-form-check" value="1">
                              <label for="medical-form-check" class="custom-control-label">Medical Form</label>
                            </div>
                          </div>
                       </div>
                    </div>
                    <div class="row">
<!--                        <div class="col-6">
                          <div class="form-group">
                            <div class="custom-control custom-checkbox">
                              <input class="custom-control-input" name="health_insurance_check" type="checkbox" id="health-insurance-check" value="1" @if($applicant->insurance_check == 1) checked="checked" @endif disabled readonly>
                              <label for="health-insurance-check" class="custom-control-label">Health Insurance</label>
                              <input type="hidden" name="insurance_check" @if($applicant->insurance_check == 1) value="1" @else value="0" @endif>
                            </div>
                          </div>
                       </div> -->
                       <div class="col-6">
                          <div class="form-group">
                            <div class="custom-control custom-checkbox">
                              <input class="custom-control-input" name="fee_payment_check" type="checkbox" id="fee-payment-check" value="1" @if($applicant->tuition_payment_check == 1 && $applicant->other_payment_check == 1) checked="checked" @endif disabled readonly>
                              <label for="fee-payment-check" class="custom-control-label">Fee Payment</label>
                              <input type="hidden" name="fee_payment_check" @if($applicant->tuition_payment_check == 1 && $applicant->other_payment_check == 1) value="1" @else value="0" @endif>
                            </div>
                          </div>
                       </div>
                    </div>
                 </div>
                 <div class="card-footer">
                   <button type="submit" class="btn btn-primary">Register</button>
                 </div>
                 {!! Form::close() !!}
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

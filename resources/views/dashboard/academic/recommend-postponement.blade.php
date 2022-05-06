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
            <h1 class="m-0">Postponement Recommendation</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Postponement Recommendation</a></li>
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
                       src="{{ asset('uploads/'.$postponement->student->image) }}"
                       onerror="this.src='{{ asset("img/user-avatar.png") }}'">
                </div>

                <h3 class="profile-username text-center">{{ $postponement->student->first_name }} {{ $postponement->student->middle_name }} {{ $postponement->student->surname }}</h3>

                <p class="text-muted text-center">{{ $postponement->student->index_number }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Address</b> <a class="float-right">{{ $postponement->student->address }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Email</b> <a class="float-right">{{ $postponement->student->email }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Phone</b> <a class="float-right">{{ $postponement->student->phone }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Date of Birth</b> <a class="float-right">{{ $postponement->student->birth_date }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Gender</b> <a class="float-right">{{ $postponement->student->gender }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Programme</b> <a class="float-right">{{ $postponement->student->campusProgram->program->name }}</a>
                  </li>
                </ul>
                
       
                <!-- <a href="#" class="btn btn-primary btn-block" data-toggle="modal" data-target="#ss-edit-applicant-profile"><b>Edit Profile</b></a> -->
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <div class="col-8">
            <div class="card">
               <div class="card-body">
              @if($post->letter)
                  <iframe
                      src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$post->letter) }}#toolbar=0&scrollbar=0"
                      frameBorder="0"
                      scrolling="auto"
                      height="auto"
                      width="100%"
                  ></iframe>
               @endif
               @if($post->supporting_document)
                  <iframe
                      src="https://drive.google.com/viewerng/viewer?embedded=true&url={{ asset('uploads/'.$post->supporting_document) }}#toolbar=0&scrollbar=0"
                      frameBorder="0"
                      scrolling="auto"
                      height="auto"
                      width="100%"
                  ></iframe>
               @endif
                </div>
              </div>

              <div class="card">
                 {!! Form::open(['url'=>'academic/postponement/recommend','class'=>'ss-form-processing']) !!}
                 <div class="card-body">
                   <div class="row">
                     <div class="col-12">
                        <label class="radio-inline">
                          <input type="radio" name="recommended" id="inlineRadio1" value="1"> Recommended
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="recommended" id="inlineRadio2" value="0"> Not Recommended
                        </label>

                     </div>
                  </div>
                   <div class="row">
                    <div class="form-group col-12">
                      {!! Form::label('','Recommendation') !!}
                      {!! Form::textarea('recommendation',null,['class'=>'form-control','placeholder'=>'Recommendation','rows'=>3,'required'=>true]) !!}

                      {!! Form::input('hidden','postponement_id',$postponement->id) !!}
                    </div>
                 </div>
                 </div>
                 <div class="card-footer">
                   <button type="submit" class="btn btn-primary">Save Recommendation</button>
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

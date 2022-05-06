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
            <h1>{{ __('Postponements') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Postponements') }}</li>
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
                <h3 class="card-title">Select Academic Year</h3>
              </div>
              <!-- /.card-header -->
                 <div class="card-body">
                 {!! Form::open(['url'=>'academic/postponements','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($postponements) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Postponements</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Student</th>
                    <th>Reg Number</th>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Is Renewal</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($postponements as $post)
                  <tr>
                    <td>{{ $post->student->first_name }} {{ $post->student->middle_name }} {{ $post->student->surname }}</td>
                    <td>{{ $post->student->registration_number }}</td>
                    <td>{{ $post->studyAcademicYear->academicYear->year }}</td>
                    <td>@if($post->semester) {{ $post->semester->name }} @endif</td>
                    <td>{{ $post->category }}</td>
                    <td>{{ $post->status }}</td>
                    <td>@if($post->is_renewal == 1) Yes @else No @endif</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-post-{{ $post->id }}">
                              <i class="fas fa-eye-open">
                              </i>
                              Recommend
                       </a>
                       <div class="modal fade" id="ss-edit-post-{{ $post->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Recommend Postponement</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
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
                               {!! Form::open(['url'=>'academic/postponement/recommend','class'=>'ss-form-processing']) !!}

                               <div class="row">
                                <div class="form-group col-12">
                                  {!! Form::label('','Recommendation') !!}
                                  {!! Form::textarea('recommendation',null,['class'=>'form-control','placeholder'=>'Recommendation','required'=>true]) !!}

                                  {!! Form::input('hidden','postponement_id',$post->id) !!}
                                </div>
                             </div>
                               <div class="ss-form-actions">
                               <button type="submit" class="btn btn-primary">{{ __('Save Recommendation') }}</button>
                              </div>
                               {!! Form::close() !!}
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->

                      <a class="btn btn-success btn-sm" href="{{ url('academic/postponement/'.$post->id.'/accept') }}">
                              <i class="fas fa-check">
                              </i>
                              Accept
                       </a>
                       <a class="btn btn-danger btn-sm" href="{{ url('academic/postponement/'.$post->id.'/decline') }}">
                              <i class="fas fa-check">
                              </i>
                              Decline
                       </a>
                    </td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Postponement Created') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
              </div>
            </div>
            <!-- /.card -->
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

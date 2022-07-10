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
                <h3 class="card-title">Request Postponement - {{ $study_academic_year->academicYear->year }} - {{ $semester->name }}</h3>
              </div>
              <!-- /.card-header -->
                 {!! Form::open(['url'=>'academic/postponement/store','class'=>'ss-form-processing','files'=>true]) !!}
              <div class="card-body">

                 {!! Form::input('hidden','status','PENDING') !!}
                 {!! Form::input('hidden','semester_id',$semester->id) !!}
                 {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                 <div class="row">
                  <div class="form-group col-6">
                     {!! Form::label('','Category') !!}
                     <select name="category" class="form-control" required>
                       <option>Select Category</option>
                       @if(!str_contains($semester->name,'2'))
                       <option value="YEAR">Year</option>
                       @endif
                       <option value="SEMESTER">Semester</option>
                     </select>

                     {!! Form::input('hidden','student_id',$student->id) !!}
                  </div>
                  <div class="form-group col-6">
                     {!! Form::label('','Upload postponement letter') !!}
                     {!! Form::file('postponement_letter',['class'=>'form-control','required'=>true]) !!}
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-6">
                     {!! Form::label('','Upload supporting document (Optional)') !!}
                     {!! Form::file('supporting_document',['class'=>'form-control']) !!}
                  </div>
                </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Request Postponement') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($postponements) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Postponements</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                @php
                  $is_renewal = false;
                @endphp
                @foreach($postponements as $post)
                  @if($post->is_renewal == 1)
                    @php
                      $is_renewal = true;
                      break;
                    @endphp
                  @endif
                @endforeach
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Download Letter</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($postponements as $post)
                  <tr>
                    <td>{{ $post->studyAcademicYear->academicYear->year }}</td>
                    <td>@if($post->semester) {{ $post->semester->name }} @endif</td>
                    <td>{{ $post->category }}</td>
                    <td>{{ $post->status }}</td>
                    <td>
                      <a href="{{ url('student/postponement-letter/'.$post->id.'/download') }}">Postponement Letter</a><br>

                      @if(!is_null($post->supporting_document))
                      <a href="{{ url('student/supporting-document/'.$post->id.'/download') }}">Supporting Document</a>
                      @endif

                      @if(!is_null($post->resumption_letter))
                      <a href="{{ url('student/resumption-letter/'.$post->id.'/download') }}">Resumption Letter</a></td>
                      @endif

                    </td>
                    <td>
                      @if($post->status == 'PENDING')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-post-{{ $post->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Cancel
                       </a>
                      @endif

                      @if($post->status == 'POSTPONED' && !is_null($post->resumption_letter) && !is_null($post->resume_recommended))
                      <a class="btn btn-danger btn-sm" href="{{ url('student/postponement/'.$post->id.'/cancel-resumption') }}">
                              <i class="fas fa-trash">
                              </i>
                              Cancel Resumption
                       </a>
                      @endif
                      
                      @if($post->status == 'POSTPONED' && $is_renewal && $post->is_renewal == 1)
                      {{ is_null($post->resumption_letter) }}
                      <a href="{{ url('student/postponement/'.$post->id.'/resume') }}" class="btn btn-primary">Resume</a>
                      @elseif($post->status == 'POSTPONED' && !$is_renewal && $post->is_renewal == null)
                      @if($post->status == 'POSTPONED' && is_null($post->resumption_letter))
                      <a href="{{ url('student/postponement/'.$post->id.'/resume') }}" class="btn btn-primary">Resume</a>
                      @endif
                      @endif

                       <div class="modal fade" id="ss-delete-post-{{ $post->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to cancel this postponement?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/postponement/'.$post->id.'/destroy') }}" class="btn btn-danger">Delete</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
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

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
            <h1>{{ __('Special Exams') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Special Exams') }}</li>
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
                <h3 class="card-title">Request Exam Postponement</h3>
              </div>
              <!-- /.card-header -->
                 {!! Form::open(['url'=>'student/special-exam/store','class'=>'ss-form-processing','files'=>true]) !!}
              <div class="card-body">

                 <div class="row">
                  <div class="form-group col-6">
                     {!! Form::label('','Type') !!}
                     <select name="type" class="form-control" required>
                       <option value="">Select Type</option>
                       <option value="FINAL">Final</option>
                       <option value="SUPP">Supplementary</option>
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
                <div class="row">
                   @foreach($module_assignments as $assign)
                   <div class="col-3">
                     <div class="checkbox">
                       <label>
                          {!! Form::checkbox('mod_assign_'.$assign->id,$assign->id) !!}
                          {{ $assign->module->name }}
                       </label>
                     </div>
                   </div>
                   @endforeach
                </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Request Exam Postponement') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($special_exams) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Special Exams</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>Module</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Letter</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($special_exams as $exam)
                  <tr>
                    <td>{{ $exam->studyAcademicYear->academicYear->year }}</td>
                    <td>{{ $exam->semester->name }}</td>
                    <td>{{ $exam->moduleAssignment->module->name }} - {{ $exam->moduleAssignment->module->code }}</td>
                    <td>{{ $exam->type }}</td>
                    <td>{{ $exam->status }}</td>
                    <td><a href="{{ url('student/special-exam/postponement-letter/'.$post->id.'/download') }}">Postponement Letter</a><br>

                      <a href="{{ url('student/special-exam/supporting-document/'.$post->id.'/download') }}">Supporting Document</a>
                    </td>
                    <td>

                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-irregularity-{{ $exam->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-irregularity-{{ $exam->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this special exam from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/special-exam/'.$exam->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <h3 class="card-title">{{ __('No Special Exam Created') }}</h3>
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

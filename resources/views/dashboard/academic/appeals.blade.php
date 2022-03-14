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
            <h1>{{ __('Appeals List') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Appeals List') }}</li>
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
                 <h3 class="card-title">Select Study Academic Year</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/results/appeals','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
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

            <div class="card">
              <div class="card-header">
                 <h3 class="card-title">Upload Completed Appeals List</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/upload-appeal-list','class'=>'ss-form-processing','files'=>true]) !!}
                   
                  <div class="form-group">
                    {!! Form::label('','Upload completed appeals list') !!}
                    {!! Form::file('appeals_file',['class'=>'form-control','required'=>true]) !!}
                    
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($appeals) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Appeals - {{ $study_academic_year->academicYear->year }}</h3><br>
                <a href="{{ url('academic/download-appeal-list?study_academic_year_id='.$study_academic_year->id) }}" class="btn btn-primary"><i class="fa fa-download"></i> Download Appeal List</a>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Student</th>
                    <th>Module</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                    @foreach($appeals as $appeal)
                    <tr>
                      <td>{{ $appeal->student->first_name }} {{ $appeal->student->middle_name }} {{ $appeal->student->surname }}</td>
                      <td>{{ $appeal->moduleAssignment->module->name }}</td>
                      <td>
                          @if($appeal->is_downloaded == 1) <span class="badge badge-warning">On Progress</span> @elseif($appeal->is_downloaded == 0) <span class="badge badge-warning">Pending</span> @else <span class="badge badge-success">Completed</span> @endif
                      </td>
                      <td><a href="#" class="btn btn-primary" data-toggle="modal" data-target="#ss-update-appeal-{{ $appeal->id }}">Update Results</a></td>

                      <div class="modal fade" id="ss-update-appeal-{{ $appeal->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @php
                                $course_work_score = [
                                   'placeholder'=>'Coursework score',
                                   'class'=>'form-control',
                                   'readonly'=>true,
                                   'required'=>true
                                ];

                                $final_score = [
                                   'placeholder'=>'Final score',
                                   'class'=>'form-control',
                                   'required'=>true
                                ];

                            @endphp
                            
                            @if($appeal->examinationResult->supp_score == null)
                             @php
                                $supp_score = [
                                   'placeholder'=>'Supp score',
                                   'class'=>'form-control',
                                   'readonly'=>true

                                ];
                            @endphp
                            @else
                            @php
                                $supp_score = [
                                   'placeholder'=>'Supp score',
                                   'class'=>'form-control',
                                ];
                            @endphp
                            @endif
                            {!! Form::open(['url'=>'academic/results/update-examination-results','class'=>'ss-form-processing']) !!}
                              <div class="card-body">
                                <div class="row">
                                  <div class="form-group col-4">
                                    {!! Form::label('','Coursework score') !!}
                                    {!! Form::text('course_work_score',$appeal->examinationResult->course_work_score,$course_work_score) !!}
                                  </div>
                                  <div class="form-group col-4">
                                    {!! Form::label('','Final score (/100)') !!}
                                    {!! Form::text('appleal_score',round($appeal->examinationResult->final_score*100/$appeal->examinationResult->moduleAssignment->programModuleAssignment->final_min_mark,1),$final_score) !!}
                                  </div>
                                  <div class="form-group col-4">
                                    {!! Form::label('','Supp score') !!}
                                    {!! Form::text('supp_score',$appeal->examinationResult->supp_score,$supp_score) !!}

                                    {!! Form::input('hidden','student_id',$appeal->student_id) !!}
                                    {!! Form::input('hidden','exam_type','APPEAL') !!}

                                    {!! Form::input('hidden','study_academic_year_id',$appeal->examinationResult->moduleAssignment->study_academic_year_id) !!}

                                    {!! Form::input('hidden','semester_id',$appeal->examinationResult->moduleAssignment->programModuleAssignment->semester_id) !!}

                                    {!! Form::input('hidden','module_assignment_id',$appeal->examinationResult->moduleAssignment->id) !!}
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->

                              <div class="ss-form-actions">
                                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
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
                <h3 class="card-title">{{ __('No Appeals List Obtained') }}</h3>
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

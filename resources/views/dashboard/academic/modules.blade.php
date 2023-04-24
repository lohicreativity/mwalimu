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
            <h1>{{ __('Modules') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Modules') }}</li>
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
         
         
         <div class="card card-default">
           <div class="card-header">
                <ul class="nav nav-tabs">
                  @can('view-module-assignments')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module-assignments?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Module Assignments') }}</a></li>
                  @endcan
                  @can('view-module-assignment-requests')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module-assignment-requests') }}">{{ __('Modules Assignment Requests') }}</a></li>
                  @endcan
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module-assignment/confirmation?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Modules Assignment Confirmation') }}</a></li>
                  @can('view-modules')
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/modules') }}">{{ __('Modules') }}</a></li>
                  @endcan
                </ul>
              </div>
            </div>

            @can('add-module')
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Module') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Module name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $code = [
                     'placeholder'=>'Code',
                     'class'=>'form-control ss-autofill-nta',
                     'data-target'=>'#ss-nta-level, #ss-nta-level-input',
                     'data-token'=>session()->token(),
                     'data-source-url'=>url('api/v1/get-nta-level-by-code'),
                     'required'=>true
                  ];

                  $credit = [
                     'placeholder'=>'Credit',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $course_work = [
                     'placeholder'=>'Course work',
                     'class'=>'form-control',
                     'data-target'=>'#ss-final-exam',
                     'steps'=>'any',
                     'required'=>true
                  ];

                  $final_exam = [
                     'placeholder'=>'Final exam',
                     'class'=>'form-control',
                     'id'=>'ss-final-exam',
                     'steps'=>'any',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'academic/module/store','class'=>'ss-form-processing','true','files'=>true]) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Code') !!}
                    {!! Form::text('code',null,$code) !!}
                  </div>
                  
                  <div class="form-group col-6">
                    {!! Form::label('','Department') !!}
                    <select name="department_id" class="form-control" required>
                      @foreach($departments as $department)
                      @if($staff->department_id == $department->id)
                      <option value="{{ $department->id }}" selected="selected">{{ $department->name }}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>
                 </div>
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Name') !!}
                    {!! Form::text('name',null,$name) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Credit') !!}
                    <select name="credit" class="form-control" required>
                       <option value="">Select Credit</option>
                       @for($i = 1; $i<= 20; $i++)
                       <option value="{{ $i }}">{{ $i }}</option>
                       @endfor
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','NTA Level') !!}
                    <select name="nta_level_id" class="form-control" required id="ss-nta-level" disabled="disabled">
                      <option value="">Select NTA Level</option>
                      @foreach($nta_levels as $level)
                      <option value="{{ $level->id }}">{{ $level->name }}</option>
                      @endforeach
                    </select>

                    {!! Form::input('hidden','nta_level_id',null,['id'=>'ss-nta-level-input']) !!}
                    {!! Form::input('hidden','campus_id',$staff->campus_id) !!}
                  </div>
                   <div class="form-group col-6">
                    {!! Form::label('','Upload module syllabus (PDF)') !!}
                    {!! Form::file('syllabus',['class'=>'form-control']) !!}
                  </div>
                 </div><!-- end of row -->
                 <div class="row">
                    <div class="form-group">
                        <div class="custom-control custom-radio">
                          <input class="custom-control-input" type="radio" id="customRadio1" name="course_work_based" value="1" required>
                          <label for="customRadio1" class="custom-control-label">Coursework Based</label>
                        </div>
                        <div class="custom-control custom-radio">
                          <input class="custom-control-input" type="radio" id="customRadio2" name="course_work_based" value="0" required>
                          <label for="customRadio2" class="custom-control-label">Non-Coursework Based</label>
                        </div>
                    </div>
                 </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Module') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($modules) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Modules') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                {!! Form::open(['url'=>'academic/modules','method'=>'GET']) !!}
                {!! Form::close() !!}
                <table id="example2" class="table table-bordered ss-margin-top ss-paginated-table">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Credit</th>
                    <th>NTA Level</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($modules as $module)
                  <tr>
                    <td>{{ $module->name }}</td>
                    <td>{{ $module->code }}</td>
                    <td>{{ $module->credit }}</td>
                    <td>{{ $module->ntaLevel->name }}</td>
                    <td>
                      @can('download-syllabus')
                      <a class="btn btn-info btn-sm" href="{{ url('academic/module/'.$module->id.'/download-syllabus') }}">
                              <i class="fas fa-download">
                              </i>
                              Download Syllabus
                       </a>
                      @endcan
                      @can('edit-module')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-module-{{ $module->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-edit-module-{{ $module->id }}">
                        <div class="modal-dialog modal-xl">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Module</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @php
                                $name = [
                                   'placeholder'=>'Module name',
                                   'class'=>'form-control',
                                   'required'=>true
                                ];

                                $code = [
                                   'placeholder'=>'Code',
                                   'class'=>'form-control ss-autofill-nta',
                                   'data-target'=>'#ss-nta-level-'.$module->id.',#ss-nta-level-input-'.$module->id,
                                   'data-token'=>session()->token(),
                                   'data-source-url'=>url('api/v1/get-nta-level-by-code'),
                                   'required'=>true
                                ];

                                $course_work = [
                                   'placeholder'=>'Course work',
                                   'class'=>'form-control',
                                   'data-target'=>'#ss-final-exam-'.$module->id,
                                   'steps'=>'any',
                                   'required'=>true
                                ];

                                $final_exam = [
                                   'placeholder'=>'Final exam',
                                   'class'=>'form-control',
                                   'id'=>'ss-final-exam-'.$module->id,
                                   'steps'=>'any',
                                   'required'=>true
                                ];
                            @endphp
                                {!! Form::open(['url'=>'academic/module/update','class'=>'ss-form-processing','files'=>true]) !!}
                                  <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Code') !!}
                                      {!! Form::text('code',$module->code,$code) !!}
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Department') !!}
                                      <select name="department_id" class="form-control" required>
                                        @foreach($departments as $department)
                                        @if($staff->department_id == $department->id)
                                        <option value="{{ $department->id }}" selected="selected">{{ $department->name }}</option>
                                        @endif
                                        @endforeach
                                      </select>
                                    </div>
                                   </div>
                                    <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Name') !!}
                                      {!! Form::text('name',$module->name,$name) !!}

                                      {!! Form::input('hidden','module_id',$module->id) !!}
                                      {!! Form::input('hidden','campus_id',$staff->campus_id) !!}
                                    </div>
                                    
                                    <div class="form-group col-6">
                                      {!! Form::label('','Credit') !!}
                                      <select name="credit" class="form-control" required>
                                         <option value="">Select Credit</option>
                                         @for($i = 1; $i<= 36; $i++)
                                         <option value="{{ $i }}" @if($module->credit == $i) selected="selected" @endif>{{ $i }}</option>
                                         @endfor
                                      </select>
                                    </div>
                                    </div>
                                    
                                   <div class="row">
                                      <div class="form-group col-6">
                                        {!! Form::label('','NTA Level') !!}
                                        <select name="nta_level_id" class="form-control" required disabled="disabled" id="ss-nta-level-{{ $module->id }}">
                                          <option value="">Select NTA Level</option>
                                          @foreach($nta_levels as $level)
                                          <option value="{{ $level->id }}" @if($module->nta_level_id == $level->id) selected="selected" @endif>{{ $level->name }}</option>
                                          @endforeach
                                        </select>

                                        {!! Form::input('hidden','nta_level_id',$module->nta_level_id,['id'=>'ss-nta-level-input-'.$module->id]) !!}
                                      </div>
                                       <div class="form-group col-6">
                                        {!! Form::label('','Upload module syllabus (PDF)') !!}
                                        {!! Form::file('syllabus',['class'=>'form-control']) !!}
                                      </div>
                                   </div>
                                   <div class="row">
                                    <div class="form-group">
                                        <div class="custom-control custom-radio">
                                          <input class="custom-control-input" type="radio" id="customRadio1-{{ $module->id }}" name="course_work_based" value="1" @if($module->course_work_based == 1) checked="checked" @endif required>
                                          <label for="customRadio1-{{ $module->id }}" class="custom-control-label">Coursework Based</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                          <input class="custom-control-input" type="radio" id="customRadio2-{{ $module->id }}" name="course_work_based" value="0" @if($module->course_work_based == 0) checked="checked" @endif required>
                                          <label for="customRadio2-{{ $module->id }}" class="custom-control-label">Non-Coursework Based</label>
                                        </div>
                                    </div>
                                     </div>
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
                      @can('delete-module')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-module-{{ $module->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                       @endcan

                       <div class="modal fade" id="ss-delete-module-{{ $module->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this module from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/module/'.$module->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <div class="ss-pagination-links">
                {!! $modules->appends($request->except('page'))->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
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

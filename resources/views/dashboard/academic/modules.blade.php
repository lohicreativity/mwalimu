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

            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add module') }}</h3>
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
                     'class'=>'form-control',
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
                     'steps'=>'any',
                     'required'=>true
                  ];

                  $final_exam = [
                     'placeholder'=>'Final exam',
                     'class'=>'form-control',
                     'steps'=>'any',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'academic/module/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Module name') !!}
                    {!! Form::text('name',null,$name) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Department') !!}
                    <select name="department_id" class="form-control" required>
                      <option value="">Select Department</option>
                      @foreach($departments as $department)
                      <option value="{{ $department->id }}">{{ $department->name }}</option>
                      @endforeach
                    </select>
                  </div>
                 </div>
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Code') !!}
                    {!! Form::text('code',null,$code) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Credit') !!}
                    {!! Form::text('credit',null,$credit) !!}
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Course work') !!}
                    {!! Form::input('number','course_work',null,$course_work) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Final exam') !!}
                    {!! Form::input('number','final_exam',null,$final_exam) !!}
                  </div>
                 </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($modules) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Modules') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Credit</th>
                    <th>Course Work</th>
                    <th>Final Exam</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($modules as $module)
                  <tr>
                    <td>{{ $module->name }}</td>
                    <td>{{ $module->department->name }}</td>
                    <td>{{ $module->code }}</td>
                    <td>{{ $module->credit }}</td>
                    <td>{{ $module->course_work }}</td>
                    <td>{{ $module->final_exam }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-module-{{ $module->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>

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
                                {!! Form::open(['url'=>'academic/module/update','class'=>'ss-form-processing']) !!}
                                  <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Module name') !!}
                                      {!! Form::text('name',$module->name,$name) !!}

                                      {!! Form::input('hidden','module_id',$module->id) !!}
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Department') !!}
                                      <select name="department_id" class="form-control" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @if($module->department_id == $department->id) selected="selected" @endif>{{ $department->name }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                   </div>
                                    <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Code') !!}
                                      {!! Form::text('code',$module->code,$code) !!}
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Credit') !!}
                                      {!! Form::text('credit',$module->credit,$credit) !!}
                                    </div>
                                    </div>
                                    <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Course work') !!}
                                      {!! Form::input('number','course_work',$module->course_work,$course_work) !!}
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Final exam') !!}
                                      {!! Form::input('number','final_exam',$module->final_exam,$final_exam) !!}
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
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-module-{{ $module->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

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
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Abort</button>
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
                {!! $modules->render() !!}
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

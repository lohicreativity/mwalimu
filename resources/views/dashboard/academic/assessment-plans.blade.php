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
            @php
              $module_name = str_replace(' Of ',' of ',$module_assignment->module->name);
              $module_name = str_replace(' And ',' and ',$module_name);
              $module_name = str_replace(' In ',' in ',$module_name);

            @endphp
            <h1>{{ __('Assessment Policy') }} - {{ $module_name }} ({{ round($module_assignment->programModuleAssignment->course_work_min_mark) }} Marks)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignments') }}">{{ __('Module Assignment') }}</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results') }}</a></li>
              <li class="breadcrumb-item active">{{ __('CA Components') }}</li>
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
                <div class="card-header p-2">
                <ul class="nav nav-tabs">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module/'.$module_assignment->module_id.'/download-syllabus') }}">{{ __('Module Syllabus') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/attendance') }}">{{ __('Attendance') }}</a></li>
                  @if($module->course_work_based == 1)
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/assessment-plans') }}">{{ __('CA Components') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results Management') }}</a></li>
                  @else
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results Management') }}</a></li>
                  @endif
                </ul>
              </div><!-- /.card-header -->
              <!-- form start -->
              @if($module->course_work_based == 1)
                @if(count($course_work_components) == 0)
                {!! Form::open(['url'=>'academic/course-work-component/store','class'=>'ss-form-processing']) !!}
                  <div class="card-body">
                    <div class="row">
                    <div class="form-group col-6">
                        {!! Form::label('','Test(s)') !!}
                        <select name="tests" class="form-control" required>
                          @for($i = 0; $i <= 4; $i++)
                          <option value="{{ $i }}" @if($i == 2) selected="selected" @endif @if($i == 1) disabled="disabled" @endif>{{ $i }}</option>
                          @endfor
                        </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                        {!! Form::label('','Assignment(s)') !!}
                        <select name="assignments" class="form-control" required>
                          @for($i = 0; $i <= 4; $i++)
                          <option value="{{ $i }}">{{ $i }}</option>
                          @endfor
                        </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                        {!! Form::label('','Quiz(es)') !!}
                        <select name="quizes" class="form-control" required>
                          @for($i = 0; $i <= 4; $i++)
                          <option value="{{ $i }}">{{ $i }}</option>
                          @endfor
                        </select>
                    </div>
                    </div>
                    <div class="row">
                    <div class="form-group col-6">
                        {!! Form::label('','Portfolio(s)') !!}
                        <select name="portfolios" class="form-control" required>
                          @for($i = 0; $i <= 4; $i++)
                          <option value="{{ $i }}">{{ $i }}</option>
                          @endfor
                        </select>
                    </div>
                    </div>
                  </div>
                  <!-- /.card-body -->
                  {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}

                  <div class="card-footer">
                    <button type="submit" class="btn btn-primary">{{ __('Add Assessment Components') }}</button>
                  </div>
                {!! Form::close() !!}
                @elseif(count($course_work_components) != 0 && count($assessment_plans) == 0)
                @php
                    $name = [
                      'placeholder'=>'Name',
                      'class'=>'form-control',
                      'disabled'=>true,
                    ];

                    $marks = [
                      'placeholder'=>'Marks',
                      'class'=>'form-control',
                      'min'=>1,
                      'steps'=>'any',
                      'required'=>true
                    ];
                @endphp
                {!! Form::open(['url'=>'academic/assessment-plan/store','class'=>'ss-form-processing']) !!}
                  <div class="card-body">
                    @foreach($course_work_components as $component)
                    @for($i = 1; $i <= $component->quantity; $i++)
                    <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('',$component->name.$i) !!}
                      {!! Form::text('name_component_'.$component->id,$component->name.$i,$name) !!}

                      {!! Form::input('hidden','name_'.$i.'_component_'.$component->id,$component->name.$i) !!}

                      
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Marks') !!}
                      {!! Form::input('number','marks_'.$i.'_component_'.$component->id,null,$marks) !!}
                    </div>
                    </div>
                    @endfor
                    @endforeach
                  </div>
                  <!-- /.card-body -->
                  {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}
                  <div class="card-footer">
                    <button type="submit" class="btn btn-primary">{{ __('Add Assessment Plan') }}</button>
                  </div>
                {!! Form::close() !!}

                @endif
              @endif
              
            </div>
            <!-- /.card -->

            @if(count($assessment_plans) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Assessment Policy') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                @if(!$coursework_process_status)
                <a class="ss-color-danger ss-margin-bottom" href="#" data-toggle="modal" data-target="#ss-delete-plan-{{ $module_assignment->id }}">Reset Assessment Plan</a>
                @endif

                <div class="modal fade" id="ss-delete-plan-{{ $module_assignment->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to reset this assessment plan?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/assessment-plan/'.$module_assignment->id.'/reset') }}" class="btn btn-danger">Reset</a>
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
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Marks</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($assessment_plans as $plan)
                  <tr>
                    <td>{{ $plan->name }}</td>
                    <td>{{ $plan->weight }}</td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>

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

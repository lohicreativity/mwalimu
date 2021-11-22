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
            <h1>{{ __('Assessment Plans') }} - {{ $module_assignment->module->name }} ({{ $module_assignment->module->course_work}} Wgt)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignments') }}">{{ __('Module Assignment') }}</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/syllabus') }}">{{ __('Syllabus') }}</a></li>
              <li class="breadcrumb-item"><a href="{{ url('academic/staff-module-assignment/'.$module_assignment->id.'/results') }}">{{ __('Results') }}</a></li>
              <li class="breadcrumb-item active">{{ __('Assessment Plans') }}</li>
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
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module/'.$module_assignment->module_id.'/download-syllabus') }}">{{ __('Module Syllabus') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">{{ __('Attendance Sheet') }}</a></li>
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">{{ __('Assessment Plans') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">{{ __('Results Management') }}</a></li>
                </ul>
              </div><!-- /.card-header -->
              <!-- form start -->
              @if(count($course_work_components) == 0)
               {!! Form::open(['url'=>'academic/course-work-component/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-6">
                      {!! Form::label('','Test(s)') !!}
                      <select name="tests" class="form-control" required>
                         @for($i = 0; $i <= 4; $i++)
                         <option value="{{ $i }}">{{ $i }}</option>
                         @endfor
                      </select>
                   </div>
                 </div>
                 <div class="row">
                   <div class="form-group col-6">
                      {!! Form::label('','Assignement(s)') !!}
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
                </div>
                <!-- /.card-body -->
                {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Course Work Components') }}</button>
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
                     'min'=>0,
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
                    {!! Form::label('',$component->name) !!}
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
            </div>
            <!-- /.card -->

            @if(count($assessment_plans) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Assessment Plans') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
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
                    <td>{{ $plan->marks }}</td>
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

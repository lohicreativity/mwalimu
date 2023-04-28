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
            <h1>{{ __('Uploaded Modules') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Uploaded Modules') }}</li>
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
                <ul class="nav nav-tabs">
                  @can('process-examination-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Process Results') }}</a></li>
                  @endcan
                  @can('view-programme-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-program-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('View Programme Results') }}</a></li>
                  @endcan
                  @can('view-module-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('View Module Results') }}</a></li>
                  @endcan
                  @can('view-student-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-student-results') }}">{{ __('View Student Results') }}</a></li>
                  @endcan
                  @can('view-publish-examination-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  @endcan
                  @can('view-uploaded-modules')
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/results/uploaded-modules?campus_id='.session('staff_campus_id')) }}">{{ __('Uploaded Modules') }}</a></li>
                  @endcan
                  @can('upload-module-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/upload-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Upload Module Results') }}</a></li>
                  @endcan
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/results/uploaded-modules','class'=>'ss-form-processing','method'=>'GET']) !!}
                  <div class="row">
                  @if(Auth::user()->hasRole('arc') || Auth::user()->hasRole('administrator'))                      
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($request->get('campus_id') == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @else
                  
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($request->get('campus_id') == $cp->id) selected="selected" @else disabled='disabled' @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>   
                  @endif               
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
             
            @if($campus)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Uploaded Modules</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'academic/results/uploaded-modules','class'=>'ss-form-processing','method'=>'GET']) !!}
              <div class="card-body">
                  
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                       <option value="">Select Programme</option>
                       @foreach($campus_programs as $program)
                          @if(App\Utils\Util::collectionContainsKey($program->program->departments,$staff->department_id))
                          @for($i = 1; $i <= $program->program->min_duration; $i++)
                          <option value="{{ $program->id }}_year_{{ $i }}">{{ $program->program->name }} - Year {{ $i }}</option>
                          @endfor
                          @endif
                       @endforeach
                    </select>
                  </div>
                </div>
                <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if(session('active_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Semester') !!}
                    <select name="semester_id" class="form-control" required>
                       <option value="">Select Semester</option>
                       @foreach($semesters as $semester)
                       <option value="{{ $semester->id }}" @if(session('active_semester_id') == $semester->id) selected="selected" @endif>{{ $semester->name }}</option>
                       @endforeach
                    </select>
                    {!! Form::input('hidden','campus_id',$campus->id) !!}
                  </div>
                  </div>
                  <div class="row">
                  <div class="form-group col-12">
                        <label class="radio-inline">
                          <input type="radio" name="results_type" id="inlineRadio2" value="CW"> Course Work
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="results_type" id="inlineRadio3" value="FN"> Final
                        </label>
                  </div>
                </div>
              </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('View') }}</button>
                </div>
              {!! Form::close() !!}
             </div>
            @endif

            @if(count($modules) != 0)
             <div class="card">
              <div class="card-header">
                <h3 class="card-title">Uploaded Modules - @if($request->get('results_type') == 'FN') Final @else Coursework @endif</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                   <thead>
                     <tr>
                       <th>SN</th>
                       <th>Module Code</th>
                       <th>Module Name</th>
                       <th>Staff Name</th>
                       <th>Phone Number</th>
                       <th>Number of Students</th>
                     </tr>
                   </thead>
                   <tbody>
                      @foreach($modules as $key=>$module)
                      <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ $module->module->code }}</td>
                        <td>{{ $module->module->name }}</td>
                        <td>@if(count($module->moduleAssignments) != 0) {{ $module->moduleAssignments[0]->staff->title }} {{ $module->moduleAssignments[0]->staff->first_name }} {{ $module->moduleAssignments[0]->staff->surname }} @endif</td>
                        <td>@if(count($module->moduleAssignments) != 0) {{ $module->moduleAssignments[0]->staff->phone }} @endif</td>
						@if(count($module->moduleAssignments) != 0)
                        <td><a href="{{ url('academic/results/uploaded-modules/'.$module->id.'/students?result_type='.$request->get('results_type')) }}">@if($module->moduleAssignments[0]->course_work_process_status == 'PROCESSED') {{ count($module->examinationResults) }} @else 0 @endif</a></td>
					    @else
					    <td><a href="{{ url('academic/results/uploaded-modules/'.$module->id.'/students?result_type='.$request->get('results_type')) }}">0</a></td>
						@endif
                      </tr>
                     @endforeach
                   </tbody>
                 </table>
              </div>
            </div>
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

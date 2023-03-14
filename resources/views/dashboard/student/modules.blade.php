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
            <h1>{{ __('Programme Modules') }} @if($study_academic_year) - {{ $study_academic_year->academicYear->year }} @endif</h1>
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



            @if($study_academic_year)
            @foreach($semesters as $semester)
                    @php
                       $sem_reg[$semester->id] = false;
                    @endphp

                    @foreach($student->registrations as $reg)
                        @if($reg->semester_id == $semester->id)
                          @php
                            $sem_reg[$semester->id] = true;
                          @endphp
                        @endif
                    @endforeach

            <div class="card" @if($student->academicStatus->name == 'RETAKE' && !$sem_reg[$semester->id]) style="display: none;" @endif>
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Modules') }} - Year {{ $student->year_of_study }} - {{ $semester->name }} @if(count($semester->electivePolicies) != 0) - ({{ $semester->electivePolicies[0]->number_of_options }} Options Maximum) @endif

                 @if(count($semester->electiveDeadlines) != 0) - <span class="ss-color-danger">Option Deadline {{ $semester->electiveDeadlines[0]->deadline }} </span> @endif</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                
                
                <table id="example2" class="table table-bordered table-hover ss-margin-bottom">
                  <thead>
                  <tr>
                    <th>Module</th>
                    <th>Code</th>
                    <th>Staff</th>
                    <th>Room</th>
                    <th>Category</th>
                  </tr>
                  </thead>
                  <tbody>
                  
                   @foreach($study_academic_year->moduleAssignments as $assignment)
                     @if($semester->id == $assignment->semester_id)
                    <tr>
                      <td><a href="{{ url('academic/module/'.$assignment->module_id.'/download-syllabus') }}">{{ $assignment->module->name }}</a></td>
                      <td>{{ $assignment->module->code }}</td>
                      <td>
					      @if(count($assignment->moduleAssignments) != 0)
						  {{ $assignment->moduleAssignments[0]->staff->title }} {{ $assignment->moduleAssignments[0]->staff->first_name }} {{ $assignment->moduleAssignments[0]->staff->surname }}
					      @endif
					  </td>
                      <td>@if(count($assignment->moduleAssignments) != 0) {{ $assignment->moduleAssignments[0]->staff->room }} @endif</td>
                      
                      <td>
                          @if($assignment->category == 'OPTIONAL')
                           @if(!App\Utils\Util::collectionContains($options,$assignment))
                            @if(count($semester->electiveDeadlines) != 0) @if(strtotime(now()->format('Y-m-d')) <= strtotime($semester->electiveDeadlines[0]->deadline))
                            <a href="{{ url('student/module/'.$assignment->id.'/opt') }}" class="btn btn-primary"><i class="fa fa-check-circle"></i> Opt</a>
                            @else
                            <p class="ss-no-margin">OPTIONAL</p>
                            @endif
                            @endif
                          @else
                            <p class="ss-no-margin"><span class="badge badge-info">OPTED</span> 
							@if(count($semester->electiveDeadlines) != 0) @if(strtotime(now()->format('Y-m-d')) <= strtotime($semester->electiveDeadlines[0]->deadline))
								<a href="{{ url('student/module/'.$assignment->id.'/reset-option') }}" class="ss-color-danger ss-right ss-font-xs">Reset Option</a> @endif
                            @endif</p>
                          @endif
                          @else
                            <p class="ss-no-margin">{{ $assignment->category }}</p>
                          @endif
                      </td>
                    </tr>
                     @endif
                   @endforeach
                  
                  </tbody>
                </table>

              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @endforeach
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

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
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Modules') }} - Year {{ $student->year_of_study }} - {{ $semester->name }} - @if(count($semester->electivePolicies) != 0) ({{ $semester->electivePolicies[0]->number_of_options }} Options Maximum) @endif

                 @if(count($semester->electiveDeadlines) != 0) - Option Deadline {{ $semester->electiveDeadlines[0]->deadline }}  @endif</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                
                
                <table id="example2" class="table table-bordered table-hover ss-margin-bottom">
                  <thead>
                  <tr>
                    <th>Module</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  
                   @foreach($study_academic_year->moduleAssignments as $assignment)
                     @if($semester->id == $assignment->semester_id)
                    <tr>
                      <td>{{ $assignment->module->name }}</td>
                      <td>{{ $assignment->module->code }}</td>
                      <td>{{ $assignment->type }}</td>
                      <td>{{ $assignment->category }}</td>
                      
                      
                      <td>
                          @if($assignment->category == 'OPTIONAL')
                           @if(!App\Utils\Util::collectionContains($options,$assignment))
                            <a href="{{ url('student/module/'.$assignment->id.'/opt') }}" class="btn btn-primary"><i class="fa fa-check-circle"></i> Opt</a>
                          @else
                            <p class="ss-no-margin"><span class="badge badge-info">OPTED</span> <a href="{{ url('student/module/'.$assignment->id.'/reset-option') }}" class="ss-color-danger ss-right ss-font-xs">Reset Option</a></p>
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

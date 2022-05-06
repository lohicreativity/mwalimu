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
                <h3 class="card-title">Select Academic Year</h3>
              </div>
              <!-- /.card-header -->
                 <div class="card-body">
                 {!! Form::open(['url'=>'academic/postponements','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
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

            @if(count($postponements) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Postponements</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover ss-paginated-table">
                  <thead>
                  <tr>
                    <th>Student</th>
                    <th>Reg Number</th>
                    <th>Semester</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Is Renewal</th>
                    <th>Recommendation</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($postponements as $post)
                  <tr>
                    <td>{{ $post->student->first_name }} {{ $post->student->middle_name }} {{ $post->student->surname }}</td>
                    <td>{{ $post->student->registration_number }}</td>
                    <td>@if($post->semester) {{ $post->semester->name }} @endif</td>
                    <td>{{ $post->category }}</td>
                    <td>{{ $post->status }}</td>
                    <td>@if($post->is_renewal == 1) Yes @else No @endif</td>
                    <td>@if($post->recommended == 1) Recommended @else Not Recommended @endif</td>
                    <td>
                      @if(Auth::user()->hasRole('hod'))
                      <a class="btn btn-info btn-sm" href="{{ url('academic/postponement/'.$post->id.'/recommend') }}">
                              <i class="fas fa-eye-open">
                              </i>
                              @if($post->recommendation) Edit Recommendation @else Recommend @endif
                       </a>
                       @else

                      <a class="btn btn-info btn-sm" href="{{ url('academic/postponement/'.$post->id.'/recommend') }}">
                              <i class="fas fa-eye-open">
                              </i>
                              View Recommendation
                       </a>

                      <a class="btn btn-success btn-sm" href="{{ url('academic/postponement/'.$post->id.'/accept') }}">
                              <i class="fas fa-check">
                              </i>
                              Accept
                       </a>
                       <a class="btn btn-danger btn-sm" href="{{ url('academic/postponement/'.$post->id.'/decline') }}">
                              <i class="fas fa-check">
                              </i>
                              Decline
                       </a>
                       @endif
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

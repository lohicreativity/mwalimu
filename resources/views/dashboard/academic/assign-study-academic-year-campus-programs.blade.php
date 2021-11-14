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
            <h1>{{ __('Study Academic Years Campus Programs') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Study Academic Years Campus Programs') }}</li>
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
                <h3 class="card-title">{{ __('Select Campus') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/study-academic-year-campus-programs','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $camp)
                       <option value="{{ $camp->id }}">{{ $camp->name }}</option>
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




            @if(count($study_academic_years) != 0 && $campus)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Study Academic Years') }} - {{ $campus->name }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Year</th>
                    <th>Programs</th>
                    <th>Assign</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($study_academic_years as $year)
                  <tr>
                    <td>{{ $year->academicYear->year }}</td>
                    <td>@foreach($year->campusPrograms as $prog)
                          <p class="ss-font-xs ss-no-margin">{{ $prog->program->name }}</p>
                        @endforeach
                    </td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-academic-year-{{ $year->id }}">
                              <i class="fas fa-plus">
                              </i>
                              Assign
                       </a>

                       <div class="modal fade" id="ss-edit-academic-year-{{ $year->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Assign Study Academic Year Programs</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                {!! Form::open(['url'=>'academic/study-academic-year-campus-programs/update','class'=>'ss-form-processing']) !!}

                                <table class="table table-bordered">
                                <thead>
                                  <tr>
                                    <th>Program</th>
                                    <th>Assign</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach($campusPrograms as $program)
                                    <tr>
                                      <td>{{ $program->program->name }}</td>
                                      <td>
                                        @if(App\Utils\Util::collectionContains($year->campusPrograms,$program))
                                         
                                         {!! Form::checkbox('year_'.$year->id.'_program_'.$program->id,$program->id,true) !!} 

                                         @else
                                          
                                          {!! Form::checkbox('year_'.$year->id.'_program_'.$program->id,$program->id) !!}

                                         @endif
                                      </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                </table>

                                    <div class="form-group">
                                      {!! Form::input('hidden','study_academic_year_id',$year->id) !!}
                                      {!! Form::input('hidden','campus_id',$campus->id) !!}
                                    </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Assign Study Academic Year Campus Programs') }}</button>
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
                <h3 class="card-title">{{ __('No Study Academic Years Created') }}</h3>
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

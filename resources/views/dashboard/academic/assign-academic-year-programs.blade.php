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
            <h1>{{ __('Academic Years Programs') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Academic Years Programs') }}</li>
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

            @if(count($academic_years) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Academic Years') }}</h3>
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
                  @foreach($academic_years as $academic_year)
                  <tr>
                    <td>{{ $academic_year->year }}</td>
                    <td>@foreach($academic_year->programs as $prog)
                          <p class="ss-font-xs ss-no-margin">{{ $prog->name }}</p>
                        @endforeach
                    </td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-academic_year-{{ $academic_year->id }}">
                              <i class="fas fa-check-circle">
                              </i>
                              Assign
                       </a>

                       <div class="modal fade" id="ss-edit-academic_year-{{ $academic_year->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Assign Academic Year Programs</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                {!! Form::open(['url'=>'academic/academic-year-programs/update','class'=>'ss-form-processing']) !!}

                                <table class="table table-bordered">
                                <thead>
                                  <tr>
                                    <th>Program</th>
                                    <th>Assign</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach($programs as $program)
                                    <tr>
                                      <td>{{ $program->name }}</td>
                                      <td>
                                        @if(App\Utils\Util::collectionContains($academic_year->programs,$program))
                                         
                                         {!! Form::checkbox('year_'.$academic_year->id.'_program_'.$program->id,$program->id,true) !!} 

                                         @else
                                          
                                          {!! Form::checkbox('year_'.$academic_year->id.'_program_'.$program->id,$program->id) !!}

                                         @endif
                                      </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                </table>

                                    <div class="form-group">
                                      {!! Form::input('hidden','academic_year_id',$academic_year->id) !!}
                                    </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Assign Academic Year Programs') }}</button>
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
                <div class="ss-pagination-links">
                {!! $academic_years->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Academic Years Created') }}</h3>
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

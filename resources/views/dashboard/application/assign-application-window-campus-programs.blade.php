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
            <h1>{{ __('Offered Programmes') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Offered Programmes') }}</li>
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
                <h3 class="card-title">{{ __('Select Application Window') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'application/application-window-campus-programs','class'=>'ss-form-processing','method'=>'GET']) !!}
                 <div class="row">
                   <div class="form-group col-12">
                     @if(Auth::user()->hasRole('administrator'))
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $k=>$win)
                        <option value="{{ $win->id }}" @if($request->get('application_window_id') == $win->id) selected="selected" @endif>{{ $win->begin_date }} - {{ $win->end_date }} - {{ $win->campus->name }} - {{ $win->intake->name }}</option>
                        @endforeach
                     </select>
                     @else
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $k=>$win)
                          @if($staff->campus_id == $win->campus_id)
                        <option value="{{ $win->id }}" @if($request->get('application_window_id') == $win->id) selected="selected" @endif>{{ $win->begin_date }} - {{ $win->end_date }} - {{ $win->campus->name }} - {{ $win->intake->name }}</option>
                          @endif
                        @endforeach
                     </select>
                     @endif
                   </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->



            @if($window && $campus)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Offered Programmes') }} - {{ $campus->name }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
              {{--  {!! Form::open(['url'=>'application/application-window-campus-programs','method'=>'GET']) !!}
                {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                {!! Form::input('hidden','campus_id',$request->get('campus_id')) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for module name or code">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!} --}}
                <table id="example2" class="table table-bordered table-hover ss-margin-top">
                  <thead>
                  <tr>
                    <th>Year</th>
                    <th>Intake</th>
                    <th>Programmes</th>
                    <th>Assign</th>
                  </tr>
                  </thead>
                  <tbody>
                  
                  <tr>
                    <td>{{ $window->begin_date }} - {{ $window->end_date }}</td>
                    <td>{{ $window->intake->name }}</td>
                    <td>
                        @foreach($window->campusPrograms as $key=>$prog)
                        <p class="ss-font-xs ss-no-margin">{{ $prog->program->name }}</p>
                        @endforeach
                    </td>
                    <td>
                    
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-academic-window-{{ $window->id }}">
                              <i class="fas fa-plus">
                              </i>
                              Assign
                       </a>

                       <div class="modal fade" id="ss-edit-academic-window-{{ $window->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Assign Offered Campus Programmes</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

                                <div>
                                <input type="checkbox" class="btn btn-primary" name="checkall" id="checkall"
		                              onClick="check_uncheck_checkbox(this.checked);" /><span class="check-text">Check All</span>
                                </div><br>

                                {!! Form::open(['url'=>'application/application-window-campus-programs/update','class'=>'ss-form-processing']) !!}

                                <table class="table table-bordered assign-table">
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
                                        @if(App\Utils\Util::collectionContains($window->campusPrograms,$program))
                                         
                                         {!! Form::checkbox('window_'.$window->id.'_program_'.$program->id,$program->id,true, ['class' => 'assign-checkbox']) !!} 

                                         @else
                                          
                                          {!! Form::checkbox('window_'.$window->id.'_program_'.$program->id,$program->id, ['class' => 'assign-checkbox']) !!}

                                         @endif
                                      </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                </table>

                                    <div class="form-group">
                                      {!! Form::input('hidden','application_window_id',$window->id) !!}
                                      {!! Form::input('hidden','campus_id',$campus->id) !!}
                                    </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Assign Offered Programmes') }}</button>
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
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Offered Programmes Selected') }}</h3>
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

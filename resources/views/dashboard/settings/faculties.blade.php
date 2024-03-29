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
            <h1>{{ __('Faculties') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Faculties') }}</li>
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

                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('Add Faculty') }}</h3>
                        </div>

                        {!! Form::open(['url'=>'settings/faculty/store','class'=>'ss-form-processing']) !!}
                        <div class="card-body">
                            @if(Auth::user()->hasRole('administrator'))
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Faculty Name</label>
                                        <input type="text" required class="form-control" name="name" placeholder="Faculty Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Faculty Abbreviation</label>
                                        <input type="text" required class="form-control" name="abbreviation" placeholder="Faculty Abbreviation">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Campus</label>
                                        <select name="campuses" required class="form-control">
                                            <option value="">Select campus</option>
                                            @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @elseif(Auth::user()->hasRole('admission-officer'))
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Faculty Name</label>
                                        <input type="text" required class="form-control" name="name" placeholder="Faculty Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Faculty Abbreviation</label>
                                        <input type="text" required class="form-control" name="abbreviation" placeholder="Faculty Abbreviation">
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">{{ __('Add Faculty') }}</button>
                        </div>
                        {!! Form::close() !!}

                    </div>

                </div>
            </div>

            <div class="row">
                <div class="col-12">

                    @if(count($faculties) != 0)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('List of Faculties') }}</h3>
                        </div>

                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Abbreviation</th>
                                        @if(Auth::user()->hasRole('administrator'))
                                        <th>Campus</th>
                                        @endif
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($faculties as $faculty)
                                    <tr>
                                        <td>{{ $faculty->name }}</td>
                                        <td>{{ $faculty->abbreviation }}</td>
                                        @if(Auth::user()->hasRole('administrator'))
                                        <td>
                                            {{ $faculty->campus->name }}
                                        </td>
                                        @endif
                                        <td>
                                            <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-faculty-{{ $faculty->id }}">
                                                <i class="fas fa-pencil-alt"></i>
                                                    Edit Faculty
                                            </a>

                                            <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-campus-{{ $faculty->id }}">
                                                <i class="fas fa-trash"></i>
                                                    Delete Faculty
                                            </a>

                                            <div class="modal fade" id="ss-edit-faculty-{{ $faculty->id }}">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Faculty</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @php
                                                                $name = [
                                                                    'placeholder'=>'Faculty Name',
                                                                    'class'=>'form-control',
                                                                    'required'=>true
                                                                ];

                                                                $abbreviation = [
                                                                    'placeholder'=>'Abbreviation',
                                                                    'class'=>'form-control',
                                                                    'required'=>true
                                                                ];
                                                            @endphp

                                                            {!! Form::open(['url'=>'settings/faculty/update','class'=>'ss-form-processing']) !!}
                                                            <div class="row">
                                                                <div class="col-md-7">
                                                                    <div class="form-group">
                                                                        {!! Form::label('','Faculty Name') !!}
                                                                        {!! Form::text('name', $faculty->name, $name) !!}

                                                                        {!! Form::input('hidden','faculty_id', $faculty->id) !!}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="form-group">
                                                                        {!! Form::label('','Abbreviation') !!}
                                                                        {!! Form::text('abbreviation', $faculty->abbreviation, $abbreviation) !!}
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        {!! Form::label('','Campus') !!}
                                                                        <select name="campus" class="form-control">
                                                                            <option value="">Select campus</option>
                                                                            @foreach($campuses as $campus)
                                                                            <option value="{{ $campus->id }}" @if($campus->id == $faculty->campus_id) selected="selected" @endif>{{ $campus->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        {!! Form::input('hidden','faculty_id', $faculty->id) !!}
                                                                    </div>
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

                                            <div class="modal fade" id="ss-delete-campus-{{ $faculty->id }}">
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
                                                                    <p id="ss-confirmation-text">Are you sure you want to delete this faculty from the list?</p>
                                                                    <div class="ss-form-controls">
                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                        <a href="{{ url('settings/faculty/'.$faculty->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                                    <tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

        </div>
    </section>
            
           
          
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

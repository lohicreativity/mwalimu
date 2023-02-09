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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($faculties as $faculty)
                                    <tr>
                                        <td>{{ $faculty->name }}</td>
                                        <td>{{ $faculty->abbreviation }}</td>
                                        <td>
                                            <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-faculty-{{ $faculty->id }}">
                                                <i class="fas fa-list-alt"></i>
                                                    Edit Faculty
                                            </a>
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

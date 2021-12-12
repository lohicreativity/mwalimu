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
            <h1>{{ __('Roles') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Roles') }}</li>
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
              <div class="card-header">
                <h3 class="card-title">{{ __('Select Module') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'settings/role/'.$role->id.'/permissions','class'=>'ss-form-processing','method'=>'GET']) !!}
                <div class="card-body">
                  <div class="form-group">
                    {!! Form::label('','Select module') !!}
                    <select name="system_module_id" class="form-control" required>
                      <option value="">Select Module</option>
                      @foreach($system_modules as $mod)
                      <option value="{{ $mod->id }}">{{ $mod->name }}</option>
                      @endforeach
                    </select>

                    {!! Form::input('hidden','role_id',$role->id) !!}
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->


            @if(count($permissions) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Permissions') }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'settings/role/permission/update','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 

                 {!! Form::input('hidden','role_id',$role->id) !!}
                <div class="row">
                   @foreach($permissions as $permission)
                     <div class="form-check col-4">
                      @if(App\Utils\Util::collectionContains($role->permissions,$permission))
                      <input type="checkbox" name="permission_{{ $permission->id }}" class="form-check-input" value="{{ $permission->id }}" id="ss-permission-{{ $permission->id }}" checked="checked">
                      @else
                      <input type="checkbox" name="permission_{{ $permission->id }}" class="form-check-input" value="{{ $permission->id }}" id="ss-permission-{{ $permission->id }}">
                      @endif
                      <label class="form-check-label" for="ss-permission-{{ $permission->id }}">{{ $permission->display_name }}</label>
                     </div>
                   @endforeach
                </div>
  
              </div>
              <!-- /.card-body -->
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Update Permissions') }}</button>
                </div>
              {!! Form::close() !!}
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

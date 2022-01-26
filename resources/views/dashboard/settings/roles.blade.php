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
                <h3 class="card-title">{{ __('Add Role') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $display_name = [
                     'placeholder'=>'Display name',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'settings/role/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  
                 <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Name') !!}
                    {!! Form::text('name',null,$name) !!}

                    {!! Form::input('hidden','is_system_role',0) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Display name') !!}
                    {!! Form::text('display_name',null,$display_name) !!}
                  </div>
                </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Role') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($roles) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Roles') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Display Name</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($roles as $role)
                  <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->display_name }}</td>
                    <td>
                      @foreach($role->permissions as $perm)
                      <span class="badge badge-info">{{ $perm->name }}</span>
                      @endforeach
                    </td>
                    <td>
                      @can('assign-role-permissions')
                      <a class="btn btn-info btn-sm" href="{{ url('settings/role/'.$role->id.'/permissions') }}">
                              <i class="fas fa-check-circle">
                              </i>
                              Assign Permissions
                       </a>
                      @endcan 
                      @can('edit-role')
                      <a class="btn btn-info btn-sm" href="#" @if($role->is_system_role == 1) disabled="disabled" @else data-toggle="modal" data-target="#ss-edit-role-{{ $role->id }}" @endif>
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-edit-role-{{ $role->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Role</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                {!! Form::open(['url'=>'settings/role/update','class'=>'ss-form-processing']) !!}

                                    <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Name') !!}
                                      {!! Form::text('name',$role->name,$name) !!}

                                      {!! Form::input('hidden','is_system_role',$role->is_system_role) !!}
                                      {!! Form::input('hidden','role_id',$role->id) !!}
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Display name') !!}
                                      {!! Form::text('display_name',$role->display_name,$display_name) !!}
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
                      @can('delete-role')
                      <a class="btn btn-danger btn-sm" href="#" @if($role->is_system_role == 1) disabled="disabled" @else  data-toggle="modal" data-target="#ss-delete-role-{{ $role->id }}" @endif>
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                       @endcan

                       <div class="modal fade" id="ss-delete-role-{{ $role->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this role from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('settings/role/'.$role->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
                <div class="ss-pagination-links">
                {!! $roles->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
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

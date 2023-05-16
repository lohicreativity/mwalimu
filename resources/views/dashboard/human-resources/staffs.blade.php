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
            <h1>{{ __('Staff Members') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Staff Members') }}</li>
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
                <h3 class="card-title">{{ __('Staff Members') }}</h3><br>
                @can('add-staff')
                <a href="{{ url('staff/staff/create') }}" class="btn btn-info ss-right">{{ __('Add Staff') }}</a>
                @endcan
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                @if(count($staffs) != 0)
                <table id="example2" class="table table-bordered table-hover ss-margin-top ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Email</th>
                    <th>Phone#</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($staffs as $key=>$staff)
                  <tr>
                    <td>{{ ($key+1) }}</td>
                    <td>{{ $staff->title }} {{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->surname }}</td>
                    <td>{{ $staff->category }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>{{ $staff->phone }}</td>
                    <td>
                      @can('view-staff')
                      <a class="btn btn-info btn-sm" href="{{ url('staff/staff/'.$staff->id.'/show') }}">
                              <i class="fas fa-id-card">
                              </i>
                              View
                       </a>
                      @endcan
                        
                      @can('assign-staff-roles')
                       <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-roles-staff-{{ $staff->id }}">
                              <i class="fas fa-user">
                              </i>
                              Assign Roles
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-roles-staff-{{ $staff->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Assign Roles</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              
                                {!! Form::open(['url'=>'staff/staff/update-roles','class'=>'ss-form-processing']) !!}
                 

                               {!! Form::input('hidden','user_id',$staff->user->id) !!}
                              <div class="row">
                                 @foreach($roles as $role)
                                  <div class="col-4">
                                   <div class="form-check">
                                    @if(App\Utils\Util::collectionContains($staff->user->roles,$role))
                                    <input type="checkbox" name="role_{{ $role->id }}" class="form-check-input" value="{{ $role->id }}" id="ss-role-{{ $role->id }}" checked="checked">
                                    @else
                                    <input type="checkbox" name="role_{{ $role->id }}" class="form-check-input" value="{{ $role->id }}" id="ss-role-{{ $role->id }}">
                                    @endif
                                    <label class="form-check-label" for="ss-role-{{ $role->id }}">{{ $role->display_name }}</label>
                                   </div>
                                  </div>
                                 @endforeach
                              </div>
                               <div class="ss-form-controls">
                                  <button type="submit" class="btn btn-primary">{{ __('Update Roles') }}</button>
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
                      
                      @can('edit-staff')
                      <a class="btn btn-info btn-sm" href="{{ url('staff/staff/'.$staff->id.'/edit') }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       @endcan

                       @can('delete-staff')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-staff-{{ $staff->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                       @endcan

                       <div class="modal fade" id="ss-delete-staff-{{ $staff->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this staff from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Abort</button>
                                         <a href="{{ url('staff/staff/'.$staff->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                @endif
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            
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

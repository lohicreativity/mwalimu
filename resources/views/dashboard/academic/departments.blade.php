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
            <h1>{{ __('Departments') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Departments') }}</li>
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
           
            @can('add-department')
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Department') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $abbreviation = [
                     'placeholder'=>'Abbreviation',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $description = [
                     'placeholder'=>'Description',
                     'class'=>'form-control',
                     'rows'=>2
                  ];
              @endphp
              {!! Form::open(['url'=>'academic/department/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-8">
                    {!! Form::label('','Name') !!}
                    {!! Form::text('name',null,$name) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Abbreviation') !!}
                    {!! Form::text('abbreviation',null,$abbreviation) !!}
                  </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-12">
                    {!! Form::label('','Description') !!}
                    {!! Form::textarea('description',null,$description) !!}
                  </div>
                  </div>

                  @if(Auth::user()->hasRole('administrator'))
                  <div class="row">
                    <div class="form-group col-4">
                      {!! Form::label('','Type') !!}
                      <select name="unit_category_id" class="form-control" required>
                        <option value="">Select Type</option>
                        @foreach($unit_categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Parent') !!}
                      <select name="parent_id" class="form-control">
                        <option value="">Select Parent</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Campus') !!}
                      <!-- <select name="campuses[]" class="form-control ss-select-tags" multiple="multiple"> -->
                        <select name="campuses" class="form-control" required>
                          <option value="">Select Campus</option>
                          @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                          @endforeach
                        </select>
                    </div>
                  </div>
                  @elseif(Auth::user()->hasRole('admission-officer'))
                  <input type="text" id="campus_id" value="{{ $staff->campus_id }}">
                  <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Type') !!}
                      <select name="unit_category_id" class="form-control" id="unit-categories" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                        <option value="">Select Type</option>
                        @foreach($unit_categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Parent') !!}
                      <select name="parent_id" id="parents" class="form-control">
                        <option value="">Select Parent</option>
                        <!-- @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach -->
                      </select>
                    </div>
                  </div>
                  @endif
                  
                  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Department') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($departments) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Departments') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                @if(Auth::user()->hasRole('administrator'))
                <table id="example2" class="table table-bordered table-hover ss-paginated-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Abbreviation</th>
                      <th>Type</th>
                      <th>Campus</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                      @foreach($departments as $department)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $department->name }}</td>
                          <td>{{ $department->abbreviation }}</td>
                          <td>{{ $department->unitCategory->name }}</td>
                          <td>
                            @foreach($department->campuses as $campus)
                            <p class="ss-no-margin">{{ $campus->name }}</p>
                            @endforeach
                          </td>
                          <td>
                            @can('edit-department')
                            <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-department-{{ $department->id }}">
                                    <i class="fas fa-pencil-alt">
                                    </i>
                                    Edit
                            </a>
                            @endcan

                            <div class="modal fade" id="ss-edit-department-{{ $department->id }}">
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h4 class="modal-title">Edit Department</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <div class="modal-body">
                                      @php
                                          $name = [
                                            'placeholder'=>'Name',
                                            'class'=>'form-control',
                                            'required'=>true
                                          ];

                                          $abbreviation = [
                                            'placeholder'=>'Abbreviation',
                                            'class'=>'form-control',
                                            'required'=>true
                                          ];

                                          $description = [
                                            'placeholder'=>'Description',
                                            'class'=>'form-control',
                                            'rows'=>2
                                          ];
                                      @endphp
                                      {!! Form::open(['url'=>'academic/department/update','class'=>'ss-form-processing']) !!}

                                          <div class="row">
                                          <div class="form-group col-8">
                                            {!! Form::label('','Name') !!}
                                            {!! Form::text('name',$department->name,$name) !!}

                                            {!! Form::input('hidden','department_id',$department->id) !!}
                                          </div>
                                          <div class="form-group col-4">
                                            {!! Form::label('','Abbreviation') !!}
                                            {!! Form::text('abbreviation',$department->abbreviation,$abbreviation) !!}
                                          </div>
                                          </div>
                                          <div class="row">
                                            <div class="form-group col-12">
                                            {!! Form::label('','Description') !!}
                                            {!! Form::textarea('description',$department->description,$description) !!}
                                          </div>
                                          </div>
                                          <div class="row">
                                          <div class="form-group col-4">
                                            {!! Form::label('','Type') !!}
                                            <select name="unit_category_id" class="form-control" id="unit-categories" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                                              <option value="">Select Type</option>
                                              @foreach($unit_categories as $category)
                                              <option value="{{ $category->id }}" @if($category->id == $department->unit_category_id) selected="selected" @endif>{{ $category->name }}</option>
                                              @endforeach
                                            </select>
                                          </div>
                                          <div class="form-group col-4">
                                            {!! Form::label('','Parent') !!}
                                            <select name="parent_id" class="form-control" id="parents" required>
                                              <option value="">Select one</option>
                                              @foreach($all_departments as $dept)
                                              <option value="{{ $dept->id }}" @if($dept->id == $department->parent_id) selected="selected" @endif>{{ $dept->name }}</option>
                                              @endforeach
                                            </select>
                                          </div>
                                          <div class="form-group col-4">
                                            {!! Form::label('','Campus') !!}<br>
                                            <select name="campuses" class="form-control" style="width: 100%;">
                          <!-- <select name="campuses[]" class="form-control ss-select-tags" style="width: 100%;" multiple="multiple"> -->
                                              <option value="">Select Campus</option>
                                              @foreach($campuses as $campus)
                                              <option value="{{ $campus->id }}" @if(App\Utils\Util::collectionContains($department->campuses,$campus)) selected="selected" @endif>{{ $campus->name }}</option>
                                              @endforeach
                                            </select>
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
                            @can('delete-department')
                            <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-department-{{ $department->id }}">
                                    <i class="fas fa-trash">
                                    </i>
                                    Delete
                            </a>
                            @endcan

                            <div class="modal fade" id="ss-delete-department-{{ $department->id }}">
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
                                            <p id="ss-confirmation-text">Are you sure you want to delete this department from the list?</p>
                                            <div class="ss-form-controls">
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                              <a href="{{ url('academic/department/'.$department->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                @elseif(Auth::user()->hasRole('admission-officer'))
                  <table id="example2" class="table table-bordered table-hover ss-admission-officer-table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Abbreviation</th>
                        <th>Type</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>

                      @foreach($departments as $department)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $department->name }}</td>
                          <td>{{ $department->abbreviation }}</td>
                          <td>{{ $department->categoryName }}</td>
                          <td>
                            @can('edit-department')
                            <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-department-{{ $department->id }}">
                                    <i class="fas fa-pencil-alt">
                                    </i>
                                    Edit
                            </a>
                            @endcan
                            <div class="modal fade" id="ss-edit-department-{{ $department->id }}">
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h4 class="modal-title">Edit Department</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <div class="modal-body">
                                      @php
                                          $name = [
                                            'placeholder'=>'Name',
                                            'class'=>'form-control',
                                            'required'=>true
                                          ];

                                          $abbreviation = [
                                            'placeholder'=>'Abbreviation',
                                            'class'=>'form-control',
                                            'required'=>true
                                          ];

                                          $description = [
                                            'placeholder'=>'Description',
                                            'class'=>'form-control',
                                            'rows'=>2
                                          ];
                                      @endphp
                                      {!! Form::open(['url'=>'academic/department/update','class'=>'ss-form-processing']) !!}

                                          <div class="row">
                                          <div class="form-group col-8">
                                            {!! Form::label('','Name') !!}
                                            {!! Form::text('name',$department->name,$name) !!}

                                            {!! Form::input('hidden','department_id',$department->id) !!}
                                          </div>
                                          <div class="form-group col-4">
                                            {!! Form::label('','Abbreviation') !!}
                                            {!! Form::text('abbreviation',$department->abbreviation,$abbreviation) !!}
                                          </div>
                                          </div>
                                          <div class="row">
                                            <div class="form-group col-12">
                                            {!! Form::label('','Description') !!}
                                            {!! Form::textarea('description',$department->description,$description) !!}
                                          </div>
                                          </div>
                                          <div class="row">
                                          <div class="form-group col-6">
                                            {!! Form::label('','Type') !!}
                                            <select name="unit_category_id" class="form-control" id="unit-categories" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                                              <option value="">Select Type</option>
                                              @foreach($unit_categories as $category)
                                              <option value="{{ $category->id }}" @if($category->id == $department->categoryId) selected="selected" @endif>{{ $category->name }}</option>
                                              @endforeach
                                            </select>
                                          </div>
                                          <div class="form-group col-6">
                                            {!! Form::label('','Parent') !!}
                                            <select name="parent_id" class="form-control" id="parents" required>
                                              <option value="">Select one</option>
                                              @foreach($all_departments as $dept)
                                              <option value="{{ $dept->id }}" @if($dept->id == $department->parent_id) selected="selected" @endif>{{ $dept->name }}</option>
                                              @endforeach
                                            </select>
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
                            @can('delete-department')
                            <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-department-{{ $department->id }}">
                                    <i class="fas fa-trash">
                                    </i>
                                    Delete
                            </a>
                            @endcan
                            <div class="modal fade" id="ss-delete-department-{{ $department->id }}">
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
                                            <p id="ss-confirmation-text">Are you sure you want to delete this department from the list?</p>
                                            <div class="ss-form-controls">
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                              <a href="{{ url('academic/department/'.$department->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                @endif
                
                
                <!-- <div class="ss-pagination-links">
                {!! $departments->render() !!}
                </div> -->
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

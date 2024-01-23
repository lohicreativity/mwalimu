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

                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))
                  <div class="row">
                  <div class="form-group col-4">
                      {!! Form::label('','Campus') !!}
                      <!-- <select name="campuses[]" class="form-control ss-select-tags" multiple="multiple"> -->
                      <select name="campus_id" class="form-control" id="campuses" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                      <option value="">Select Campus</option>
                      @foreach($campuses as $cp)
                      <option value="{{ $cp->id }}" @if($staff->campus_id == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
                      @endforeach
                      </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Type') !!}
                      <select name="unit_category_id" class="form-control" id="unit-categories" data-target="#parents" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                        <option value="">Select Type</option>
                        @foreach($unit_categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>

                        @endforeach
                      </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Parent',array('id' => 'parent-label')) !!}
                      <div id="parent_input"></div>
                      <select name="parent_id" id="parents" class="form-control">
                        <option value="">Select Parent</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach 
                      </select>
                    </div>
                    {!! Form::input('hidden','decoy_id',$staff->campus_id,['id'=>'campus_id']) !!}
                  </div>
                  @elseif(Auth::user()->hasRole('admission-officer'))
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
                      {!! Form::label('','Parent',array('id' => 'parent-label')) !!}
                      <div id="parent_input"></div>
                      <select name="parent_id" id="parents" class="form-control">
                        <option value="">Select Parent</option>
                        @foreach($all_departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach 
                      </select>
                    </div>
                    {!! Form::input('hidden','campus_id',$staff->campus_id,['id'=>'campus_id']) !!}
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
                @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))
                  <table class="table table-bordered table-hover ss-admission-officer-table ss-paginated-table">
                    <thead>
                      <tr>
                        <th>SN</th>
                        <th>Name</th>
                        <th>Abbreviation</th>
                        <th>Type</th>
                        <th>Parent</th>
                        @if(Auth::user()->hasRole('administrator'))
                          <th>Campus</th>
                        @endif
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($departments as $department)
                      @php
                        $current_parent_id = $department->id;
                      @endphp
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->abbreviation }}</td>
                        <td>{{ $department->unitCategory->name }}</td>
                        <td>
                          @foreach($all_departments as $dept)
                            @if($dept->id == $department->id && $department->unit_category_id == 1 )
                              @foreach($campuses as $campus)
                                @if($department->parent_id == $campus->id)
                                  {{ $campus->name }}
                                  @break   
                                @endif
                              @endforeach
                            @elseif($dept->id == $department->id && $department->unit_category_id == 2 )

                              @foreach($faculties as $faculty)
                                @if($department->parent_id == $faculty->id)
                                  {{ $faculty->name }}
                                  @break   
                                @endif
                              @endforeach
                            @elseif($department->unit_category_id == 4)
                              @if($department->parent_id == $dept->id)
                                {{ $dept->name }}
                                @break   
                              @endif  
                            @endif
                          @endforeach
                        </td>
                        @if(Auth::user()->hasRole('administrator'))
                          <td>
                            @foreach($department->campuses as $campus)
                            <p class="ss-no-margin">{{ $campus->name }}</p>
                            @endforeach
                          </td>
                        @endif
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
                                      $current_edited_parent_id = $department->parent_id;
                                    @endphp

                                    {!! Form::open(['url'=>'academic/department/update','class'=>'ss-form-processing']) !!}

                                    @if(Auth::user()->hasRole('admission-officer'))
                                      <input type="hidden" name="staff_campus" value="{{ $staff->campus_id }}">
                                    @endif
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
                                    
                                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))
                                    <div class="row">
                                      <div class="form-group col-4">
                                        {!! Form::label('','Campus') !!}
                                        <!-- <select name="campuses[]" class="form-control ss-select-tags" multiple="multiple"> -->
                                        <select name="campus_id" class="form-control" id="campuses-edit" data-target="#parents-edit" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                                        <option value="">Select Campus</option>
                                        @foreach($campuses as $cp)
                                          @foreach($departments->campuses as $dept_camp)
                                            @if($cp->id == $dept_camp->campus_id)
                                              <option value="{{ $cp->id }}" selected="selected">{{ $cp->name }}</option>
                                              @break
                                            @else
                                              <option value="{{ $cp->id }}">{{ $cp->name }}</option>
                                              @break
                                            @endif
                                          @endforeach
                                        @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Type') !!}
                                        <select name="unit_category_id" class="form-control" id="unit-categories-edit" data-target="#parents-edit" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                                          <option value="">Select Type</option>
                                          @foreach($unit_categories as $category)
                                          <option value="{{ $category->id }}" @if($department->unit_category_id == $category->id) selected = 'selected' @endif>{{ $category->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Parent',array('id' => 'parent-label-edit')) !!}
  
                                        <div id="parent_input"></div>
                                        <select name="parent_id" id="parents-edit" class="form-control">

                                          <option value="">Select Parent</option>
                                          @foreach($all_departments as $dept)
                                          @php
                                          $parent_name = $parent_id = null;

                                            if($dept->unit_category_id == 1 ){
                                              foreach($campuses as $campus){
                                                if($dept->parent_id == $campus->id){
                                                  $parent_name = $campus->name;
                                                  $parent_id = $campus->id;
                                                  break; 
                                                }
                                              }  
                                            }elseif($dept->unit_category_id == 2 ){
                                              foreach($faculties as $faculty){
                                                if($dept->parent_id == $faculty->id){
                                                  $parent_name = $faculty->name;
                                                  $parent_id = $faculty->id;
                                                  break; 
                                                }  
                                              }
                                            }elseif($dept->unit_category_id == 4){
                                              if($dept->parent_id == $dept->id){
                                                $parent_name == $dept->name;
                                                $parent_id = $dept->id;
                                              }
                                            } 
                                        @endphp

                                          <option value="{{ $dept->id }}" @if($department->parent_id == $parent_id) selected = 'selected' @endif>{{ $dept->name }}
                                          </option>
                                          @endforeach 
                                        </select>
                                      </div>

                                      {!! Form::input('hidden','current_parent_id',$current_edited_parent_id) !!}
                                    </div>

                                    @elseif(Auth::user()->hasRole('admission-officer'))
                                    <div class="row">
                                      <div class="form-group col-6">
                                        {!! Form::label('','Type') !!}
                                        <select name="unit_category_id" class="form-control" id="unit-categories-edit" data-target="#parents-edit" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-parents') }}" required>
                                          <option value="">Select Type</option>
                                          @foreach($unit_categories as $category)
                                          <option value="{{ $category->id }}">{{ $category->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-6">
                                        {!! Form::label('','Parent',array('id' => 'parent-label-edit')) !!}
                                        <div id="parent_input_edit"></div>
                                        <select name="parent_id" id="parents-edit" class="form-control">
                                          <option value="">Select Parent</option>
                                          @foreach($all_departments as $dept)
                                          <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                          @endforeach 
                                        </select>
                                      </div>
                                      {!! Form::input('hidden','campus_id',$staff->campus_id,['id'=>'campus_id']) !!}
                                    </div>
                                    @endif

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

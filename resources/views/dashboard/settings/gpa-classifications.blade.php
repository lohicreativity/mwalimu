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
            <h1>{{ __('GPA Classification') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('GPA Classification') }}</li>
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
                <h3 class="card-title">{{ __('Select NTA Level') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
             @php
				  $name = [
					 'placeholder'=>'Name',
					 'class'=>'form-control',
					 'required'=>true
				  ];

				  $min_gpa = [
					 'placeholder'=>'Min GPA',
					 'class'=>'form-control',
					 'required'=>true
				  ];

				  $max_gpa = [
					 'placeholder'=>'Max GPA',
					 'class'=>'form-control',
					 'required'=>true
				  ];
			  @endphp
			{!! Form::open(['url'=>'settings/gpa-classifications','method'=>'GET','class'=>'ss-form-processing']) !!}
			<div class="card-body">
				<div class="row">
				  <div class="form-group col-6">
					{!! Form::label('','NTA level') !!}
					<select name="nta_level_id" class="form-control">
					  <option value="">Select NTA Level</option>
					  @foreach($nta_levels as $level)
					  <option value="{{ $level->id }}" @if($level->id == $request->get('nta_level_id')) selected="selected" @endif>{{ $level->name }}</option>
					  @endforeach
					</select>
				  </div>
				  <div class="form-group col-6">
					{!! Form::label('','Study academic year') !!}
					<select name="nta_level_id" class="form-control">
					  <option value="">Select Study Academic Year</option>
					  @foreach($study_academic_years as $year)
					  <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
					  @endforeach
					</select>
				  </div>
				  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add NTA Level') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
             @php
					  $name = [
						 'placeholder'=>'Name',
						 'class'=>'form-control',
						 'required'=>true
					  ];

					  $min_gpa = [
						 'placeholder'=>'Min GPA',
						 'class'=>'form-control',
						 'required'=>true
					  ];

					  $max_gpa = [
						 'placeholder'=>'Max GPA',
						 'class'=>'form-control',
						 'required'=>true
					  ];
				  @endphp
				{!! Form::open(['url'=>'settings/gpa-classification/store','class'=>'ss-form-processing']) !!}
				 <div class="card-body">
					<div class="row">
					  <div class="form-group col-4">
						{!! Form::label('','Name') !!}
						{!! Form::text('name',null,$name) !!}
					  </div>
					  <div class="form-group col-4">
						{!! Form::label('','NTA level') !!}
						<select name="nta_level_id[]" class="form-control ss-select-tags" multiple>
						  <option value="">Select NTA Level</option>
						  @foreach($nta_levels as $level)
						  <option value="{{ $level->id }}">{{ $level->name }}</option>
						  @endforeach
						</select>
					  </div>
					  <div class="form-group col-4">
						{!! Form::label('','Study academic year') !!}
						<select name="study_academic_year_id" class="form-control">
						  <option value="">Select Study Academic Year</option>
						  @foreach($study_academic_years as $year)
						  <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
						  @endforeach
						</select>
					  </div>
					  </div>
					  <div class="row">
					  <div class="form-group col-6">
					  {!! Form::label('','Min GPA') !!}
					  {!! Form::text('min_gpa',null,$min_gpa) !!}
					</div>
					<div class="form-group col-6">
					  {!! Form::label('','Max GPA') !!}
					  {!! Form::text('max_gpa',null,$max_gpa) !!}
					</div>
					</div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add GPA Classification') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($classifications) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of GPA Classifications') }} - {{ $nta_level->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Min GPA</th>
                    <th>Max GPA</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($classifications as $class)
                  <tr>
                    <td>{{ $class->name }}</td>
                    <td>{{ $class->min_gpa }}</td>
                    <td>{{ $class->max_gpa }}</td>
                    <td>

                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-level-{{ $class->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      

                       <div class="modal fade" id="ss-edit-level-{{ $class->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit GPA Classification</h4>
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

                                      $min_gpa = [
                                         'placeholder'=>'Min GPA',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $max_gpa = [
                                         'placeholder'=>'Max GPA',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];
                                  @endphp
                                {!! Form::open(['url'=>'settings/gpa-classification/update','class'=>'ss-form-processing']) !!}
                                    <div class="row">
                                      <div class="form-group col-4">
                                        {!! Form::label('','Name') !!}
                                        {!! Form::text('name',$class->name,$name) !!}

                                        {!! Form::input('hidden','gpa_classification_id',$class->id) !!}
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','NTA level') !!}
                                        <select name="nta_level_id" class="form-control">
                                          <option value="">Select NTA Level</option>
                                          @foreach($nta_levels as $level)
                                          <option value="{{ $level->id }}" @if($level->id == $class->nta_level_id) selected="selected" @endif>{{ $level->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
									  <div class="form-group col-4">
                                        {!! Form::label('','Study academic year') !!}
                                        <select name="study_academic_year_id" class="form-control">
                                          <option value="">Select Study Academic Year</option>
                                          @foreach($study_academic_years as $year)
                                          <option value="{{ $year->id }}" @if($year->id == $class->study_academic_year_id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      </div>
                                      <div class="row">
                                      <div class="form-group col-6">
                                      {!! Form::label('','Min GPA') !!}
                                      {!! Form::text('min_gpa',$class->min_gpa,$min_gpa) !!}
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Max GPA') !!}
                                      {!! Form::text('max_gpa',$class->max_gpa,$max_gpa) !!}
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
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-level-{{ $class->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-level-{{ $class->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this gpa classification from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('settings/gpa-classification/'.$class->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                {!! $classifications->render() !!}
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

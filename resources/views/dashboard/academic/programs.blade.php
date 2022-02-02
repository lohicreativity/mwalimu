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
            <h1>{{ __('Programmes') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Programmes') }}</li>
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
            
            @can('add-programme')
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Programme') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $code = [
                     'placeholder'=>'Code',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $description = [
                     'placeholder'=>'Description',
                     'class'=>'form-control',
                     'rows'=>2,
                     'required'=>true
                  ];

                  $min_duration = [
                     'placeholder'=>'Min duration',
                     'class'=>'form-control',
                     'id'=>'ss-min-duration',
                     'readonly'=>true,
                     'required'=>true
                  ];

                  $max_duration = [
                     'placeholder'=>'Max duration',
                     'class'=>'form-control',
                     'id'=>'ss-max-duration',
                     'readonly'=>true,
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'academic/program/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-8">
                    {!! Form::label('','Name') !!}
                    {!! Form::text('name',null,$name) !!}
                  </div>
                   <div class="form-group col-4">
                    {!! Form::label('','Code') !!}
                    {!! Form::text('code',null,$code) !!}
                  </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-12">
                      {!! Form::label('','Description') !!}
                      {!! Form::textarea('description',null,$description) !!}
                    </div>
                  </div>
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Department') !!}
                    <select name="department_id" class="form-control" required>
                      <option value="">Select Department</option>
                      @foreach($departments as $department)
                      <option value="{{ $department->id }}">{{ $department->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Min duration') !!}
                    {!! Form::text('min_duration',null,$min_duration) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Max duration') !!}
                    {!! Form::text('max_duration',null,$max_duration) !!}
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','NTA level') !!}
                    <select name="nta_level_id" class="form-control ss-select-nta-level" required data-min-target="#ss-min-duration" data-max-target="#ss-max-duration" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-nta-level') }}" data-award-target="#ss-nta-award, #ss-nta-award-input">
                      <option value="">Select NTA level</option>
                      @foreach($nta_levels as $level)
                      <option value="{{ $level->id }}">{{ $level->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Award') !!}
                    <select name="award_id" class="form-control" required id="ss-nta-award" disabled="disabled">
                      <option value="">Select Award</option>
                      @foreach($awards as $award)
                      <option value="{{ $award->id }}">{{ $award->name }}</option>
                      @endforeach
                    </select>
                    {!! Form::input('hidden','award_id',null,['id'=>'ss-nta-award-input']) !!}
                    {!! Form::input('hidden','campus_id',$staff->campus_id) !!}
                  </div>
                   
                 </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Programme') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($programs) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Programmes') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                {!! Form::open(['url'=>'academic/programs','method'=>'GET']) !!}
                <div class="row">
                {!! Form::label('','Order NTA Level') !!}<br>
                <div class="form-group col-3">
                  <select name="nta_level" class="form-control" required>
                    <option value="ASC">ASC</option>
                    <option value="DESC">DESC</option>
                  </select>
                </div>
                <div class="input-group col-9">
                 <input type="text" name="query" class="form-control" placeholder="Search for module name or code">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
              </div>
                {!! Form::close() !!}
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>NTA Level</th>
                    <th>Min Duration</th>
                    <th>Max Duration</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($programs as $program)
                  <tr>
                    <td>{{ $program->name }}</td>
                    <td>{{ $program->code }}</td>
                    <td>{{ $program->ntaLevel->name }}</td>
                    <td>{{ $program->min_duration }}</td>
                    <td>{{ $program->max_duration }}</td>
                    <td>
                      @can('edit-programme')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-program-{{ $program->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-edit-program-{{ $program->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Programme</h4>
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

                                    $code = [
                                       'placeholder'=>'Code',
                                       'class'=>'form-control',
                                       'required'=>true
                                    ];

                                    $description = [
                                       'placeholder'=>'Description',
                                       'class'=>'form-control',
                                       'rows'=>2,
                                       'required'=>true
                                    ];

                                   $min_duration = [
                                     'placeholder'=>'Min duration',
                                     'class'=>'form-control',
                                     'id'=>'ss-min-duration-'.$program->id,
                                     'readonly'=>true,
                                     'required'=>true
                                  ];

                                  $max_duration = [
                                     'placeholder'=>'Max duration',
                                     'class'=>'form-control',
                                     'id'=>'ss-max-duration-'.$program->id,
                                     'readonly'=>true,
                                     'required'=>true
                                  ];
                                @endphp

                                {!! Form::open(['url'=>'academic/program/update','class'=>'ss-form-processing']) !!}
                                   <div class="row">
                                    <div class="form-group col-8">
                                      {!! Form::label('','Name') !!}
                                      {!! Form::text('name',$program->name,$name) !!}

                                      {!! Form::input('hidden','program_id',$program->id) !!}
                                      {!! Form::input('hidden','campus_id',$staff->campus_id) !!}
                                    </div>
                                    <div class="form-group col-4">
                                      {!! Form::label('','Code') !!}
                                      {!! Form::text('code',$program->code,$code) !!}
                                    </div>
                                    </div>
                                    <div class="row">
                                      <div class="form-group col-12">
                                        {!! Form::label('','Description') !!}
                                        {!! Form::textarea('description',$program->description,$description) !!}
                                      </div>
                                    </div>
                                        <div class="row">
                                        <div class="form-group col-4">
                                          {!! Form::label('','Department') !!}
                                          <select name="department_id" class="form-control" required>
                                            <option value="">Select Department</option>
                                            @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @if($department->id == $program->department_id) selected="selected" @endif>{{ $department->name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Min duration') !!}
                                          {!! Form::text('min_duration',$program->min_duration,$min_duration) !!}
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Max duration') !!}
                                          {!! Form::text('max_duration',$program->max_duration,$max_duration) !!}
                                        </div>
                                       </div>
                                       <div class="row">
                                        <div class="form-group col-4">
                                          {!! Form::label('','NTA level') !!}
                                          <select name="nta_level_id" class="form-control ss-select-nta-level" required data-min-target="#ss-min-duration-{{ $program->id}}" data-max-target="#ss-max-duration-{{ $program->id }}" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-nta-level') }}" data-award-target="#ss-nta-award-{{ $program->id }}, #ss-nta-award-input-{{ $program->id }}">
                                            <option value="">Select NTA level</option>
                                            @foreach($nta_levels as $level)
                                            <option value="{{ $level->id }}" @if($level->id == $program->nta_level_id) selected="selected" @endif>{{ $level->name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Award') !!}
                                          <select name="award_id" class="form-control" required id="ss-nta-award-{{ $program->id }}" disabled="disabled">
                                            <option value="">Select Award</option>
                                            @foreach($awards as $award)
                                            <option value="{{ $award->id }}" @if($award->id == $program->award_id) selected="selected" @endif>{{ $award->name }}</option>
                                            @endforeach
                                          </select>

                                          {!! Form::input('hidden','award_id',$program->award_id,['id'=>'ss-nta-award-input-'.$program->id]) !!}
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
                      @can('delete-programme')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-program-{{ $program->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                       @endcan

                       <div class="modal fade" id="ss-delete-program-{{ $program->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this program from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/program/'.$program->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                {!! $programs->appends($request->except('page'))->render() !!}
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

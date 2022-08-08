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
            <h1>{{ __('NTA Levels') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('NTA Levels') }}</li>
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
            @can('add-nta-level')
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add NTA Level') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'NTA Level',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $min_duration = [
                     'placeholder'=>'Min duration',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $max_duration = [
                     'placeholder'=>'Max duration',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'settings/nta-level/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','NTA level') !!}
                    {!! Form::text('name',null,$name) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Description') !!}
                    <select name="award_id" class="form-control">
                      <option value="">Select description</option>
                      @foreach($awards as $award)
                      <option value="{{ $award->id }}">{{ $award->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-6">
                    {!! Form::label('','Min duration') !!}
                    {!! Form::text('min_duration',null,$min_duration) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Max duration') !!}
                    {!! Form::text('max_duration',null,$max_duration) !!}
                  </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add NTA Level') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($nta_levels) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of NTA Levels') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Min Duration</th>
                    <th>Max Duration</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($nta_levels as $level)
                  <tr>
                    <td>{{ $level->name }}</td>
                    <td>{{ $level->award->name }}</td>
                    <td>@if(str_contains($level->name,'8')) 1 @else {{ $level->min_duration }} @endif</td>
                    <td>{{ $level->max_duration }}</td>
                    <td>
                      @can('edit-nta-level')
                      <a class="btn btn-info btn-sm" href="#" @if(count($level->programs) == 0) data-toggle="modal" data-target="#ss-edit-level-{{ $level->id }}" @else disabled="disabled" @endif>
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       @endcan

                       <div class="modal fade" id="ss-edit-level-{{ $level->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit level</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                  @php
                                      $name = [
                                         'placeholder'=>'NTA Level',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $min_duration = [
                                         'placeholder'=>'Min duration',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $max_duration = [
                                         'placeholder'=>'Max duration',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];
                                  @endphp
                                {!! Form::open(['url'=>'settings/nta-level/update','class'=>'ss-form-processing']) !!}
                                    <div class="row">
                                      <div class="form-group col-6">
                                        {!! Form::label('','NTA level') !!}
                                        {!! Form::text('name',$level->name,$name) !!}

                                        {!! Form::input('hidden','level_id',$level->id) !!}
                                      </div>
                                      <div class="form-group col-6">
                                        {!! Form::label('','Description') !!}
                                        <select name="award_id" class="form-control">
                                          <option value="">Select description</option>
                                          @foreach($awards as $award)
                                          <option value="{{ $award->id }}" @if($award->id == $level->award_id) selected="selected" @endif>{{ $award->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      </div>
                                      <div class="row">
                                      <div class="form-group col-6">
                                      {!! Form::label('','Min duration') !!}
                                      {!! Form::text('min_duration',$level->min_duration,$min_duration) !!}
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Max duration') !!}
                                      {!! Form::text('max_duration',$level->max_duration,$max_duration) !!}
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
                      @can('delete-nta-level')
                      <a class="btn btn-danger btn-sm" href="#" @if(count($level->programs) == 0) data-toggle="modal" data-target="#ss-delete-level-{{ $level->id }}" @else disabled="disabled" @endif>
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                       @endcan

                       <div class="modal fade" id="ss-delete-level-{{ $level->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this level from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('settings/nta-level/'.$level->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                {!! $nta_levels->render() !!}
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

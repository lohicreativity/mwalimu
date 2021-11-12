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
            <h1>{{ __('Awards') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Awards') }}</li>
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
                <h3 class="card-title">{{ __('Add Award') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Award name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $code = [
                     'placeholder'=>'Code',
                     'class'=>'form-control',
                     'required'=>true
                  ];

              @endphp
              {!! Form::open(['url'=>'academic/award/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-12">
                    {!! Form::label('','Award name') !!}
                    {!! Form::text('name',null,$name) !!}
                  </div>
                 </div>
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Code') !!}
                    {!! Form::text('code',null,$code) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Level of study') !!}
                    <select name="level_id" class="form-control" required>
                      <option value="">Select Level</option>
                      @foreach($levels as $level)
                      <option value="{{ $level->id }}">{{ $level->name }}</option>
                      @endforeach
                    </select>
                  </div>
                 </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->

            @if(count($awards) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('awards') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Code</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($awards as $award)
                  <tr>
                    <td>{{ $award->name }}</td>
                    <td>{{ $award->level->name }}</td>
                    <td>{{ $award->code }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-award-{{ $award->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>

                       <div class="modal fade" id="ss-edit-award-{{ $award->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit award</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

                                {!! Form::open(['url'=>'academic/award/update','class'=>'ss-form-processing']) !!}
                                  <div class="row">
                                    <div class="form-group col-12">
                                      {!! Form::label('','award name') !!}
                                      {!! Form::text('name',$award->name,$name) !!}

                                      {!! Form::input('hidden','award_id',$award->id) !!}
                                    </div>
                                   </div>
                                    <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Code') !!}
                                      {!! Form::text('code',$award->code,$code) !!}
                                    </div>
                                    <div class="form-group col-6">
                                    {!! Form::label('','Level of study') !!}
                                    <select name="level_id" class="form-control" required>
                                      <option value="">Select Level</option>
                                      @foreach($levels as $level)
                                      <option value="{{ $level->id }}" @if($level->id == $award->level_id) selected="selected" @endif>{{ $level->name }}</option>
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
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-award-{{ $award->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-award-{{ $award->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this award from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Abort</button>
                                         <a href="{{ url('academic/award/'.$award->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                {!! $awards->render() !!}
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

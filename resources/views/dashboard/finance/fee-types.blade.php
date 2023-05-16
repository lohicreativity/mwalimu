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
            <h1>{{ __('Fee Types') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Fee Types') }}</li>
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
            @can('add-fee-type')
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Type') }}</h3>
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

                  $gfs_code = [
                     'placeholder'=>'GFS code',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $gl_code = [
                     'placeholder'=>'GL code',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $description = [
                     'placeholder'=>'Description',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'finance/fee-type/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Name') !!}
                    {!! Form::text('name',null,$name) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Description') !!}
                    {!! Form::text('description',null,$description) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Code') !!}
                    {!! Form::text('code',null,$code) !!}
                    
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','GFS code') !!}
                    {!! Form::text('gfs_code',null,$gfs_code) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','GL code') !!}
                    {!! Form::text('gl_code',null,$gl_code) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Payment duration') !!}
                    <select name="duration" class="form-control" required>
                      <option value="">Select Duration</option>
                      @for($i = 10; $i <= 360; $i++)
                        <option value="{{ $i }}">{{ $i }} Days</option>
                        @php
                          $i = $i+9; 
                        @endphp
                      @endfor
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Payment option') !!}
                    <select name="payment_option" class="form-control" required>
                      <option value="">Select Payment Option</option>
                      <option value="0">Full Payment</option>
                      <option value="1">Partial Payment</option>
                      <option value="2">Exact Payment</option>
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Payer category') !!}
                    <select name="payer" class="form-control">
                      <option value="">Select Payer Category</option>
                      <option value="INTERNAL">Internal</option>
                      <option value="EXTERNAL">External</option>
                      <option value="BOTH">Internal and External</option>
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Payment frequency') !!}
                    <select name="when_paid" class="form-control">
                      <option value="">Select Payment Frequency</option>
                      <option value="PAID_ONCE">Once</option>
                      <option value="PAID_ONCE_PER_SEMESTER">Once Per Semester</option>
                      <option value="PAID_MULTIPLE_TIMES">Multiple Times</option>
                    </select>
                  </div>
                </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Fee Type') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($types) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Fee Types') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Name</th>
                    <th>GFS Code</th>
                    <th>GL Code</th>
                    <th>Duration</th>
                    <th>Payment Option</th>
                    <th>Frequency</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($types as $key=>$type)
                  <tr>
                    <td>{{ ($key+1) }}</td>
                    <td>{{ $type->name }}</td>
                    <td>{{ $type->gfs_code }}</td>
                    <td>{{ $type->gl_code }}</td>
                    <td>{{ $type->duration }}</td>
                    <td>
                      @if($type->payment_option == 1)
                        Full Payment
                      @elseif($type->payment_option == 2)
                        Partial Payment
                      @else
                        Exact Payment
                      @endif
                    </td>
                    <td>
                      @if($type->is_paid_once == 1)
                        Paid Once
                      @elseif($type->is_paid_per_semester == 1)
                        Paid Per Semester
                      @else
                        Multiple Times
                      @endif
                    </td>
                    <td>
                      @can('edit-fee-type')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-type-{{ $type->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan
                       <div class="modal fade" id="ss-edit-type-{{ $type->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Fee Type</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                @php
                                      $name = [
                                         'placeholder'=>'Name eg. Application Fee',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $code = [
                                         'placeholder'=>'Code eg. AF',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $gfs_code = [
                                         'placeholder'=>'GFS code',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $gl_code = [
                                         'placeholder'=>'GL code',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];

                                      $description = [
                                         'placeholder'=>'Description',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];
                                  @endphp
                                  {!! Form::open(['url'=>'finance/fee-type/update','class'=>'ss-form-processing']) !!}

                                    <div class="row">
                                        <div class="form-group col-4">
                                          {!! Form::label('','Name') !!}
                                          {!! Form::text('name',$type->name,$name) !!}

                                          {!! Form::input('hidden','fee_type_id',$type->id) !!}
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Description') !!}
                                          {!! Form::text('description',$type->description,$description) !!}
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Code') !!}
                                          {!! Form::text('code',$type->code,$code) !!}
                                        </div>
                                      </div>
                                      <div class="row">
                                        <div class="form-group col-4">
                                          {!! Form::label('','GFS code') !!}
                                          {!! Form::text('gfs_code',$type->gfs_code,$gfs_code) !!}
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','GL code') !!}
                                          {!! Form::text('gl_code',$type->gl_code,$gl_code) !!}
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Payment Duration') !!}
                                          <select name="duration" class="form-control">
                                            <option value="">Select Duration</option>
                                            @for($i = 10; $i <= 360; $i++)
                                              <option value="{{ $i }}" @if($i == $type->duration) selected="selected" @endif>{{ $i }} Days</option>
                                              @php
                                                $i = $i+9; 
                                              @endphp
                                            @endfor
                                          </select>
                                        </div>
                                      </div>
                                      <div class="row">
                                        <div class="form-group col-4">
                                          {!! Form::label('','Payment option') !!}
                                          <select name="payment_option" class="form-control" required>
                                            <option value="">Select Payment Option</option>
                                            <option value="1" @if($type->payment_option == 1) selected="selected" @endif>Full Payment</option>
                                            <option value="2" @if($type->payment_option == 2) selected="selected" @endif>Partial Payment</option>
                                            <option value="3" @if($type->payment_option == 3) selected="selected" @endif>Exact Payment</option>
                                          </select>
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Payer category') !!}
                                          <select name="when_paid" class="form-control">
                                            <option value="">Select Payer Category</option>
                                            <option value="INTERNAL" @if($type->is_internal == 1 && $type->is_external == 0) selected="selected" @endif>Internal</option>
                                            <option value="EXTERNAL" @if($type->is_internal == 0 && $type->is_external == 1) selected="selected" @endif>External</option>
                                            <option value="BOTH" @if($type->is_internal == 1 && $type->is_external == 1) selected="selected" @endif>Internal and External</option>
                                          </select>
                                        </div>
                                        <div class="form-group col-4">
                                          {!! Form::label('','Payment frequency') !!}
                                          <select name="payer" class="form-control">
                                            <option value="">Select Payment Frequency</option>
                                            <option value="PAID_ONCE">Once</option>
                                            <option value="PAID_ONCE_PER_SEMESTER">Once Per Semester</option>
                                            <option value="PAID_MULTIPLE_TIMES">Multiple Times</option>
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
                      
                      @can('delete-fee-type')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-type-{{ $type->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                      @endcan
                       <div class="modal fade" id="ss-delete-type-{{ $type->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this fee type from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('finance/fee-type/'.$type->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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

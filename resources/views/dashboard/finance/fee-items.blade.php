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
            <h1>{{ __('Fee items') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Fee items') }}</li>
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
            @can('add-fee-item')
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add item') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Name',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $description = [
                     'placeholder'=>'Description',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'finance/fee-item/store','class'=>'ss-form-processing']) !!}
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
                    {!! Form::label('','Is mandatory (Registration)') !!}
                    <select name="is_mandatory" class="form-control">
                      <option value="1">Yes</option>
                      <option value="0">No</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Payment order') !!}
                    <select name="payment_order" class="form-control" required>
                      <option value="">Select Payment Order</option>
                      @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                      @endfor
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Fee type') !!}
                    <select name="fee_type_id" class="form-control" required>
                      <option value="">Select Fee Type</option>
                      @foreach($fee_types as $type)
                      <option value="{{ $type->id }}">{{ $type->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))                  
                  <div class="form-group col-3">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($staff->campus_id == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @else
                  <div class="form-group col-3">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $staff->campus_id) selected="selected" @else disabled='disabled' @endif>
                       {{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  @endif
                </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button item="submit" class="btn btn-primary">{{ __('Add Fee Item') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($items) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Fee items') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Name</th>
                    <th>Fee Type</th>
                    <th>Payment Order</th>
                    <th>Is Mandatory</th>
                    <th>Campus</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($items as $key=>$item)
                  <tr>
                    <td>{{ ($key+1) }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->feeType->name }}</td>
                    <td>{{ $item->payment_order }}</td>
                    <td>
                        @if($item->is_mandatory == 1)
                          Yes
                        @else
                          No
                        @endif
                    </td>
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) 
                    <td>{{ $item->campus->name }}</td>
                    @endif
                    <td>
                      <!-- <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-item-{{ $item->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a> -->

                       <div class="modal fade" id="ss-edit-item-{{ $item->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Fee item</h4>
                              <button item="button" class="close" data-dismiss="modal" aria-label="Close">
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

                                      $description = [
                                         'placeholder'=>'Description',
                                         'class'=>'form-control',
                                         'required'=>true
                                      ];
                                  @endphp
                                  {!! Form::open(['url'=>'finance/fee-item/update','class'=>'ss-form-processing']) !!}

                                    <div class="row">
                                      <div class="form-group col-4">
                                        {!! Form::label('','Name') !!}
                                        {!! Form::text('name',$item->name,$name) !!}

                                        {!! Form::input('hidden','fee_item_id',$item->id) !!}
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Description') !!}
                                        {!! Form::text('description',$item->description,$description) !!}
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Is mandatory (Registration)') !!}
                                        <select name="is_mandatory" class="form-control">
                                          <option value="1" @if($item->is_mandatory == 1) selected="selected" @endif>Yes</option>
                                          <option value="0" @if($item->is_mandatory == 0) selected="selected" @endif>No</option>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <div class="form-group col-4">
                                        {!! Form::label('','Payment order') !!}
                                        <select name="payment_order" class="form-control" required>
                                          <option value="">Select Payment Order</option>
                                          @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" @if($i == $item->payment_order) selected="selected" @endif>{{ $i }}</option>
                                          @endfor
                                        </select>
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Fee type') !!}
                                        <select name="fee_type_id" class="form-control" required>
                                          <option value="">Select Fee Type</option>
                                          @foreach($fee_types as $type)
                                          <option value="{{ $type->id }}" @if($type->id == $item->fee_type_id) selected="selected" @endif>{{ $type->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                      <div class="ss-form-actions">
                                       <button item="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                      </div>
                                {!! Form::close() !!}

                            </div>
                            <div class="modal-footer justify-content-between">
                              <button item="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
                     
                     @can('delete-fee-item')
                      <a class="btn btn-danger btn-sm" href="#" @if(App\Utils\Util::arrayContains($item->name,['MNMASO','Medical Examination','Caution Money','Practical Training','TCU','NACTE','Identity Card','Registration','Late Registration'])) disabled="disabled" @else data-toggle="modal" data-target="#ss-delete-item-{{ $item->id }}" @endif>
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                      @endcan
                       <div class="modal fade" id="ss-delete-item-{{ $item->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button item="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to delete this fee item from the list?</p>
                                       <div class="ss-form-controls">
                                         <button item="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('finance/fee-item/'.$item->id.'/destroy') }}" class="btn btn-danger">Delete</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button item="button" class="btn btn-default" data-dismiss="modal">Close</button>
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

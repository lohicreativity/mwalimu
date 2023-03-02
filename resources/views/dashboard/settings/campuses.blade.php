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
            <h1>{{ __('Campuses') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Campuses') }}</li>
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
            
            @can('add-campus')
            <!-- general form elements -->
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Campus') }}</h3>
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

                  $phone = [
                     'placeholder'=>'+255754505050',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $email = [
                     'placeholder'=>'Email',
                     'class'=>'form-control',
                     'required'=>true
                  ];

                  $street = [
                     'placeholder'=>'Street',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'settings/campus/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-8">
                    {!! Form::label('','Name') !!}
                    {!! Form::text('',null,$name) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Abbreviation') !!}
                    {!! Form::text('',null,$abbreviation) !!}
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Phone') !!}
                    {!! Form::text('',null,$phone) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Email') !!}
                    {!! Form::email('',null,$email) !!}
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Region') !!}
                    <select name="region_id" class="form-control ss-select-tags" required id="ss-select-regions" data-target="#ss-select-districts" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-districts') }}">
                      <option value="">Select Region</option>
                      @foreach($regions as $region)
                      <option value="{{ $region->id }}">{{ $region->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','District') !!}
                    <select name="district_id" class="form-control ss-select-tags" required id="ss-select-districts" data-target="#ss-select-wards" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-wards') }}">
                      <option value="">Select District</option>
                      @foreach($districts as $district)
                      <option value="{{ $district->id }}">{{ $district->name }}</option>
                      @endforeach
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Ward') !!}
                    <select name="ward_id" class="form-control ss-select-tags" required id="ss-select-wards" data-token="{{ session()->token() }}">
                      <option value="">Select Ward</option>
                      @foreach($wards as $ward)
                      <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Street') !!}
                    {!! Form::text('street',null,$street) !!}
                  </div>
                 </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Campus') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if(count($campuses) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Campuses') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Abbreviation</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($campuses as $campus)
                  <tr>
                    <td>{{ $campus->name }}</td>
                    <td>{{ $campus->abbreviation }}</td>
                    <td>
                      @can('view-campus-programme')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-view-campus-{{ $campus->id }}">
                              <i class="fas fa-list-alt">
                              </i>
                              View Programmes
                       </a>
                      @endcan

                      <div class="modal fade" id="ss-view-campus-{{ $campus->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Programmes - {{ $campus->name }}</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @if(count($campus->campusPrograms) != 0)
                              <table id="example2" class="table table-bordered table-hover">
                                  <thead>
                                  <tr>
                                    <th>Programme</th>
                                    <th>Code</th>
                                    <th>Regulator Code</th>
                                  </tr>
                                  </thead>
                                  <tbody>
                                  @foreach($campus->campusPrograms as $program)
                                  <tr>
                                    <td>{{ $program->program->name }}</td>
                                    <th>{{ $program->program->code }}</th>
                                    <th>{{ $program->regulator_code }}</th>

                                    </td>
                                  </tr>
                                  @endforeach
                                  
                                  </tbody>
                                </table>
                                @else
                                  <h3>No Programme Assigned.</h3>
                                @endif
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

                       <div class="modal fade" id="ss-view-campus-{{ $campus->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Programmes</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @if(count($campus->campusPrograms) != 0)
                              <table id="example2" class="table table-bordered table-hover">
                                  <thead>
                                  <tr>
                                    <th>Programme</th>
                                    <th>Code</th>
                                    <th>Regulator Code</th>
                                  </tr>
                                  </thead>
                                  <tbody>
                                  @foreach($campus->campusPrograms as $program)
                                  <tr>
                                    <td>{{ $program->program->name }}</td>
                                    <th>{{ $program->program->code }}</th>
                                    <th>{{ $program->regulator_code }}</th>

                                    </td>
                                  </tr>
                                  @endforeach
                                  
                                  </tbody>
                                </table>
                                @else
                                  <h3>No Programme Assigned.</h3>
                                @endif
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
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-view-campus-{{ $campus->id }}">
                              <i class="fas fa-list-alt">
                              </i>
                              View Programmes
                       </a>
					   {{--
                      @can('assign-campus-programme')
                       @if(Auth::user()->hasRole('administrator'))
                       <a class="btn btn-info btn-sm" href="{{ url('academic/campus/'.$campus->id.'/campus-programs') }}">
                              <i class="fas fa-plus">
                              </i>
                              Assign Programmes
                       </a>
                       @else
                       <a class="btn btn-info btn-sm" href="{{ url('academic/campus/'.$campus->id.'/campus-programs') }}" >
                              <i class="fas fa-plus">
                              </i>
                              Assign Programmes
                       </a>
                       @endif
                      @endcan
					   --}}
                      @can('edit-campus')
                      @if(Auth::user()->hasRole('administrator'))
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-campus-{{ $campus->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       @else
                       <a class="btn btn-info btn-sm" href="#" @if($staff->campus_id == $campus->id) data-toggle="modal" data-target="#ss-edit-campus-{{ $campus->id }}" @else disabled="disabled" @endif>
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       @endif
                       @endcan

                       <div class="modal fade" id="ss-edit-campus-{{ $campus->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">{{ __('Edit Campus') }}</h4>
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

                                  $phone = [
                                     'placeholder'=>'+255754505050',
                                     'class'=>'form-control',
                                     'required'=>true
                                  ];

                                  $email = [
                                     'placeholder'=>'Email',
                                     'class'=>'form-control',
                                     'required'=>true
                                  ];

                                  $street = [
                                     'placeholder'=>'Street',
                                     'class'=>'form-control',
                                     'required'=>true
                                  ];
                              @endphp

                                {!! Form::open(['url'=>'settings/campus/update','class'=>'ss-form-processing']) !!}

                                     <div class="row">
                                      <div class="form-group col-8">
                                        {!! Form::label('','Name') !!}
                                        {!! Form::text('name',$campus->name,$name) !!}

                                        {!! Form::input('hidden','campus_id',$campus->id) !!}
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Abbreviation') !!}
                                        {!! Form::text('abbreviation',$campus->abbreviation,$abbreviation) !!}
                                      </div>
                                     </div>
                                     <div class="row">
                                      <div class="form-group col-6">
                                        {!! Form::label('','Phone') !!}
                                        {!! Form::text('phone',$campus->phone,$phone) !!}
                                      </div>
                                      <div class="form-group col-6">
                                        {!! Form::label('','Email') !!}
                                        {!! Form::email('email',$campus->email,$email) !!}
                                      </div>
                                     </div>
                                     <div class="row">
                                      <div class="form-group col-6">
                                        {!! Form::label('','Region') !!}
                                        <select name="region_id" class="form-control ss-select-regions" required id="ss-select-regions-{{ $campus->id }}" data-target="#ss-select-districts-{{ $campus->id }}" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-districts') }}">
                                          <option value="">Select Region</option>
                                          @foreach($regions as $region)
                                          <option value="{{ $region->id }}" @if($campus->region_id == $region->id) selected="selected" @endif>{{ $region->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-6">
                                        {!! Form::label('','District') !!}
                                        <select name="district_id" class="form-control ss-select-districts" required id="ss-select-districts-{{ $campus->id }}" data-target="#ss-select-wards-{{ $campus->id }}" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-wards') }}">
                                          <option value="">Select District</option>
                                          @foreach($districts as $district)
                                          <option value="{{ $district->id }}" @if($campus->district_id == $district->id) selected="selected" @endif>{{ $district->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                     </div>
                                     <div class="row">
                                      <div class="form-group col-6">
                                        {!! Form::label('','Ward') !!}
                                        <select name="ward_id" class="form-control ss-select-wards" required id="ss-select-wards-{{ $campus->id }}" data-token="{{ session()->token() }}">
                                          <option value="">Select Ward</option>
                                          @foreach($wards as $ward)
                                          <option value="{{ $ward->id }}" @if($campus->ward_id == $ward->id) selected="selected" @endif>{{ $ward->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-6">
                                        {!! Form::label('','Street') !!}
                                        {!! Form::text('street',$campus->street,$street) !!}
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
                      @can('delete-campus')
                      @if(Auth::user()->hasRole('administrator'))
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-campus-{{ $campus->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                      @else
                       <a class="btn btn-danger btn-sm" href="#" @if($staff->campus_id == $campus->id) data-toggle="modal" data-target="#ss-delete-campus-{{ $campus->id }}" @else disabled="disabled" @endif>
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                      @endif
                      @endcan

                       <div class="modal fade" id="ss-delete-campus-{{ $campus->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this campus from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('settings/campus/'.$campus->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                {!! $campuses->render() !!}
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

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
            <h1>{{ __('Application Batches') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Application Batches') }}</li>
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
            @if(count($batches) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Application Batches') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>SN</th>
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) 
                      <th>Campus</th>
                    @endif
                    <th>Application Window</th>
                    <th>Intake</th>
                    <th>Programme Level</th>
                    <th>Batch#</th>
                    <th>Begin Date</th>
                    <th>End Date</th>
                    @if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator'))                    
                    <th>Actions</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>
                  @php $counter = 1 @endphp

                  @foreach($batches as $batch)
                  @foreach($batch as $ba)
                  <tr>
                    <td>{{ ($counter++) }}</td>
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) 
                      
                   
                    <td> @foreach($windows as $window)
                          @if($window->id == $ba->application_window_id)
                            @foreach($campuses as $campus)
                                @if($window->campus_id == $campus->id){{ $campus->name }} @break @endif
                            @endforeach
                            @break
                          @endif 
                          @endforeach
                    </td>
                    @endif
                    @foreach($windows as $window)
                        @if($window->id == $ba->application_window_id) 
                            <td> {{ $window->begin_date }} - {{ $window->end_date }} </td>
                            <td> @foreach($intakes as $intake)
                                    @if($intake->id == $window->intake_id) {{ $intake->name }} @break @endif
                                 @endforeach
                            </td>
                            @break
                        @endif
                    @endforeach
                    <td> @foreach($awards as $award) 
                            @if($ba->program_level_id == $award->id) {{ $award->name }} @break @endif
                        @endforeach
                    </td>
                    <td> {{ $ba->batch_no }} </td>
                    <td> {{ $ba->begin_date }} </td>
                    <td> {{ $ba->end_date }} </td>
                    @if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator'))                    
                    <td>
                      @php
                      $have_results = 0;
                      foreach($batch_ids as $batch){
                        if($batch->batch_id == $ba->id){
                          $have_results = 1;

                        }
                      }

                    @endphp
                      @if($ba->selection_released == 1)
                      <a class="btn btn-danger btn-sm" href="{{ url('application/application-batches-selection?status=0&batch_id='.$ba->id) }}">
                             <i class="fas fa-ban">
                             </i>
                             Hide Selections
                      </a>
                      <a class="btn btn-info btn-sm" href="{{ url('application/update-batches-selection?batch_id='.$ba->id) }}">
                             <i class="fas fa-check-circle">
                             </i>
                             Update Selections
                      </a>
                     @else
                      <a class="btn btn-info btn-sm" @if($have_results == 1) href="{{ url('application/application-batches-selection?status=1&batch_id='.$ba->id) }}" @else disabled="disabled" @endif>
                             <i class="fas fa-check-circle">
                             </i>
                             Show Selections
                      </a>
                      <a class="btn btn-info btn-sm disabled" href="{{ url('application/update-batches-selection?batch_id='.$ba->id) }}">
                             <i class="fas fa-check-circle">
                             </i>
                             Update Selections
                      </a>
                     @endif
 
                      @can('delete-application-window')
                      @if(($ba->where('program_level_id', $ba->program_level_id )->max('batch_no')) == $ba->batch_no)
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-batch-{{ $ba->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                      </a>
                      @else
                      <a class="btn btn-info btn-sm disabled" href="#" data-toggle="modal" data-target="#ss-edit-batch-{{ $ba->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                      </a>
                      @endif
                      @endcan

                      <div class="modal fade" id="ss-edit-batch-{{ $ba->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"> Edit Batch</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                            {!! Form::open(['url'=>'application/application-batches/update','class'=>'ss-form-processing']) !!} 
                                  @php                
                                    $begin_date = [
                                        'placeholder'=>$ba->begin_date,
                                        'value'=>$ba->begin_date,
                                        'class'=>'form-control ss-datepicker',
                                        'required'=>true
                                    ];
                                    $end_date = [
                                        'placeholder'=>$ba->end_date,
                                        'value'=>$ba->end_date,
                                        'class'=>'form-control ss-datepicker',
                                        'required'=>true
                                    ];
                                 @endphp
                              <div class='row'>
                                <div class='col-6'>
                                  {!! Form::label('','Begin Date') !!}
                                  {!! Form::text('begin_date',App\Utils\DateMaker::toStandardDate($ba->begin_date),$begin_date) !!}
                                </div>
                                <div class='col-6'>
                                  {!! Form::label('','End Date') !!}
                                  {!! Form::text('end_date',App\Utils\DateMaker::toStandardDate($ba->end_date),$end_date) !!}
                                  {!! Form::input('hidden','campus_id',$staff->campus_id) !!}
                                  {!! Form::input('hidden','batch_id',$ba->id) !!}
                                  {!! Form::input('hidden','program_level_id',$ba->program_level_id) !!}
                                </div>
                              </div>
                            </div>
                            <div class="card-footer">
                              <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                            </div>
                            {!! Form::close() !!}
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
                    @endif
                  </tr>
                  @endforeach
                  @endforeach
                  
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @endif
 
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

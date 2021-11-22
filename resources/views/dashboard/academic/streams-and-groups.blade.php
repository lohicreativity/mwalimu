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
            <h1>{{ __('Campus Program Streams') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Campus Program Streams') }}</li>
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

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Select Study Academic Year') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/streams','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                     
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->


          @if(count($campus_programs) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Select Study Academic Year') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                   <thead>
                      <tr>
                        <th>Program</th>
                        <th>Streams</th>
                        <th>Groups</th>
                      </tr>
                   </thead>
                   <tbody>
                    @foreach($campus_programs as $cp)

                      @for($i = 1; $i <= $cp->program->min_duration; $i++)
                          @php
                            $stream_created = false;
                          @endphp
                      <tr>
                        <td>{{ $cp->program->name }}_YR_{{ $i }}
                         @foreach($study_academic_year->streams as $stream)
                            @if($stream->campus_program_id == $cp->id && $stream->year_of_study == $i)
                            @php
                              $stream_created = true;
                            @endphp
                            @endif
                          @endforeach
                          @if($stream_created)
                          <p><a href="{{ url('academic/stream-reset?year_of_study='.$i.'&campus_program_id='.$cp->id) }}">{{ __('Reset Streams') }}</a></p>
                          @endif
                        </td>
                        <td>
                          
                          @foreach($study_academic_year->streams as $stream)
                            @if($stream->campus_program_id == $cp->id && $stream->year_of_study == $i)
                            <p>Stream - {{ $stream->name }}</p>
                            @php
                              $stream_created = true;
                            @endphp
                            @endif
                          @endforeach
                            
                          @if(!$stream_created)
                          <a href="#" data-toggle="modal" data-target="#ss-add-stream-{{ $i }}-{{ $cp->id }}">Create Streams</a></a>
                          @endif

                          <div class="modal fade" id="ss-add-stream-{{ $i }}-{{ $cp->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Create Streams</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @php
                                    $name = [
                                       'placeholder'=>'Campus name',
                                       'class'=>'form-control',
                                       'required'=>true
                                    ];
                                @endphp
                                {!! Form::open(['url'=>'academic/stream-component/store','class'=>'ss-form-processing']) !!}

                                    <div class="form-group">
                                      {!! Form::label('','Number of streams') !!}
                                      <select name="number_of_streams" class="form-control" required>
                                        @for($j = 1; $j <= 10; $j++)
                                         <option value="{{ $j }}">{{ $j }}</option>
                                        @endfor
                                      </select>

                                      {!! Form::input('hidden','campus_program_id',$cp->id) !!}
                                      {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                      {!! Form::input('hidden','year_of_study',$i) !!}
                                    </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Create Streams') }}</button>
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
                        </td>
                        <td></td>
                      </tr>
                      @endfor
                    @endforeach
                   </tbody>
                 </table>
              </div>
            </div>
            <!-- /.card -->

            @else

           
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Campus Program Streams Created') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
              </div>
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

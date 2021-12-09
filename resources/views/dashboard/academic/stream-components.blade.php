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
            <h1>{{ __('Add Students Per Stream') }} - {{ $component->number_of_students }} {{ __('Students') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Add Students Per Stream') }}</li>
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
              </div><!-- /.card-header -->
              <!-- form start -->
              @if($component)
              @php
                  $name = [
                     'placeholder'=>'Name',
                     'class'=>'form-control',
                     'disabled'=>true,
                  ];

                  $students_number = [
                     'placeholder'=>'Number of students',
                     'class'=>'form-control',
                     'min'=>0,
                     'steps'=>'any',
                     'required'=>true
                  ];


              @endphp
              {!! Form::open(['url'=>'academic/stream/store','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  @for($i = 1; $i <= $component->number_of_streams; $i++)
                  @php
                     switch ($i) {
                          case 1:
                                  $str = 'A';
                                  break;

                          case 2:
                                  $str = 'B';
                                  break;

                          case 3:
                                  $str = 'C';
                                  break;

                          case 4:
                                  $str = 'D';
                                  break;

                          case 5:
                                  $str = 'E';
                                  break;

                          case 6:
                                  $str = 'F';
                                  break;

                          case 7:
                                  $str = 'G';
                                  break;

                          case 8:
                                  $str = 'H';
                                  break;

                          case 9:
                                  $str = 'I';
                                  break;

                          case 10:
                                  $str = 'J';
                                  break;
                          
                          default:
                                  $str = 'M';
                                  break;
                  }
                  @endphp
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Name of stream ') !!}
                    {!! Form::text('name_component_'.$component->id,'Stream '.$str,$name) !!}

                    {!! Form::input('hidden','name_'.$i.'_component_'.$component->id,$str) !!}
                    {!! Form::input('hidden','stream_component_id',$component->id) !!}
                    {!! Form::input('hidden','campus_program_id',$component->campus_program_id) !!}
                    {!! Form::input('hidden','study_academic_year_id',$component->study_academic_year_id) !!}
                    {!! Form::input('hidden','year_of_study',$i) !!}
                    
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Number of students') !!}
                    {!! Form::input('number','number_'.$i.'_component_'.$component->id,null,$students_number) !!}
                  </div>
                  </div>
                  @endfor
                </div>
                <!-- /.card-body -->
                
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Streams') }}</button>
                </div>
              {!! Form::close() !!}

              @endif
            </div>
            <!-- /.card -->

            
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

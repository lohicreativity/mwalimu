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
            <h1>{{ __('Edit CA Results') }} - {{ $module_assignment->module->name }} - {{ $module_assignment->module->code }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Edit CA Results') }}</li>
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
              <div class="card-header p-2">
                 <h3>Edit CA Results - {{ $student->surname}}, {{ (ucwords(strtolower($student->first_name))) }} {{ substr($student->middle_name,0,1) }} | {{ $student->registration_number}}</h3>
           
                </div><!-- /.card-header -->
              <!-- form start -->
              @php
                  $name = [
                     'placeholder'=>'Name',
                     'class'=>'form-control',
                     'disabled'=>true,
                  ];

                  $marks = [
                     'placeholder'=>'Marks',
                     'class'=>'form-control',
                     'min'=>0,
                     'steps'=>'any',
                     'required'=>true
                  ];

                  $planIds = [];
              @endphp
              {!! Form::open(['url'=>'academic/results/update-course-work-results','class'=>'ss-form-processing']) !!}
                <div class="card-body">
{{ $assessment_plans }}
                  @foreach($assessment_plans as $plan)
                    @foreach($results as $result)
                      @if($result->assessment_plan_id == $plan->id)
                        @php
                          $planIds[] = $plan->id;
                        @endphp
                        <div class="row">
                        <div class="form-group col-6">
                          {!! Form::label('',$plan->name) !!}
                          {!! Form::text('plan_name_'.$plan->id,$plan->name,$name) !!}
                          
                        </div>
                        <div class="form-group col-6">
                          {!! Form::label('','Marks') !!}
                          {!! Form::text('plan_'.$plan->id.'_score',$result->score,$marks) !!}
                        </div>
                        </div>
                      @endif
                    @endforeach
                  @endforeach
                   @foreach($assessment_plans as $plan)
                      @if(!in_array($plan->id,$planIds))
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('',$plan->name) !!}
                    {!! Form::text('plan_name_'.$plan->id.'_score',$plan->name,$name) !!}

                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Marks') !!}
                    {!! Form::text('plan_'.$plan->id.'_score',null,$marks) !!}
                  </div>
                  </div>
                    @endif
                   @endforeach
                </div>
                <!-- /.card-body -->
                {!! Form::input('hidden','student_id',$student->id) !!}
                {!! Form::input('hidden','examination_result_id',$exam_result->id) !!}
                {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}
                {!! Form::input('hidden','redirect_url',$redirect_url) !!}
                {!! Form::input('hidden','ac_yr_id',$ac_yr_id) !!}
                {!! Form::input('hidden','year_of_study',$year_of_study) !!}
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
              {!! Form::close() !!}

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

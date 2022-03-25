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
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Entry Requirements</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Entry Requirements</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
           <div class="col-12">
 
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Select Application Window') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  {!! Form::open(['url'=>'application/entry-requirements','class'=>'ss-form-processing','method'=>'GET']) !!}
                    <div class="row">
                    <div class="form-group col-12">
                     <select name="application_window_id" class="form-control" required>
                        <option value="">Select Application Window</option>
                        @foreach($application_windows as $window)
                        <option value="{{ $window->id }}">{{ $window->begin_date }} - {{ $window->end_date }} </option>
                        @endforeach
                     </select>
                   </div>
                 </div>
                   <div class="ss-form-actions">
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                   </div>
 
                  {!! Form::close() !!}
               </div>
             </div>
             <!-- /.card -->
             
             @if($application_window)
             <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Add Entry Requirement') }}</h3>

               </div>
               <!-- /.card-header -->
               <ul class="nav nav-tabs" id="myList" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="list" href="#ss-certificate" role="tab">Certificate</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="list" href="#ss-diploma" role="tab">Diploma</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="list" href="#ss-degree" role="tab">Degree</a></li>
              </ul>

               <div class="tab-content">
                 <div class="tab-pane active" id="ss-certificate" role="tabpanel">
               @php
                $equivalent_gpa = [
                   'placeholder'=>'Equivalent GPA',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $equivalent_pass_subjects = [
                   'placeholder'=>'Equivalent Pass Subjects',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $equivalent_average_grade = [
                   'placeholder'=>'Equivalent Average Grade',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $open_equivalent_gpa = [
                   'placeholder'=>'Open Equivalent GPA',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $open_equivalent_pass_subjects = [
                   'placeholder'=>'Open Equivalent Pass Subjects',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $open_equivalent_average_grade = [
                   'placeholder'=>'Open Equivalent Average Grade',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $principle_pass_points = [
                   'placeholder'=>'Principle Pass Points',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $principle_pass_subjects = [
                   'placeholder'=>'Principle Pass Subjects',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $pass_subjects = [
                   'placeholder'=>'Pass Subjects',
                   'class'=>'form-control'
                ];

                $pass_grade = [
                   'placeholder'=>'Pass Grade',
                   'class'=>'form-control'
                ];

                $award_level = [
                   'placeholder'=>'Award Level',
                   'class'=>'form-control'
                ];

                $award_division = [
                   'placeholder'=>'Award Division',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $exclude_subjects = [
                   'placeholder'=>'Exclude Subjects',
                   'class'=>'form-control'
                ];

                $must_subjects = [
                   'placeholder'=>'Must Subjects',
                   'class'=>'form-control'
                ];

                $subsidiary_subjects = [
                   'placeholder'=>'Subsidiary Subjects',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $principle_subjects = [
                   'placeholder'=>'Principle Subjects',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $max_capacity = [
                   'placeholder'=>'Max Capacity',
                   'class'=>'form-control'
                ];
             @endphp

               {!! Form::open(['url'=>'application/entry-requirement/store','class'=>'ss-form-processing']) !!}
               <div class="card-body">
                 
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_ids[]" class="form-control ss-select-tags" required multiple="multiple">
                      <option value="">Select Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent GPA') !!}
                    {!! Form::text('equivalent_gpa',null,$equivalent_gpa) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Pass Subjects') !!}
                    {!! Form::text('equivalent_pass_subjects',null,$equivalent_pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Average Grade') !!}
                    {!! Form::text('equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                 </div>
                 <div class="row">
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent GPA') !!}
                    {!! Form::text('open_equivalent_gpa',null,$open_equivalent_gpa) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Pass Subjects') !!}
                    {!! Form::text('open_equivalent_pass_subjects',null,$open_equivalent_pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Average Grade') !!}
                    {!! Form::text('open_equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Points') !!}
                    {!! Form::text('principle_pass_points',null,$principle_pass_points) !!}
                  </div>
                 </div>
                 <div class="row">
                   <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Subjects') !!}
                    {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Number of Pass Subjects') !!}
                    {!! Form::text('pass_subjects',null,$pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Pass Grade') !!}
                    <select name="pass_grade" class="form-control">
                       <option value="">Select Pass Grade</option>
                       <option value="A">A</option>
                       <option value="B">B</option>
                       <option value="C">C</option>
                       <option value="D">D</option>
                       <option value="E">E</option>
                       <option value="F">F</option>
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','NVA Level') !!}
                    <select name="award_level" class="form-control">
                       <option value="">Select NVA Level</option>
                       <option value="I">I</option>
                       <option value="II">II</option>
                       <option value="III">III</option>
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Award Division') !!}
                    {!! Form::text('award_division',null,$award_division) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Exclude Subjects') !!}
                    <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Must Subjects') !!}
                    <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Subsidiary Subjects') !!}
                    <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple" disabled="disabled">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Subjects') !!}
                    <select name="principle_subjects[]" class="form-control ss-select-tags" multiple="multiple" disabled="disabled">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                 </div>
             </div>
             
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                </div>
              {!! Form::close() !!}
            </div><!-- /tabpane -->
            <div class="tab-pane" id="ss-diploma" role="tabpanel">
               @php
                $equivalent_gpa = [
                   'placeholder'=>'Equivalent GPA',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $equivalent_pass_subjects = [
                   'placeholder'=>'Equivalent Pass Subjects',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $equivalent_average_grade = [
                   'placeholder'=>'Equivalent Average Grade',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $open_equivalent_gpa = [
                   'placeholder'=>'Open Equivalent GPA',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $open_equivalent_pass_subjects = [
                   'placeholder'=>'Open Equivalent Pass Subjects',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $open_equivalent_average_grade = [
                   'placeholder'=>'Open Equivalent Average Grade',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $principle_pass_points = [
                   'placeholder'=>'Principle Pass Points',
                   'class'=>'form-control'
                ];

                $principle_pass_subjects = [
                   'placeholder'=>'Principle Pass Subjects',
                   'class'=>'form-control'
                ];

                $pass_subjects = [
                   'placeholder'=>'Pass Subjects',
                   'class'=>'form-control'
                ];

                $pass_grade = [
                   'placeholder'=>'Pass Grade',
                   'class'=>'form-control'
                ];

                $award_level = [
                   'placeholder'=>'Award Level',
                   'class'=>'form-control'
                ];

                $award_division = [
                   'placeholder'=>'Award Division',
                   'class'=>'form-control'
                ];

                $exclude_subjects = [
                   'placeholder'=>'Exclude Subjects',
                   'class'=>'form-control'
                ];

                $must_subjects = [
                   'placeholder'=>'Must Subjects',
                   'class'=>'form-control'
                ];

                $subsidiary_subjects = [
                   'placeholder'=>'Subsidiary Subjects',
                   'class'=>'form-control'
                ];

                $principle_subjects = [
                   'placeholder'=>'Principle Subjects',
                   'class'=>'form-control'
                ];

                $max_capacity = [
                   'placeholder'=>'Max Capacity',
                   'class'=>'form-control'
                ];
             @endphp

               {!! Form::open(['url'=>'application/entry-requirement/store','class'=>'ss-form-processing']) !!}
               <div class="card-body">
                 
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_ids[]" class="form-control ss-select-tags" required multiple="multiple">
                      <option value="">Select Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent GPA') !!}
                    {!! Form::text('equivalent_gpa',null,$equivalent_gpa) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Pass Subjects') !!}
                    {!! Form::text('equivalent_pass_subjects',null,$equivalent_pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Average Grade') !!}
                    {!! Form::text('equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                 </div>
                 <div class="row">
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent GPA') !!}
                    {!! Form::text('open_equivalent_gpa',null,$open_equivalent_gpa) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Pass Subjects') !!}
                    {!! Form::text('open_equivalent_pass_subjects',null,$open_equivalent_pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Average Grade') !!}
                    {!! Form::text('open_equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Points') !!}
                    {!! Form::text('principle_pass_points',null,$principle_pass_points) !!}
                  </div>
                 </div>
                 <div class="row">
                   <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Subjects') !!}
                    {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Pass Subjects') !!}
                    {!! Form::text('pass_subjects',null,$pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Pass Grade') !!}
                    <select name="pass_grade" class="form-control">
                       <option value="">Select Pass Grade</option>
                       <option value="A">A</option>
                       <option value="B">B</option>
                       <option value="C">C</option>
                       <option value="D">D</option>
                       <option value="E">E</option>
                       <option value="F">F</option>
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','NVA Level') !!}
                    <select name="award_level" class="form-control">
                       <option value="">Select NVA Level</option>
                       <option value="I">I</option>
                       <option value="II">II</option>
                       <option value="III">III</option>
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Award Division') !!}
                    {!! Form::text('award_division',null,$award_division) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Exclude Subjects') !!}
                    <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Must Subjects') !!}
                    <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Subsidiary Subjects') !!}
                    <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Subjects') !!}
                    <select name="principle_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                 </div>
             </div>
             
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                </div>
              {!! Form::close() !!}
            </div><!-- /tabpane -->
            <div class="tab-pane" id="ss-degree" role="tabpanel">
               @php
                $equivalent_gpa = [
                   'placeholder'=>'Equivalent GPA',
                   'class'=>'form-control'
                ];

                $equivalent_pass_subjects = [
                   'placeholder'=>'Equivalent Pass Subjects',
                   'class'=>'form-control'
                ];

                $equivalent_average_grade = [
                   'placeholder'=>'Equivalent Average Grade',
                   'class'=>'form-control'
                ];

                $open_equivalent_gpa = [
                   'placeholder'=>'Open Equivalent GPA',
                   'class'=>'form-control'
                ];

                $open_equivalent_pass_subjects = [
                   'placeholder'=>'Open Equivalent Pass Subjects',
                   'class'=>'form-control'
                ];

                $open_equivalent_average_grade = [
                   'placeholder'=>'Open Equivalent Average Grade',
                   'class'=>'form-control'
                ];

                $principle_pass_points = [
                   'placeholder'=>'Principle Pass Points',
                   'class'=>'form-control'
                ];

                $principle_pass_subjects = [
                   'placeholder'=>'Principle Pass Subjects',
                   'class'=>'form-control'
                ];

                $pass_subjects = [
                   'placeholder'=>'Pass Subjects',
                   'class'=>'form-control'
                ];

                $pass_grade = [
                   'placeholder'=>'Pass Grade',
                   'class'=>'form-control'
                ];

                $award_level = [
                   'placeholder'=>'Award Level',
                   'class'=>'form-control'
                ];

                $award_division = [
                   'placeholder'=>'Award Division',
                   'class'=>'form-control'
                ];

                $exclude_subjects = [
                   'placeholder'=>'Exclude Subjects',
                   'class'=>'form-control'
                ];

                $must_subjects = [
                   'placeholder'=>'Must Subjects',
                   'class'=>'form-control'
                ];

                $subsidiary_subjects = [
                   'placeholder'=>'Subsidiary Subjects',
                   'class'=>'form-control'
                ];

                $principle_subjects = [
                   'placeholder'=>'Principle Subjects',
                   'class'=>'form-control'
                ];

                $max_capacity = [
                   'placeholder'=>'Max Capacity',
                   'class'=>'form-control'
                ];
             @endphp

               {!! Form::open(['url'=>'application/entry-requirement/store','class'=>'ss-form-processing']) !!}
               <div class="card-body">
                 
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_ids[]" class="form-control ss-select-tags" required multiple="multiple">
                      <option value="">Select Programme</option>
                      @foreach($campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent GPA') !!}
                    {!! Form::text('equivalent_gpa',null,$equivalent_gpa) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Pass Subjects') !!}
                    {!! Form::text('equivalent_pass_subjects',null,$equivalent_pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Average Grade') !!}
                    {!! Form::text('equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                 </div>
                 <div class="row">
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent GPA') !!}
                    {!! Form::text('open_equivalent_gpa',null,$open_equivalent_gpa) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Pass Subjects') !!}
                    {!! Form::text('open_equivalent_pass_subjects',null,$open_equivalent_pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Average Grade') !!}
                    {!! Form::text('open_equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Points') !!}
                    {!! Form::text('principle_pass_points',null,$principle_pass_points) !!}
                  </div>
                 </div>
                 <div class="row">
                   <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Subjects') !!}
                    {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Pass Subjects') !!}
                    {!! Form::text('pass_subjects',null,$pass_subjects) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Pass Grade') !!}
                    <select name="pass_grade" class="form-control">
                       <option value="">Select Pass Grade</option>
                       <option value="A">A</option>
                       <option value="B">B</option>
                       <option value="C">C</option>
                       <option value="D">D</option>
                       <option value="E">E</option>
                       <option value="F">F</option>
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Award Level') !!}
                    {!! Form::text('award_level',null,$award_level) !!}
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Award Division') !!}
                    {!! Form::text('award_division',null,$award_division) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Exclude Subjects') !!}
                    <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Must Subjects') !!}
                    <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Subsidiary Subjects') !!}
                    <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Subjects') !!}
                    <select name="principle_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                 </div>
             </div>
             
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                </div>
              {!! Form::close() !!}
            </div><!-- /tabpane -->
          </div>
            </div>
            
            @if(count($entry_requirements) != 0)
            <div class="card">
               <div class="card-header">
                 <h3 class="card-title">{{ __('Entry Requirements') }}</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Pass Subjects</th>
                    <th>Pass Grade</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($entry_requirements as $requirement)
                  <tr>
                    <td>{{ $requirement->campusProgram->program->name }}</td>
                    <td>{{ $requirement->pass_subjects }}</td>
                    <td>{{ $requirement->pass_grade }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-requirement-{{ $requirement->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>

                       <div class="modal fade" id="ss-edit-requirement-{{ $requirement->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Entry Requirement</h4>
                              <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                @php
                                $equivalent_gpa = [
                                   'placeholder'=>'Equivalent GPA',
                                   'class'=>'form-control'
                                ];

                                $equivalent_pass_subjects = [
                                   'placeholder'=>'Equivalent Pass Subjects',
                                   'class'=>'form-control'
                                ];

                                $equivalent_average_grade = [
                                   'placeholder'=>'Equivalent Average Grade',
                                   'class'=>'form-control'
                                ];

                                $open_equivalent_gpa = [
                                   'placeholder'=>'Open Equivalent GPA',
                                   'class'=>'form-control'
                                ];

                                $open_equivalent_pass_subjects = [
                                   'placeholder'=>'Open Equivalent Pass Subjects',
                                   'class'=>'form-control'
                                ];

                                $open_equivalent_average_grade = [
                                   'placeholder'=>'Open Equivalent Average Grade',
                                   'class'=>'form-control'
                                ];

                                $principle_pass_points = [
                                   'placeholder'=>'Principle Pass Points',
                                   'class'=>'form-control'
                                ];

                                $principle_pass_subjects = [
                                   'placeholder'=>'Principle Pass Subjects',
                                   'class'=>'form-control'
                                ];

                                $pass_subjects = [
                                   'placeholder'=>'Pass Subjects',
                                   'class'=>'form-control'
                                ];

                                $pass_grade = [
                                   'placeholder'=>'Pass Grade',
                                   'class'=>'form-control'
                                ];

                                $award_level = [
                                   'placeholder'=>'Award Level',
                                   'class'=>'form-control'
                                ];

                                $award_division = [
                                   'placeholder'=>'Award Division',
                                   'class'=>'form-control'
                                ];

                                $exclude_subjects = [
                                   'placeholder'=>'Exclude Subjects',
                                   'class'=>'form-control'
                                ];

                                $must_subjects = [
                                   'placeholder'=>'Must Subjects',
                                   'class'=>'form-control'
                                ];

                                $subsidiary_subjects = [
                                   'placeholder'=>'Subsidiary Subjects',
                                   'class'=>'form-control'
                                ];

                                $principle_subjects = [
                                   'placeholder'=>'Principle Subjects',
                                   'class'=>'form-control'
                                ];

                                $max_capacity = [
                                   'placeholder'=>'Max Capacity',
                                   'class'=>'form-control'
                                ];
                             @endphp

                               {!! Form::open(['url'=>'application/entry-requirement/update','class'=>'ss-form-processing']) !!}
                                 
                                 <div class="row">
                                  <div class="form-group col-3">
                                    {!! Form::label('','Programme') !!}
                                    <select name="campus_program_ids[]" class="form-control ss-select-tags" required multiple="multiple">
                                      <option value="">Select Programme</option>
                                      @foreach($campus_programs as $program)
                                      <option value="{{ $program->id }}" @if($program->id == $requirement->campus_program_id) selected="selected" @endif>{{ $program->program->name }}</option>
                                      @endforeach
                                    </select>
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Equivalent GPA') !!}
                                    {!! Form::text('equivalent_gpa',$requirement->equivalent_gpa,$equivalent_gpa) !!}
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Equivalent Pass Subjects') !!}
                                    {!! Form::text('equivalent_pass_subjects',$requirement->equivalent_pass_subjects,$equivalent_pass_subjects) !!}
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Equivalent Average Grade') !!}
                                    {!! Form::text('equivalent_average_grade',$requirement->equivalent_average_grade,$equivalent_average_grade) !!}
                                  </div>
                                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                  {!! Form::input('hidden','entry_requirement_id',$requirement->id) !!}
                                 </div>
                                 <div class="row">
                                   <div class="form-group col-3">
                                    {!! Form::label('','Open Equivalent GPA') !!}
                                    {!! Form::text('open_equivalent_gpa',$requirement->open_equivalent_gpa,$open_equivalent_gpa) !!}
                                   </div>
                                   <div class="form-group col-3">
                                    {!! Form::label('','Open Equivalent Pass Subjects') !!}
                                    {!! Form::text('open_equivalent_pass_subjects',$requirement->open_equivalent_pass_subjects,$open_equivalent_pass_subjects) !!}
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Open Equivalent Average Grade') !!}
                                    {!! Form::text('open_equivalent_average_grade',$requirement->open_equivalent_average_grade,$equivalent_average_grade) !!}
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Principle Pass Points') !!}
                                    {!! Form::text('principle_pass_points',$requirement->principle_pass_points,$principle_pass_points) !!}
                                  </div>
                                 </div>
                                 <div class="row">
                                   <div class="form-group col-3">
                                    {!! Form::label('','Principle Pass Subjects') !!}
                                    {!! Form::text('principle_pass_subjects',$requirement->principle_pass_subjects,$principle_pass_subjects) !!}
                                   </div>
                                   <div class="form-group col-3">
                                    {!! Form::label('','Pass Subjects') !!}
                                    {!! Form::text('pass_subjects',$requirement->pass_subjects,$pass_subjects) !!}
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Pass Grade') !!}
                                    {!! Form::text('pass_grade',$requirement->pass_grade,$pass_grade) !!}
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Award Level') !!}
                                    {!! Form::text('award_level',$requirement->award_level,$award_level) !!}
                                  </div>
                                 </div>
                                 <div class="row">
                                  <div class="form-group col-3">
                                    {!! Form::label('','Award Division') !!}
                                    {!! Form::text('award_division',$requirement->award_division,$award_division) !!}
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Exclude Subjects') !!}
                                    <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                       @foreach($subjects as $sub)
                                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Must Subjects') !!}
                                    <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                       @foreach($subjects as $sub)
                                       <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                  </div>
                                  <div class="form-group col-3">
                                    {!! Form::label('','Subsidiary Subjects') !!}
                                    <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                     @foreach($subjects as $sub)
                                     <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                     @endforeach
                                  </select>
                                  </div>
                                 </div>
                                 <div class="row">
                                  <div class="form-group col-3">
                                    {!! Form::label('','Principle Subjects') !!}
                                    <select name="principle_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                     @foreach($subjects as $sub)
                                     <option class="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                     @endforeach
                                  </select>
                                  </div>
                                 </div>
                             
                               <div class="ss-form-control">
                                  <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                                </div>
                              {!! Form::close() !!}

                            </div>
                            <div class="modal-footer justify-content-between">
                              <button amount="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->

                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-requirement-{{ $requirement->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-requirement-{{ $requirement->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to delete this entry requirement from the list?</p>
                                       <div class="ss-form-controls">
                                         <button amount="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('application/entry-requirement/'.$requirement->id.'/destroy') }}" class="btn btn-danger">Delete</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
                            </div>
                            <div class="modal-footer justify-content-between">
                              <button amount="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
            </div>
            @endif 
            @endif
           </div>
          </div>
      </div><!-- /.container-fluid -->
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

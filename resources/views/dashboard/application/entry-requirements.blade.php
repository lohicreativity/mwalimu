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
                                 @foreach($application_windows as $key=>$window)
                                 <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @elseif($request->get('application_window_id') != $window->id && $key == 0) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} - {{ $window->intake->name }}</option>
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
                  @if(Auth::user()->hasRole('admission-officer') || Auth::user()->hasRole('administrator'))
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

                                 $min_pass_subjects = [
                                    'placeholder'=>'Min Pass Subjects',
                                    'class'=>'form-control'
                                 ];

                                 $principle_pass_subjects = [
                                    'placeholder'=>'Principle Pass Subjects',
                                    'class'=>'form-control',
                                    'readonly'=>true
                                 ];

                                 $pass_subjects = [
                                    'placeholder'=>'No. of Pass Subjects',
                                    'class'=>'form-control'
                                 ];

                                 $pass_grade = [
                                    'placeholder'=>'Pass Grade',
                                    'class'=>'form-control'
                                 ];

                                 $nva_level = [
                                    'placeholder'=>'NVA Level',
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
                                          <!-- <option value="">Select Programme</option> -->
                                          @foreach($cert_campus_programs as $program)
                                          <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                    {!! Form::input('hidden','level','certificate') !!}

                                    <div class="form-group col-3">
                                       {!! Form::label('','No. of Pass Subjects') !!}
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
                                       {!! Form::label('','Must Subjects') !!}
                                       <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                          @foreach($subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Other Must Subjects') !!}
                                       <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                          @foreach($subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Exclude Subjects') !!}
                                       <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                          @foreach($subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                              </div>
                  
                              <div class="card-footer">
                                 <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                                 @if(App\Domain\Application\Models\EntryRequirement::hasPrevious($application_window) && $certificate_requirements == 0)
                                    <a href="{{ url('application/store-requirements-as-previous?application_window_id='.$application_window->id.'&level=certificate') }}" class="btn btn-primary">Save as Previous</a>
                                 @endif
                              </div>
                              {!! Form::close() !!}
                           </div><!-- /tabpane -->

                           <div class="tab-pane" id="ss-diploma" role="tabpanel">
                              @php
                        
                              $equivalent_majors = [
                                 'placeholder'=>'Certificate Majors',
                                 'class'=>'form-control',
                                 'readonly'=>true
                              ];

                              $principle_pass_subjects = [
                                 'placeholder'=>'No. of Principle Pass Subjects',
                                 'class'=>'form-control',
                                 'required'=>true
                              ];

                              $subsidiary_pass_subjects = [
                                 'placeholder'=>'No. of Subsidiary Pass Subjects',
                                 'class'=>'form-control',
                                 'required'=>true
                              ];

                              $pass_subjects = [
                                 'placeholder'=>'No. of Form IV Pass Subjects',
                                 'class'=>'form-control',
                                 'required'=>true
                              ];

                              $pass_grade = [
                                 'placeholder'=>'Form IV Pass Grade',
                                 'class'=>'form-control',
                                 'required'=>true
                              ];

                              $exclude_subjects = [
                                 'placeholder'=>'Form IV Exclude Subjects',
                                 'class'=>'form-control'
                              ];

                              $must_subjects = [
                                 'placeholder'=>'Form IV Must Subjects',
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
                                    {!! Form::label('','Programme') !!}<br>
                                    <select name="campus_program_ids[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;" required>
                                       <!-- <option value="">Select Programme</option> -->
                                       @foreach($diploma_campus_programs as $program)
                                       <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                                       @endforeach
                                    </select>
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','Certificate Majors') !!}
                                    <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;" >
                                       {{--
                                       @foreach($diploma_programs as $prog)
                                       <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                                       @endforeach
                                       --}}
                                       <option value="Marketing">Marketing</option>
                                       <option value="Financial Administration">Financial Administration</option>
                                       <option value="Accountancy">Accountancy</option>
                                       <option value="Finance">Finance</option>
                                       <option value="Nursing">Nursing</option>
                                       <option value="Youth">Youth</option>
                                       <option value="Clinical Science">Clinical Science</option>
                                       <option value="Police Science">Police Science</option>
                                       <option value="International Relations">International Relations</option>
                                       <option value="Diplomacy">Diplomacy</option>
                                       <option value="Counselling">Counselling</option>
                                       <option value="Psychology">Psychology</option>
                                       <option value="Law">Law</option>
                                       <option value="Secretarial Studies">Secretarial Studies</option>
                                       <option value="Office Management">Office Management</option>
                                       <option value="Public Administration">Public Administration</option>
                                       <option value="Journalism">Journalism</option>
                                       <option value="Education">Education</option>
                                       <option value="Economics">Economics</option>
                                       <option value="Procurement">Procurement</option>
                                       <option value="Human Resource">Human Resource</option>
                                       <option value="Records Management">Records Management</option>
                                       <option value="Archives">Archives</option>
                                       <option value="Rural Development">Rural Development</option>
                                       <option value="Information Management">Information Management</option>
                                       <option value="Library">Library</option>
                                       <option value="Gender">Gender</option>
                                       <option value="Social Studies">Social Studies</option>
                                       <option value="Business Administration">Business Administration</option>
                                       <option value="Community Development">Community Development</option>
                                       <option value="Information Communication Technology">Information Communication Technology</option>
                                       <option value="Information Technology">Information Technology</option>
                                       <option value="Computer Science">Computer Science</option>
                                       <option value="Social Work">Social Work</option>
                                       <option value="Development Planning">Development Planning</option>
                                       <option value="Local Government">Local Government</option>
                                       <option value="Crop Production">Crop Production</option>
                                       <option value="Agriculture Production">Agriculture Production</option>
                                       <option value="General Agriculture">General Agriculture</option>
                                       <option value="Business Management">Business Management</option>
                                       <option value="Insurance And Risk">Insurance And Risk</option>
                                       <option value="Tourism">Tourism</option>
                                       <option value="Hospitality">Hospitality</option>
                                    </select>
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','NTA Level') !!}
                                    <select name="nta_level" class="form-control" required>
                                       <option value="">Select NTA Level</option>
                                       <option value="4">4</option>
                                       <option value="5">5</option>
                                       <option value="6">6</option>
                                       <option value="7">7</option>
                                       <option value="8">8</option>
                                       <option value="9">9</option>
                                       <option value="10">10</option>
                                    </select>
                                 </div>

                                 {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                 {!! Form::input('hidden','level','diploma') !!}
                           
                                 <div class="form-group col-3" >
                                    {!! Form::label('','No. of Principle Pass Subjects') !!}
                                    {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                                 </div>
                                 
                                 <div class="form-group col-3">
                                    {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                                    {!! Form::text('subsidiary_pass_subjects',null,$subsidiary_pass_subjects) !!}
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','Form VI Must Subjects') !!}
                                    <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                       @foreach($high_subjects as $sub)
                                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','Form VI Other Must Subjects') !!}
                                    <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                       @foreach($high_subjects as $sub)
                                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','Form VI Exclude Subjects') !!}
                                    <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                       @foreach($high_subjects as $sub)
                                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','No. of Form IV Pass Subjects') !!}
                                    {!! Form::text('pass_subjects',null,$pass_subjects) !!}
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','Form IV Pass Grade') !!}
                                    <select name="pass_grade" class="form-control" required>
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
                                    {!! Form::label('','Form IV Must Subjects') !!}
                                    <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                       @foreach($subjects as $sub)
                                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','Form IV Other Must Subjects') !!}
                                    <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                       @foreach($subjects as $sub)
                                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                 </div>

                                 <div class="form-group col-3">
                                    {!! Form::label('','Form IV Exclude Subjects') !!}
                                    <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                       @foreach($subjects as $sub)
                                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                       @endforeach
                                    </select>
                                 </div>
                              </div>
                           </div>
                           
                           <div class="card-footer">
                              <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                              @if(App\Domain\Application\Models\EntryRequirement::hasPrevious($application_window) && $diploma_requirements == 0)
                                 <a href="{{ url('application/store-requirements-as-previous?application_window_id='.$application_window->id.'&level=diploma') }}" class="btn btn-primary">Save as Previous</a>
                              @endif
                           </div>
                           {!! Form::close() !!}
                           </div><!-- /tabpane -->

                           <div class="tab-pane" id="ss-degree" role="tabpanel">
                              @php
                                 $equivalent_gpa = [
                                    'placeholder'=>'Max Diploma GPA',
                                    'class'=>'form-control'
                                 ];

                                 $equivalent_majors = [
                                    'placeholder'=>'Diploma Majors',
                                    'class'=>'form-control',
                                 ];

                                 $equivalent_average_grade = [
                                    'placeholder'=>'Diploma Average Grade',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $min_equivalent_gpa = [
                                    'placeholder'=>'Min Diploma GPA',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $open_equivalent_gpa = [
                                    'placeholder'=>'OUT GPA',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $open_equivalent_majors = [
                                    'placeholder'=>'OUT Majors',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $open_equivalent_average_grade = [
                                    'placeholder'=>'OUT Average Grade',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $principle_pass_subjects = [
                                    'placeholder'=>'No. of Principle Pass Subjects',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $min_principle_pass_points = [
                                    'placeholder'=>'Min Principle Pass Points',
                                    'class'=>'form-control'
                                 ];

                                 $pass_subjects = [
                                    'placeholder'=>'No. of Form IV Pass Subjects',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $min_pass_subjects = [
                                    'placeholder'=>'Min Pass Subjects',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $pass_grade = [
                                    'placeholder'=>'Form IV Pass Grade',
                                    'class'=>'form-control',
                                    'required'=>true
                                 ];

                                 $award_level = [
                                    'placeholder'=>'Award Level',
                                    'class'=>'form-control'
                                 ];

                                 $exclude_subjects = [
                                    'placeholder'=>'Form IV Exclude Subjects',
                                    'class'=>'form-control'
                                 ];

                                 $must_subjects = [
                                    'placeholder'=>'Form IV Must Subjects',
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
                                       {!! Form::label('','Programme') !!}<br>
                                       <select name="campus_program_ids[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;" required>
                                          <!-- <option value="">Select Programme</option> -->
                                          @foreach($degree_campus_programs as $program)
                                          <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Diploma Average Grade') !!}
                                       <select name="equivalent_average_grade" class="form-control" required>
                                          <option value="">Select Diploma Average Grade</option>
                                          <option value="A">A</option>
                                          <option value="B">B</option>
                                          <option value="C">C</option>
                                          <option value="D">D</option>
                                          <option value="E">E</option>
                                          <option value="F">F</option>
                                       </select>
                                    </div>

                                    <div class="form-group col-3" style="width: 100%";>
                                       {!! Form::label('','Max Diploma GPA') !!}
                                       {!! Form::text('equivalent_gpa',null,$equivalent_gpa) !!}
                                    </div>

                                    <div class="form-group col-3" style="width: 100%";>
                                       {!! Form::label('','Diploma Majors') !!}
                                       <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;" required> 
                                          {{--
                                          @foreach($diploma_programs as $prog)
                                          <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                                          @endforeach
                                          --}}
                                          <option value="Marketing">Marketing</option>
                                          <option value="Financial Administration">Financial Administration</option>
                                          <option value="Accountancy">Accountancy</option>
                                          <option value="Finance">Finance</option>
                                          <option value="Nursing">Nursing</option>
                                          <option value="Youth">Youth</option>
                                          <option value="Clinical Science">Clinical Science</option>
                                          <option value="Police Science">Police Science</option>
                                          <option value="International Relations">International Relations</option>
                                          <option value="Diplomacy">Diplomacy</option>
                                          <option value="Counselling">Counselling</option>
                                          <option value="Psychology">Psychology</option>
                                          <option value="Law">Law</option>
                                          <option value="Secretarial Studies">Secretarial Studies</option>
                                          <option value="Office Management">Office Management</option>
                                          <option value="Public Administration">Public Administration</option>
                                          <option value="Journalism">Journalism</option>
                                          <option value="Education">Education</option>
                                          <option value="Economics">Economics</option>
                                          <option value="Procurement">Procurement</option>
                                          <option value="Human Resource">Human Resource</option>
                                          <option value="Records Management">Records Management</option>
                                          <option value="Archives">Archives</option>
                                          <option value="Rural Development">Rural Development</option>
                                          <option value="Information Management">Information Management</option>
                                          <option value="Library">Library</option>
                                          <option value="Gender">Gender</option>
                                          <option value="Social Studies">Social Studies</option>
                                          <option value="Business Administration">Business Administration</option>
                                          <option value="Community Development">Community Development</option>
                                          <option value="Information Communication Technology">Information Communication Technology</option>
                                          <option value="Information Technology">Information Technology</option>
                                          <option value="Computer Science">Computer Science</option>
                                          <option value="Social Work">Social Work</option>
                                          <option value="Development Planning">Development Planning</option>
                                          <option value="Local Government">Local Government</option>
                                          <option value="Crop Production">Crop Production</option>
                                          <option value="Agriculture Production">Agriculture Production</option>
                                          <option value="General Agriculture">General Agriculture</option>
                                          <option value="Business Management">Business Management</option>
                                          <option value="Insurance And Risk">Insurance And Risk</option>
                                          <option value="Tourism">Tourism</option>
                                          <option value="Hospitality">Hospitality</option>
                                       </select>
                                    </div>

                                    {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                    {!! Form::input('hidden','level','degree') !!}

                                    <div class="form-group col-3">
                                       {!! Form::label('','Diploma Must Subjects') !!}
                                       <select name="equivalent_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                          <option value="ENGLISH">English</option>
                                          <option value="KISWAHILI">Kiswahili</option>
                                          <option value="GEOGRAPHY">Geography</option>
                                          <option value="HISTORY">History</option>
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','NTA Level') !!}
                                       <select name="nta_level" class="form-control" required>
                                          <option value="">Select NTA Level</option>
                                          <option value="4">4</option>
                                          <option value="5">5</option>
                                          <option value="6">6</option>
                                          <option value="7">7</option>
                                          <option value="8">8</option>
                                          <option value="9">9</option>
                                          <option value="10">10</option>
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Min Diploma GPA') !!}
                                       {!! Form::text('min_equivalent_gpa',null,$min_equivalent_gpa) !!}
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','OUT GPA') !!}
                                       {!! Form::text('open_equivalent_gpa',null,$open_equivalent_gpa) !!}
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','OUT Exclude Subjects') !!}
                                       <select name="open_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;" required>
                                          <option value="OFC 017">Communication Skills</option>
                                          <option value="OFP 018">Development Studies</option>
                                          <option value="OFP 020">Introduction to ICT</option>
                                       </select>
                                    </div>
                           
                                    <div class="form-group col-3">
                                       {!! Form::label('','No. of Principle Pass Subjects') !!}
                                       {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                                       {!! Form::text('subsidiary_pass_subjects',null,$min_principle_pass_points) !!}
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Form VI Must Subjects') !!}
                                       <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                          @foreach($high_subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Form VI Other Must Subjects') !!}
                                       <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                          @foreach($high_subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Form VI Exclude Subjects') !!}
                                       <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                          @foreach($high_subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div> 

                                    <div class="form-group col-3">
                                       {!! Form::label('','No. of Form IV Pass Subjects') !!}
                                       {!! Form::text('pass_subjects',null,$pass_subjects) !!}
                                    </div>
                              
                                    <div class="form-group col-3">
                                       {!! Form::label('','Form IV Pass Grade') !!}
                                       <select name="pass_grade" class="form-control" required>
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
                                       {!! Form::label('','Form IV Must Subjects') !!}
                                       <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                          @foreach($subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>  

                                    <div class="form-group col-3">
                                       {!! Form::label('','Form IV Other Must Subjects') !!}
                                       <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                          @foreach($subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>

                                    <div class="form-group col-3">
                                       {!! Form::label('','Form IV Exclude Subjects') !!}
                                       <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple" style="width: 100%;">
                                          @foreach($subjects as $sub)
                                          <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>                  
                                 </div>
                              </div>
                           
                              <div class="card-footer">
                                 <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                                 @if(App\Domain\Application\Models\EntryRequirement::hasPrevious($application_window) && $bachelor_requirements == 0)
                                    <a href="{{ url('application/store-requirements-as-previous?application_window_id='.$application_window->id.'&level=degree') }}" class="btn btn-primary">Save as Previous</a>
                                 @endif
                              </div>
                              {!! Form::close() !!}
                           </div><!-- /tabpane -->
                        </div>
                     </div>
                  @endif

                  @if(count($entry_requirements) != 0)
                     @livewire('application.entry-requirements')
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
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
                        <option value="{{ $window->id }}" @if($request->get('application_window_id') == $window->id) selected="selected" @endif>{{ $window->begin_date }} - {{ $window->end_date }} </option>
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

                $equivalent_majors = [
                   'placeholder'=>'Equivalent Majors',
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

                $open_equivalent_majors = [
                   'placeholder'=>'Open Equivalent Majors',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $min_pass_subjects = [
                   'placeholder'=>'Min Pass Subjects',
                   'class'=>'form-control'
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
                      @foreach($cert_campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                  </div>
 <!--                 <div class="form-group col-3">
                    {!! Form::label('','Diploma GPA') !!}
                    {!! Form::text('equivalent_gpa',null,$equivalent_gpa) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Majors') !!}
                    <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple" required disabled="disabled">
                       @foreach($diploma_programs as $prog)
                       <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Average Grade') !!}
                    <select name="equivalent_average_grade" class="form-control" disabled="disabled">
                       <option value="">Select Pass Grade</option>
                       <option value="A">A</option>
                       <option value="B">B</option>
                       <option value="C">C</option>
                       <option value="D">D</option>
                       <option value="E">E</option>
                       <option value="F">F</option>
                    </select>
                  </div>
                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                  {!! Form::input('hidden','level','certificate') !!}
                   
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent GPA') !!}
                    {!! Form::text('open_equivalent_gpa',null,$open_equivalent_gpa) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Majors') !!}
                    {!! Form::text('open_equivalent_majors',null,$open_equivalent_majors) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Average Grade') !!}
                    {!! Form::text('open_equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Points') !!}
                    {!! Form::text('principle_pass_points',null,$principle_pass_points) !!}
                  </div>
                 
                   <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Subjects') !!}
                    {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                   </div>
-->
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
                   @if(App\Domain\Application\Models\EntryRequirement::hasPrevious($application_window))
                 <a href="{{ url('application/store-requirements-as-previous?application_window_id='.$application_window->id.'&level=certificate') }}" class="btn btn-primary">Save as Previous</a>
                 @endif
                </div>
              {!! Form::close() !!}
            </div><!-- /tabpane -->
            <div class="tab-pane" id="ss-diploma" role="tabpanel">
               @php
                $equivalent_gpa = [
                   'placeholder'=>'Certificate GPA',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $equivalent_majors = [
                   'placeholder'=>'Certificate Majors',
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

                $open_equivalent_majors = [
                   'placeholder'=>'Open Equivalent Majors',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $open_equivalent_average_grade = [
                   'placeholder'=>'Open Equivalent Average Grade',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $principle_pass_points = [
                   'placeholder'=>'No. of Principle Pass Points',
                   'class'=>'form-control',
                   'readonly'=>true
                ];

                $principle_pass_subjects = [
                   'placeholder'=>'No. of Principle Pass Points',
                   'class'=>'form-control',
                   'required'=>true
                ];

                $subsidiary_pass_subjects = [
                   'placeholder'=>'No. of Subsidiary Pass Subjects',
                   'class'=>'form-control',
                   'required'=>true
                ];

                $pass_subjects = [
                   'placeholder'=>'No. of Pass Subjects',
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

                $award_division = [
                   'placeholder'=>'Award Division',
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
                    {!! Form::label('','Programme') !!}<br>
                    <select name="campus_program_ids[]" class="form-control ss-select-tags" style="width: 100%; required multiple="multiple">
                      <option value="">Select Programme</option>
                      @foreach($diploma_campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                  </div>
<!--
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent GPA') !!}
                    {!! Form::text('equivalent_gpa',null,$equivalent_gpa) !!}
                  </div>
-->
                  <div class="form-group col-3">
                    {!! Form::label('','Certificate Majors') !!}
                    <select name="equivalent_majors[]" class="form-control ss-select-tags" style="width: 100%; multiple="multiple">
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
                       <option value="Information Management">Information Management</option>
                       <option value="Library">Library</option>
                       <option value="Gender">Gender</option>
                       <option value="Social Studies">Social Studies</option>
                       <option value="Business Adminstration">Business Adminstration</option>
                       <option value="Community Development">Community Development</option>
                       <option value="Information Communication Technology">Information Communication Technology</option>
                       <option value="Information Technology">Information Technology</option>
                       <option value="Computer Science">Computer Science</option>
                       <option value="Social Work">Social Work</option>
                    </select>
                  </div>
<!--
                  <div class="form-group col-3">
                    {!! Form::label('','Equivalent Average Grade') !!}
                    <select name="equivalent_average_grade" class="form-control" disabled="disabled">
                       <option value="">Select Pass Grade</option>
                       <option value="A">A</option>
                       <option value="B">B</option>
                       <option value="C">C</option>
                       <option value="D">D</option>
                       <option value="E">E</option>
                       <option value="F">F</option>
                    </select>
                  </div>
                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                  {!! Form::input('hidden','level','diploma') !!}
                 <div class="form-group col-3">
                    {!! Form::label('','Equivalent Must Subjects') !!}
                    <select name="equivalent_must_subjects[]" class="form-control ss-select-tags" multiple="multiple" disabled="disabled">
                       <option value="ENGLISH">English</option>
                       <option value="KISWAHILI">Kiswahili</option>
                       <option value="GEOGRAPHY">Geography</option>
                       <option value="HISTORY">History</option>
                    </select>
                  </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent GPA') !!}
                    {!! Form::text('open_equivalent_gpa',null,$open_equivalent_gpa) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Majors') !!}
                    {!! Form::text('open_equivalent_majors',null,$open_equivalent_majors) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Open Equivalent Average Grade') !!}
                    {!! Form::text('open_equivalent_average_grade',null,$equivalent_average_grade) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Points') !!}
                    {!! Form::text('principle_pass_points',null,$principle_pass_points) !!}
                  </div>
-->                 
                   <div class="form-group col-3" >
                    {!! Form::label('','No. of Principle Pass Subjects') !!}
                    {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                    {!! Form::text('subsidiary_pass_subjects',null,$subsidiary_pass_subjects) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','No. of Pass Subjects') !!}
                    {!! Form::text('pass_subjects',null,$pass_subjects) !!}
                  </div>

 <!--                 <div class="form-group col-3">
                    {!! Form::label('','Subsidiary Subjects') !!}
                    <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple" required>
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div> 
                  
-->                  <div class="form-group col-3">
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
                    {!! Form::label('','Form IV Must Subjects') !!}
                    <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Form IV Other Must Subjects') !!}
                    <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Form IV Exclude Subjects') !!}
                    <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Form VI Must Subjects') !!}
                    <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>

                  <div class="form-group col-3">
                    {!! Form::label('','Form VI Other Must Subjects') !!}
                    <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                   <div class="form-group col-3">
                    {!! Form::label('','Form VI Exclude Subjects') !!}
                    <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                </div>
             </div>
             
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                   @if(App\Domain\Application\Models\EntryRequirement::hasPrevious($application_window))
                 <a href="{{ url('application/store-requirements-as-previous?application_window_id='.$application_window->id.'&level=diploma') }}" class="btn btn-primary">Save as Previous</a>
                 @endif
                </div>
              {!! Form::close() !!}
            </div><!-- /tabpane -->
            <div class="tab-pane" id="ss-degree" role="tabpanel">
               @php
                $equivalent_gpa = [
                   'placeholder'=>'Max. Diploma GPA',
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
                   'placeholder'=>'Min. Diploma GPA',
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

                $principle_pass_points = [
                   'placeholder'=>'Principle Pass Points',
                   'class'=>'form-control',
                   'required'=>true
                ];

                $min_principle_pass_points = [
                   'placeholder'=>'No. of Subsidiary Pass Subjects',
                   'class'=>'form-control',
                   'required'=>true
                ];

                $principle_pass_subjects = [
                   'placeholder'=>'No. of Principle Pass Subjects',
                   'class'=>'form-control',
                   'required'=>true
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

                $award_division = [
                   'placeholder'=>'Award Division',
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
                    {!! Form::label('','Programme') !!}<br>
                    <select name="campus_program_ids[]" class="form-control ss-select-tags" style="width: 100%; required multiple="multiple">
                      <option value="">Select Programme</option>
                      @foreach($degree_campus_programs as $program)
                      <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3" style="width: 100%";>
                    {!! Form::label('','Max. Diploma GPA') !!}
                    {!! Form::text('equivalent_gpa',null,$equivalent_gpa) !!}
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Min. Diploma GPA') !!}
                    {!! Form::text('min_equivalent_gpa',null,$min_equivalent_gpa) !!}
                  </div>
                  <div class="form-group col-3" style="width: 100%";>
                    {!! Form::label('','Diploma Majors') !!}
                    <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple" required> 
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
                       <option value="Information Management">Information Management</option>
                       <option value="Library">Library</option>
                       <option value="Gender">Gender</option>
                       <option value="Social Studies">Social Studies</option>
                       <option value="Business Adminstration">Business Adminstration</option>
                       <option value="Community Development">Community Development</option>
                       <option value="Information Communication Technology">Information Communication Technology</option>
                       <option value="Information Technology">Information Technology</option>
                       <option value="Computer Science">Computer Science</option>
                       <option value="Social Work">Social Work</option>
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
                  {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                  {!! Form::input('hidden','level','degree') !!}
                  <div class="form-group col-3">
                    {!! Form::label('','Diploma Must Subjects') !!}
                    <select name="equivalent_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       <option value="ENGLISH">English</option>
                       <option value="KISWAHILI">Kiswahili</option>
                       <option value="GEOGRAPHY">Geography</option>
                       <option value="HISTORY">History</option>
                    </select>
                  </div>
                 
                   <div class="form-group col-3">
                    {!! Form::label('','OUT GPA') !!}
                    {!! Form::text('open_equivalent_gpa',null,$open_equivalent_gpa) !!}
                   </div>

                   <div class="form-group col-3">
                    {!! Form::label('','OUT Exclude Subjects') !!}
                    <select name="open_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple" required>
                       <option value="OFC 017">Communication Skills</option>
                       <option value="OFP 018">Development Studies</option>
                       <option value="OFP 020">Introduction to ICT</option>
                    </select>
                  </div>
<!--
                  <div class="form-group col-3">
                    {!! Form::label('','Principle Pass Points') !!}
                    {!! Form::text('principle_pass_points',null,$principle_pass_points) !!}
                  </div>
-->               
                   <div class="form-group col-3">
                    {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                    {!! Form::text('subsidiary_pass_subjects',null,$min_principle_pass_points) !!}
                  </div>
                   <div class="form-group col-3">
                    {!! Form::label('','No. of Principle Pass Subjects') !!}
                    {!! Form::text('principle_pass_subjects',null,$principle_pass_subjects) !!}
                   </div>
                   <div class="form-group col-3">
                    {!! Form::label('','No. of Form IV Pass Subjects') !!}
                    {!! Form::text('pass_subjects',null,$pass_subjects) !!}
                  </div>

<!--                  <div class="form-group col-3">
                    {!! Form::label('','Subsidiary Subjects') !!}
                    <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple" required>
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
-->                 
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
                    {!! Form::label('','Form IV Must Subjects') !!}
                    <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>            
                  <div class="form-group col-3">
                    {!! Form::label('','Form IV Other Must Subjects') !!}
                    <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Form IV Exclude Subjects') !!}
                    <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>                  
                  <div class="form-group col-3">
                    {!! Form::label('','Form VI Must Subjects') !!}
                    <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Form VI Other Must Subjects') !!}
                    <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-3">
                    {!! Form::label('','Form VI Exclude Subjects') !!}
                    <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                       @foreach($high_subjects as $sub)
                       <option value="{{ $sub->subject_name }}">{{ $sub->subject_name }}</option>
                       @endforeach
                    </select>
                  </div>                  
                </div>
             </div>
             
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Entry Requirement') }}</button>
                   @if(App\Domain\Application\Models\EntryRequirement::hasPrevious($application_window))
                 <a href="{{ url('application/store-requirements-as-previous?application_window_id='.$application_window->id.'&level=degree') }}" class="btn btn-primary">Save as Previous</a>
                 @endif
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
                 {!! Form::open(['url'=>'application/entry-requirements','method'=>'GET']) !!}
                 {!! Form::input('hidden','application_window_id',$request->get('application_window_id')) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for programme name">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!}

                  <table id="example2" class="table table-bordered table-hover ss-margin-top">
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

                              @if(str_contains($requirement->campusProgram->program->award->name,'Certificate'))
                                 @php
                                    $equivalent_gpa = [
                                       'placeholder'=>'Equivalent GPA',
                                       'class'=>'form-control',
                                       'readonly'=>true
                                    ];

                                    $equivalent_majors = [
                                       'placeholder'=>'Equivalent Majors',
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

                                    $open_equivalent_majors = [
                                       'placeholder'=>'Open Equivalent Majors',
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

                                   {!! Form::open(['url'=>'application/entry-requirement/update','class'=>'ss-form-processing']) !!}
                                   <div class="card-body">
                                     
                                     <div class="row">
                                      <div class="form-group col-3">
                                          {!! Form::label('','Programme') !!}
                                          <select name="campus_program_id" class="form-control" required>
                                            <option value="">Select Programme</option>
                                            @foreach($campus_programs as $program)
                                            <option value="{{ $program->id }}" @if($program->id == $requirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Equivalent GPA') !!}
                                        {!! Form::text('equivalent_gpa',$requirement->equivalent_gpa,$equivalent_gpa) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Equivalent Majors') !!}
                                        <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple" required disabled="disabled">
                                           @foreach($diploma_programs as $prog)
                                               <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                                               @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Equivalent Average Grade') !!}
                                        <select name="equivalent_average_grade" class="form-control" disabled="disabled">
                                           <option value="">Select Pass Grade</option>
                                           <option value="A" @if($requirement->equivalent_average_grade == 'A') selected="selected" @endif>A</option>
                                             <option value="B" @if($requirement->equivalent_average_grade == 'B') selected="selected" @endif>B</option>
                                             <option value="C" @if($requirement->equivalent_average_grade == 'C') selected="selected" @endif>C</option>
                                             <option value="D" @if($requirement->equivalent_average_grade == 'D') selected="selected" @endif>D</option>
                                             <option value="E" @if($requirement->equivalent_average_grade == 'E') selected="selected" @endif>E</option>
                                             <option value="F" @if($requirement->equivalent_average_grade == 'F') selected="selected" @endif>F</option>
                                        </select>
                                      </div>
                                      {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                      {!! Form::input('hidden','entry_requirement_id',$requirement->id) !!}
                                     
                                       <div class="form-group col-3">
                                        {!! Form::label('','Open Equivalent GPA') !!}
                                        {!! Form::text('open_equivalent_gpa',$requirement->open_equivalent_gpa,$open_equivalent_gpa) !!}
                                       </div>
                                       <div class="form-group col-3">
                                        {!! Form::label('','Open Equivalent Majors') !!}
                                        {!! Form::text('open_equivalent_majors',$requirement->open_equivalent_majors,$open_equivalent_majors) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Open Equivalent Average Grade') !!}
                                        {!! Form::text('open_equivalent_average_grade',$requirement->open_equivalent_average_grade,$equivalent_average_grade) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Principle Pass Points') !!}
                                        {!! Form::text('principle_pass_points',$requirement->principle_pass_points,$principle_pass_points) !!}
                                      </div>
                                     
                                       <div class="form-group col-3">
                                        {!! Form::label('','Principle Pass Subjects') !!}
                                        {!! Form::text('principle_pass_subjects',$requirement->principle_pass_subjects,$principle_pass_subjects) !!}
                                       </div>
                                       <div class="form-group col-3">
                                        {!! Form::label('','Number of Pass Subjects') !!}
                                        {!! Form::text('pass_subjects',$requirement->pass_subjects,$pass_subjects) !!}
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Pass Grade') !!}
                                        <select name="pass_grade" class="form-control">
                                           <option value="A" @if($requirement->pass_grade == 'A') selected="selected" @endif>A</option>
                                             <option value="B" @if($requirement->pass_grade == 'B') selected="selected" @endif>B</option>
                                             <option value="C" @if($requirement->pass_grade == 'C') selected="selected" @endif>C</option>
                                             <option value="D" @if($requirement->pass_grade == 'D') selected="selected" @endif>D</option>
                                             <option value="E" @if($requirement->pass_grade == 'E') selected="selected" @endif>E</option>
                                             <option value="F" @if($requirement->pass_grade == 'F') selected="selected" @endif>F</option>
                                        </select>
                                      </div>
                                      
                                    
                                      <div class="form-group col-3">
                                        {!! Form::label('','Form IV Exclude Subjects') !!}
                                        <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                           @foreach($subjects as $sub)
                                           <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                           @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Form IV Must Subjects') !!}
                                        <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                           @foreach($subjects as $sub)
                                           <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                           @endforeach
                                        </select>
                                      </div>
                                      <div class="form-group col-3">
                                        {!! Form::label('','Form IV Other Must Subjects') !!}
                                        <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                           @foreach($subjects as $sub)
                                           <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                           @endforeach
                                        </select>
                                      </div>
                                     </div>

                                 </div>
                                 
                                   <div class="card-footer">
                                      <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                    </div>
                                  {!! Form::close() !!}
                              @elseif(str_contains($requirement->campusProgram->program->award->name,'Diploma'))
                                  @php
                                      $equivalent_gpa = [
                                         'placeholder'=>'Equivalent GPA',
                                         'class'=>'form-control',
                                         'readonly'=>true
                                      ];

                                      $equivalent_majors = [
                                         'placeholder'=>'Equivalent Majors',
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

                                      $open_equivalent_majors = [
                                         'placeholder'=>'Open Equivalent Majors',
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
                                         'class'=>'form-control'
                                      ];

                                      $subsidiary_pass_subjects = [
                                         'placeholder'=>'Subsidiary Pass Subjects',
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
                                     <div class="card-body">
                                       
                                       <div class="row">
                                        <div class="form-group col-3">
                                          {!! Form::label('','Programme') !!}
                                          <select name="campus_program_id" class="form-control" required>
                                            <option value="">Select Programme</option>
                                            @foreach($campus_programs as $program)
                                            <option value="{{ $program->id }}" @if($program->id == $requirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Equivalent GPA') !!}
                                          {!! Form::text('equivalent_gpa',$requirement->equivalent_gpa,$equivalent_gpa) !!}
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Equivalent Majors') !!}
                                          <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple">
                                             {{--
                                             @foreach($diploma_programs as $prog)

                                               <option value="{{ substr($prog->name,20) }}" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array(substr($prog->name,20),unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>{{ substr($prog->name,20) }}</option>
                                               @endforeach

                                               @foreach($diploma_programs as $prog)
                                               <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                                               @endforeach
                                               --}}
                                               <option value="Marketing" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Marketing',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Marketing</option>
                                               <option value="Financial Administration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Financial Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Financial Administration</option>
                                               <option value="Accountancy" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Accountancy',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Accountancy</option>
                                               <option value="Finance" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Finance',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Finance</option>
                                               <option value="Nursing" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Nursing',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Nursing</option>
                                               <option value="Youth" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Youth',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Youth</option>
                                               <option value="Clinical Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Clinical Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Clinical Science</option>
                                               <option value="Police Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Police Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Police Science</option>
                                               <option value="International Relations" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('International Relations',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>International Relations</option>
                                               <option value="Diplomacy" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Diplomacy',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Diplomacy</option>
                                               <option value="Counselling" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Counselling',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Counselling</option>
                                               <option value="Psychology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Psychology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Psychology</option>
                                               <option value="Law" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Law',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Law</option>
                                               <option value="Secretarial Studies" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Secretarial Studies',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Secretarial Studies</option>
                                               <option value="Office Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Office Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Office Management</option>
                                               <option value="Public Administration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Public Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Public Administration</option>
                                               <option value="Journalism" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Journalism',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Journalism</option>
                                               <option value="Education" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Education',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Education</option>
                                               <option value="Economics" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Economics',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Economics</option>
                                               <option value="Procurement" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Procurement',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Procurement</option>
                                               <option value="Human Resource" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Human Resource',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Human Resource</option>
                                               <option value="Records Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Records Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Records Management</option>
                                               <option value="Archives" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Archives',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Archives</option>
                                               <option value="Information Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Management</option>
                                               <option value="Library" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Library',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Library</option>
                                               <option value="Gender" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Gender',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Gender</option>
                                               <option value="Social Studies" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Studies',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Studies</option>
                                               <option value="Business Adminstration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Business Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Business Adminstration</option>
                                               <option value="Community Development" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Community Development',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Community Development</option>
                                               <option value="Information Communication Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Communication Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Communication Technology</option>
                                               <option value="Information Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Technology</option>
                                               <option value="Computer Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Computer Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Computer Science</option>
                                               <option value="Social Work" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Work',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Work</option>
                                          </select>
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Equivalent Average Grade') !!}
                                          <select name="equivalent_average_grade" class="form-control" disabled="disabled">
                                             <option value="">Select Pass Grade</option>
                                             <option value="A" @if($requirement->equivalent_average_grade == 'A') selected="selected" @endif>A</option>
                                             <option value="B" @if($requirement->equivalent_average_grade == 'B') selected="selected" @endif>B</option>
                                             <option value="C" @if($requirement->equivalent_average_grade == 'C') selected="selected" @endif>C</option>
                                             <option value="D" @if($requirement->equivalent_average_grade == 'D') selected="selected" @endif>D</option>
                                             <option value="E" @if($requirement->equivalent_average_grade == 'E') selected="selected" @endif>E</option>
                                             <option value="F" @if($requirement->equivalent_average_grade == 'F') selected="selected" @endif>F</option>
                                          </select>
                                        </div>
                                        {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                        {!! Form::input('hidden','entry_requirement_id',$requirement->id) !!}
                                       
                                         <div class="form-group col-3">
                                          {!! Form::label('','Open Equivalent GPA') !!}
                                          {!! Form::text('open_equivalent_gpa',$requirement->open_equivalent_gpa,$open_equivalent_gpa) !!}
                                         </div>
                                         <div class="form-group col-3">
                                          {!! Form::label('','Open Equivalent Majors') !!}
                                          {!! Form::text('open_equivalent_majors',$requirement->open_equivalent_majors,$open_equivalent_majors) !!}
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Open Equivalent Average Grade') !!}
                                          {!! Form::text('open_equivalent_average_grade',$requirement->open_equivalent_average_grade,$equivalent_average_grade) !!}
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Principle Pass Points') !!}
                                          {!! Form::text('principle_pass_points',$requirement->principle_pass_points,$principle_pass_points) !!}
                                        </div>
                                       
                                         <div class="form-group col-3">
                                          {!! Form::label('','No. of Principle Pass Subjects') !!}
                                          {!! Form::text('principle_pass_subjects',$requirement->principle_pass_subjects,$principle_pass_subjects) !!}
                                         </div>
                                         <div class="form-group col-3">
                                          {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                                          {!! Form::text('subsidiary_pass_subjects',$requirement->subsidiary_pass_subjects,$subsidiary_pass_subjects) !!}
                                         </div>
                                         <div class="form-group col-3">
                                          {!! Form::label('','No. of Pass Subjects') !!}
                                          {!! Form::text('pass_subjects',$requirement->pass_subjects,$pass_subjects) !!}
                                        </div>
<!--
                                        <div class="form-group col-3">
                                            {!! Form::label('','Subsidiary Subjects') !!}
                                            <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($high_subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->subsidiary_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->subsidiary_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
-->                                        
                                       
                                        <div class="form-group col-3">
                                          {!! Form::label('','Pass Grade') !!}
                                          <select name="pass_grade" class="form-control">
                                             <option value="">Select Pass Grade</option>
                                             <option value="A" @if($requirement->pass_grade == 'A') selected="selected" @endif>A</option>
                                             <option value="B" @if($requirement->pass_grade == 'B') selected="selected" @endif>B</option>
                                             <option value="C" @if($requirement->pass_grade == 'C') selected="selected" @endif>C</option>
                                             <option value="D" @if($requirement->pass_grade == 'D') selected="selected" @endif>D</option>
                                             <option value="E" @if($requirement->pass_grade == 'E') selected="selected" @endif>E</option>
                                             <option value="F" @if($requirement->pass_grade == 'F') selected="selected" @endif>F</option>
                                          </select>
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','NTA Level') !!}
                                          <select name="nta_level" class="form-control">
                                             <option value="">Select NTA Level</option>
                                             <option value="4" @if($requirement->nta_level == 4) selected="selected" @endif>4</option>
                                               <option value="5" @if($requirement->nta_level == 5) selected="selected" @endif>5</option>
                                               <option value="6" @if($requirement->nta_level == 6) selected="selected" @endif>6</option>
                                               <option value="7" @if($requirement->nta_level == 7) selected="selected" @endif>7</option>
                                               <option value="8" @if($requirement->nta_level == 8) selected="selected" @endif>8</option>
                                               <option value="9" @if($requirement->nta_level == 9) selected="selected" @endif>9</option>
                                               <option value="10" @if($requirement->nta_level == 10) selected="selected" @endif>10</option>
                                          </select>
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Form IV Exclude Subjects') !!}
                                          <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Form IV Must Subjects') !!}
                                          <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                        </div>
                                       
                                       
                                          <div class="form-group col-3">
                                          {!! Form::label('','Form IV Other Must Subjects') !!}
                                          <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                        </div>
                                         <div class="form-group col-3">
                                          {!! Form::label('','Form VI Exclude Subjects') !!}
                                          <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($high_subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->advance_exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->advance_exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                        </div>
                                        <div class="form-group col-3">
                                          {!! Form::label('','Form VI Must Subjects') !!}
                                          <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($high_subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->advance_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                        </div>

                                        <div class="form-group col-3">
                                          {!! Form::label('','Form VI Other Must Subjects') !!}
                                          <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($high_subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_advance_must_subjects) != '') @if(in_array($sub->subject_name,$subjects)) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                        </div>
                                   
                                      </div>
                                   </div>
                                   
                                     <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                      </div>
                                    {!! Form::close() !!}
                              @elseif(str_contains($requirement->campusProgram->program->award->name,'Bachelor'))
                                     @php
                                        $equivalent_gpa = [
                                           'placeholder'=>'Equivalent GPA',
                                           'class'=>'form-control'
                                        ];

                                        $equivalent_majors = [
                                           'placeholder'=>'Equivalent Majors',
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

                                        $open_equivalent_majors = [
                                           'placeholder'=>'Open Equivalent Majors',
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

                                        $min_principle_pass_points = [
                                           'placeholder'=>'Min Principle Pass Points',
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

                                        $min_pass_subjects = [
                                           'placeholder'=>'Min Pass Subjects',
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
                                       <div class="card-body">
                                         
                                         <div class="row">
                                          <div class="form-group col-3">
                                            {!! Form::label('','Programme') !!}
                                            <select name="campus_program_id" class="form-control" required>
                                              <option value="">Select Programme</option>
                                              @foreach($campus_programs as $program)
                                              <option value="{{ $program->id }}" @if($program->id == $requirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                              @endforeach
                                            </select>
                                          </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Equivalent GPA') !!}
                                            {!! Form::text('equivalent_gpa',$requirement->equivalent_gpa,$equivalent_gpa) !!}
                                          </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Min Equivalent GPA') !!}
                                            {!! Form::text('min_equivalent_gpa',$requirement->min_equivalent_gpa,$min_equivalent_gpa) !!}
                                          </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Equivalent Majors') !!}
                                            <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple">
                                               {{--
                                               @foreach($diploma_programs as $prog)
                                               <option value="{{ substr($prog->name,20) }}" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array(substr($prog->name,20),unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>{{ substr($prog->name,20) }}</option>
                                               @endforeach

                                               @foreach($diploma_programs as $prog)
                                               <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                                               @endforeach
                                               --}}
                                               <option value="Marketing" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Marketing',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Marketing</option>
                                               <option value="Financial Administration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Financial Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Financial Administration</option>
                                               <option value="Accountancy" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Accountancy',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Accountancy</option>
                                               <option value="Finance" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Finance',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Finance</option>
                                               <option value="Nursing" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Nursing',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Nursing</option>
                                               <option value="Youth" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Youth',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Youth</option>
                                               <option value="Clinical Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Clinical Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Clinical Science</option>
                                               <option value="Police Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Police Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Police Science</option>
                                               <option value="International Relations" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('International Relations',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>International Relations</option>
                                               <option value="Diplomacy" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Diplomacy',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Diplomacy</option>
                                               <option value="Counselling" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Counselling',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Counselling</option>
                                               <option value="Psychology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Psychology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Psychology</option>
                                               <option value="Law" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Law',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Law</option>
                                               <option value="Secretarial Studies" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Secretarial Studies',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Secretarial Studies</option>
                                               <option value="Office Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Office Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Office Management</option>
                                               <option value="Public Administration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Public Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Public Administration</option>
                                               <option value="Journalism" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Journalism',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Journalism</option>
                                               <option value="Education" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Education',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Education</option>
                                               <option value="Economics" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Economics',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Economics</option>
                                               <option value="Procurement" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Procurement',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Procurement</option>
                                               <option value="Human Resource" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Human Resource',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Human Resource</option>
                                               <option value="Records Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Records Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Records Management</option>
                                               <option value="Archives" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Archives',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Archives</option>
                                               <option value="Information Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Management</option>
                                               <option value="Library" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Library',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Library</option>
                                               <option value="Gender" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Gender',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Gender</option>
                                               <option value="Social Studies" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Studies',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Studies</option>
                                               <option value="Business Adminstration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Business Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Business Adminstration</option>
                                               <option value="Community Development" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Community Development',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Community Development</option>
                                               <option value="Information Communication Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Communication Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Communication Technology</option>
                                               <option value="Information Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Technology</option>
                                               <option value="Computer Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Computer Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Computer Science</option>
                                               <option value="Social Work" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Work',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Work</option>
                                            </select>
                                          </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Equivalent Average Grade') !!}
                                            <select name="equivalent_average_grade" class="form-control">
                                               <option value="">Select Pass Grade</option>
                                               <option value="A" @if($requirement->equivalent_average_grade == 'A') selected="selected" @endif>A</option>
                                               <option value="B" @if($requirement->equivalent_average_grade == 'B') selected="selected" @endif>B</option>
                                               <option value="C" @if($requirement->equivalent_average_grade == 'C') selected="selected" @endif>C</option>
                                               <option value="D" @if($requirement->equivalent_average_grade == 'D') selected="selected" @endif>D</option>
                                               <option value="E" @if($requirement->equivalent_average_grade == 'E') selected="selected" @endif>E</option>
                                               <option value="F" @if($requirement->equivalent_average_grade == 'F') selected="selected" @endif>F</option>
                                            </select>
                                          </div>
                                          {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                          {!! Form::input('hidden','entry_requirement_id',$requirement->id) !!}
                                          <div class="form-group col-3">
                                            {!! Form::label('','Equivalent Must Subjects') !!}
                                            <select name="equivalent_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               <option value="ENGLISH" @if(unserialize($requirement->equivalent_must_subjects) != '') @if(in_array('ENGLISH',unserialize($requirement->equivalent_must_subjects))) selected="selected" @endif @endif>English</option>
                                               <option value="KISWAHILI" @if(unserialize($requirement->equivalent_must_subjects) != '') @if(in_array('KISWAHILI',unserialize($requirement->equivalent_must_subjects))) selected="selected" @endif @endif>Kiswahili</option>
                                               <option value="GEOGRAPHY" @if(unserialize($requirement->equivalent_must_subjects) != '') @if(in_array('GEOGRAPHY',unserialize($requirement->equivalent_must_subjects))) selected="selected" @endif @endif>Geography</option>
                                               <option value="HISTORY" @if(unserialize($requirement->equivalent_must_subjects) != '') @if(in_array('HISTORY',unserialize($requirement->equivalent_must_subjects))) selected="selected" @endif @endif>History</option>
                                            </select>
                                          </div>
                                         
                                           <div class="form-group col-3">
                                            {!! Form::label('','Open Equivalent GPA') !!}
                                            {!! Form::text('open_equivalent_gpa',$requirement->open_equivalent_gpa,$open_equivalent_gpa) !!}
                                           </div>
                                           <div class="form-group col-3">
                                              {!! Form::label('','Open Exclude Subjects') !!}
                                              <select name="open_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                 <option value="OFC 017" @if(unserialize($requirement->open_exclude_subjects) != '') @if(in_array('OFP 018',unserialize($requirement->open_exclude_subjects))) selected="selected" @endif @endif>Communication Skills</option>
                                                 <option value="OFC 017" @if(unserialize($requirement->open_exclude_subjects) != '') @if(in_array('OFP 018',unserialize($requirement->open_exclude_subjects))) selected="selected" @endif @endif>Development Studies</option>
                                                 <option value="OFP 020" @if(unserialize($requirement->open_exclude_subjects) != '') @if(in_array('OFP 020',unserialize($requirement->open_exclude_subjects))) selected="selected" @endif @endif>Introduction to ICT</option>
                                              </select>
                                            </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Principle Pass Points') !!}
                                            {!! Form::text('principle_pass_points',$requirement->principle_pass_points,$principle_pass_points) !!}
                                          </div>
                                     
                                           <div class="form-group col-3">
                                              {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                                              {!! Form::text('subsidiary_pass_subjects',$requirement->subsidiary_pass_subjects,$min_principle_pass_points) !!}
                                            </div>
                                           <div class="form-group col-3">
                                            {!! Form::label('','No. of Principle Pass Subjects') !!}
                                            {!! Form::text('principle_pass_subjects',$requirement->principle_pass_subjects,$principle_pass_subjects) !!}
                                           </div>
                                           <div class="form-group col-3">
                                            {!! Form::label('','No. of Pass Subjects') !!}
                                            {!! Form::text('pass_subjects',$requirement->pass_subjects,$pass_subjects) !!}
                                          </div>
<!--                                          
                                          <div class="form-group col-3">
                                            {!! Form::label('','Subsidiary Subjects') !!}
                                            <select name="subsidiary_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($high_subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->subsidiary_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->subsidiary_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
-->                                       
                                           <div class="form-group col-3">
                                            {!! Form::label('','Pass Grade') !!}
                                            <select name="pass_grade" class="form-control">
                                               <option value="">Select Pass Grade</option>
                                               <option value="A" @if($requirement->pass_grade == 'A') selected="selected" @endif>A</option>
                                               <option value="B" @if($requirement->pass_grade == 'B') selected="selected" @endif>B</option>
                                               <option value="C" @if($requirement->pass_grade == 'C') selected="selected" @endif>C</option>
                                               <option value="D" @if($requirement->pass_grade == 'D') selected="selected" @endif>D</option>
                                               <option value="E" @if($requirement->pass_grade == 'E') selected="selected" @endif>E</option>
                                               <option value="F" @if($requirement->pass_grade == 'F') selected="selected" @endif>F</option>
                                            </select>
                                          </div>
                                          <div class="form-group col-3">
                                             {!! Form::label('','NTA Level') !!}
                                            <select name="nta_level" class="form-control">
                                               <option value="">Select NTA Level</option>
                                               <option value="4" @if($requirement->nta_level == 4) selected="selected" @endif>4</option>
                                               <option value="5" @if($requirement->nta_level == 5) selected="selected" @endif>5</option>
                                               <option value="6" @if($requirement->nta_level == 6) selected="selected" @endif>6</option>
                                               <option value="7" @if($requirement->nta_level == 7) selected="selected" @endif>7</option>
                                               <option value="8" @if($requirement->nta_level == 8) selected="selected" @endif>8</option>
                                               <option value="9" @if($requirement->nta_level == 9) selected="selected" @endif>9</option>
                                               <option value="10" @if($requirement->nta_level == 10) selected="selected" @endif>10</option>
                                            </select>
                                          </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Form IV Exclude Subjects') !!}
                                            <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Form IV Must Subjects') !!}
                                            <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
                                         
                                          
                                          <div class="form-group col-3">
                                            {!! Form::label('','Form IV Other Must Subjects') !!}
                                            <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
                                          
                                          <div class="form-group col-3">
                                            {!! Form::label('','Form VI Exclude Subjects') !!}
                                            <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($high_subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->advance_exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->advance_exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
                                         
                                          
                                          <div class="form-group col-3">
                                            {!! Form::label('','Form VI Must Subjects') !!}
                                            <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($high_subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->advance_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
                                          <div class="form-group col-3">
                                            {!! Form::label('','Form VI Other Must Subjects') !!}
                                            <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                               @foreach($high_subjects as $sub)
                                               <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_advance_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->other_advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                               @endforeach
                                            </select>
                                          </div>
                                            
                                        </div>
                                     </div>
                                     
                                       <div class="card-footer">
                                          <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                        </div>
                                      {!! Form::close() !!}
                              @endif

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

                      <a class="btn btn-danger btn-sm" href="#" @if($selection_run) disabled="disabled" @else data-toggle="modal" data-target="#ss-delete-requirement-{{ $requirement->id }}" @endif>
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
                <div class="ss-pagination-links">
                   {!! $entry_requirements->appends($request->except('page'))->render() !!}
                </div>
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
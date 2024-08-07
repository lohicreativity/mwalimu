<div class="card">
   <div class="card-header">
   <h3 class="card-title">{{ __('Entry Requirements') }}</h3>
   </div>
   <!-- /.card-header -->
   <div class="card-body">
      <table id="example2" class="table table-bordered table-hover ss-margin-top ss-paginated-table">
         <thead>
            <tr>
               <th>SN</th>
               <th>Programme</th>
               <th>NTA Level</th>
               <th>Pass Subjects</th>
               <th>Pass Grade</th>
               <th>Actions</th>
            </tr>
         </thead>
         <tbody>
            @foreach($entry_requirements as $key=>$requirement)
               <tr>
                  <td>{{ ($key+1)}}</td>
                  <td>{{ $requirement->campusProgram->program->name }}</td>
                  <td>@if($requirement->nta_level =='') N/A @else {{ $requirement->nta_level }} @endif</td>					
                  <td>{{ $requirement->pass_subjects }}</td>
                  <td>{{ $requirement->pass_grade }}</td>
                  <td>
                     <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-view-requirement-{{ $requirement->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              View
                     </a>

                     <div class="modal fade" id="ss-view-requirement-{{ $requirement->id }}">
                        <div class="modal-dialog modal-lg">
                           <div class="modal-content">
                              <div class="modal-header">
                                 <h4 class="modal-title">View Entry Requirement</h4>
                                 <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                                 <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                              <div class="modal-body">

                                 @if(str_contains($requirement->campusProgram->program->award->name,'Certificate'))
                                    @php
                                    
                                       $pass_subjects = [
                                          'placeholder'=>'Pass Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $pass_grade = [
                                          'placeholder'=>'Pass Grade',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $exclude_subjects = [
                                          'placeholder'=>'Exclude Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $must_subjects = [
                                          'placeholder'=>'Must Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $max_capacity = [
                                          'placeholder'=>'Max Capacity',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];
                                    @endphp

                                    {!! Form::open(['url'=>'application/entry-requirement/update','class'=>'ss-form-processing']) !!}
                                    <div class="card-body">
                                       
                                       <div class="row">
                                          <div class="form-group col-4">
                                             {!! Form::label('','Programme') !!}
                                             <select name="campus_program_id" class="form-control" required>
                                             <option value="">Select Programme</option>
                                                @foreach($campus_programs as $program)
                                                   <option value="{{ $program->id }}" @if($program->id == $requirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                          {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                          {!! Form::input('hidden','entry_requirement_id',$requirement->id) !!}

                                          <div class="form-group col-4">
                                             {!! Form::label('','Number of Pass Subjects') !!}
                                             {!! Form::text('pass_subjects',$requirement->pass_subjects,$pass_subjects) !!}
                                          </div>

                                          <div class="form-group col-4">
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
                                       
                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Exclude Subjects') !!}
                                             <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Must Subjects') !!}
                                             <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Other Must Subjects') !!}
                                             <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                       </div>

                                    </div>
                                    {!! Form::close() !!}

                                 @elseif(str_contains($requirement->campusProgram->program->award->name,'Diploma'))
                                    @php

                                       $equivalent_majors = [
                                          'placeholder'=>'Equivalent Majors',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $principle_pass_subjects = [
                                          'placeholder'=>'Principle Pass Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $subsidiary_pass_subjects = [
                                          'placeholder'=>'Subsidiary Pass Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $pass_subjects = [
                                          'placeholder'=>'Pass Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>true
                                       ];

                                       $pass_grade = [
                                          'placeholder'=>'Pass Grade',
                                          'class'=>'form-control'
                                       ];

                                       $award_level = [
                                          'placeholder'=>'Award Level',
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

                                       $max_capacity = [
                                          'placeholder'=>'Max Capacity',
                                          'class'=>'form-control'
                                       ];
                                    @endphp

                                    {!! Form::open(['url'=>'application/entry-requirement/update','class'=>'ss-form-processing']) !!}
                                    <div class="card-body">
                                       
                                       <div class="row">
                                          <div class="form-group col-6">
                                             {!! Form::label('','Programme') !!}
                                             <select name="campus_program_id" class="form-control" required>
                                                <option value="">Select Programme</option>
                                                @foreach($campus_programs as $program)
                                                   <option value="{{ $program->id }}" @if($program->id == $requirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                          <div class="form-group col-6">
                                             {!! Form::label('','Certificate Majors') !!}
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
                                                <option value="Rural Development" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Rural Development',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Rural Development</option>
                                                <option value="Information Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Management</option>
                                                <option value="Library" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Library',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Library</option>
                                                <option value="Gender" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Gender',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Gender</option>
                                                <option value="Social Studies" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Studies',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Studies</option>
                                                <option value="Business Administration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Business Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Business Administration</option>
                                                <option value="Community Development" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Community Development',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Community Development</option>
                                                <option value="Information Communication Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Communication Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Communication Technology</option>
                                                <option value="Information Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Technology</option>
                                                <option value="Computer Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Computer Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Computer Science</option>
                                                <option value="Social Work" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Work',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Work</option>
                                                <option value="Development Planning" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Development Planning',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Development Planning</option>
                                                <option value="Local Government" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Local Government',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Local Government</option>
                                                <option value="Crop Production" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Crop Production',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Crop Production</option>
                                                <option value="Agriculture Production" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Agriculture Production',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Agriculture Production</option>
                                                <option value="General Agriculture" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('General Agriculture',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>General Agriculture</option>
                                                <option value="Business Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Business Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Business Management</option>
                                                <option value="Insurance And Risk" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Insurance And Risk',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Insurance And Risk</option>
                                                <option value="Tourism" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Tourism',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Tourism</option>
                                                <option value="Hospitality" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Hospitality',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Hospitality</option>
                                             </select>
                                          </div>
                                       
                                          <div class="form-group col-4">
                                             {!! Form::label('','No. of Principle Pass Subjects') !!}
                                             {!! Form::text('principle_pass_subjects',$requirement->principle_pass_subjects,$principle_pass_subjects) !!}
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                                             {!! Form::text('subsidiary_pass_subjects',$requirement->subsidiary_pass_subjects,$subsidiary_pass_subjects) !!}
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','No. of Pass Subjects') !!}
                                             {!! Form::text('pass_subjects',$requirement->pass_subjects,$pass_subjects) !!}
                                          </div>

                                          <div class="form-group col-4">
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

                                          <div class="form-group col-4">
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

                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Exclude Subjects') !!}
                                             <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                             
                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Must Subjects') !!}
                                             <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                       
                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Other Must Subjects') !!}
                                             <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','Form VI Exclude Subjects') !!}
                                             <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->advance_exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->advance_exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                
                                                @endforeach
                                             </select>
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','Form VI Must Subjects') !!}
                                             <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->advance_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($requirement->advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','Form VI Other Must Subjects') !!}
                                             <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($requirement->other_advance_must_subjects) != '') @if(in_array($sub->subject_name, unserialize($requirement->other_advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                       </div>
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
                                                <option value="Rural Development" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Rural Development',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Rural Development</option>
                                                <option value="Information Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Management</option>
                                                <option value="Library" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Library',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Library</option>
                                                <option value="Gender" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Gender',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Gender</option>
                                                <option value="Social Studies" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Studies',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Studies</option>
                                                <option value="Business Administration" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Business Administration',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Business Administration</option>
                                                <option value="Community Development" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Community Development',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Community Development</option>
                                                <option value="Information Communication Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Communication Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Communication Technology</option>
                                                <option value="Information Technology" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Information Technology',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Information Technology</option>
                                                <option value="Computer Science" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Computer Science',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Computer Science</option>
                                                <option value="Social Work" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Social Work',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Social Work</option>
                                                <option value="Development Planning" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Development Planning',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Development Planning</option>
                                                <option value="Local Government" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Local Government',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Local Government</option>
                                                <option value="Crop Production" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Crop Production',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Crop Production</option>
                                                <option value="Agriculture Production" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Agriculture Production',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Agriculture Production</option>
                                                <option value="General Agriculture" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('General Agriculture',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>General Agriculture</option>
                                                <option value="Business Management" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Business Management',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Business Management</option>
                                                <option value="Insurance And Risk" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Insurance And Risk',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Insurance And Risk</option>
                                                <option value="Tourism" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Tourism',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Tourism</option>
                                                <option value="Hospitality" @if(unserialize($requirement->equivalent_majors) != '') @if(in_array('Hospitality',unserialize($requirement->equivalent_majors))) selected="selected" @endif @endif>Hospitality</option>
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
                     @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admission-officer'))  
                           <a class="btn btn-info btn-sm" href="#"
                              data-toggle="modal"
                              wire:click="fetchEntryRequirement({{$requirement}})"
                              data-target="#ss-edit-requirement">
                              <i class="fas fa-pencil-alt"></i> Edit
                           </a>                                

                     <div wire:ignore.self class="modal fade" id="ss-edit-requirement">
                        <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                           <div class="modal-header">
                              <h4 class="modal-title">Edit Entry Requirement</h4>
                              <button amount="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                              </button>
                           </div>
                           <div class="modal-body">
                              @if(!empty($selectedEntryRequirement))
                                 @if(str_contains($selectedEntryRequirement->campusProgram->program->award->name,'Certificate'))
                                    @php
                                       $equivalent_gpa = [
                                          'placeholder'=>'Equivalent GPA',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $equivalent_majors = [
                                          'placeholder'=>'Equivalent Majors',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $equivalent_average_grade = [
                                          'placeholder'=>'Equivalent Average Grade',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $open_equivalent_gpa = [
                                          'placeholder'=>'Open Equivalent GPA',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $open_equivalent_majors = [
                                          'placeholder'=>'Open Equivalent Majors',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $open_equivalent_average_grade = [
                                          'placeholder'=>'Open Equivalent Average Grade',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $principle_pass_points = [
                                          'placeholder'=>'Principle Pass Points',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $principle_pass_subjects = [
                                          'placeholder'=>'Principle Pass Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>false
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
                                          'readonly'=>false
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
                                          'readonly'=>false
                                       ];

                                       $principle_subjects = [
                                          'placeholder'=>'Principle Subjects',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $max_capacity = [
                                          'placeholder'=>'Max Capacity',
                                          'class'=>'form-control'
                                       ];
                                    @endphp

                                    {!! Form::open(['url'=>'application/entry-requirement/update','class'=>'ss-form-processing']) !!}
                                    <div class="card-body">
                                       
                                       <div class="row">
                                       <div class="form-group col-4">
                                             {!! Form::label('','Programme') !!}
                                             <select name="campus_program_id" class="form-control" required>
                                             <option value="">Select Programme</option>
                                             @foreach($campus_programs as $program)
                                             <option value="{{ $program->id }}" @if($program->id == $selectedEntryRequirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                             @endforeach
                                             </select>
                                          </div>

                                       {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                       {!! Form::input('hidden','entry_requirement_id',$selectedEntryRequirement->id) !!}
                                       
                                          <div class="form-group col-4">
                                          {!! Form::label('','Number of Pass Subjects') !!}
                                          {!! Form::text('pass_subjects',$selectedEntryRequirement->pass_subjects,$pass_subjects) !!}
                                       </div>
                                       <div class="form-group col-4">
                                          {!! Form::label('','Pass Grade') !!}
                                          <select name="pass_grade" class="form-control">
                                             <option value="A" @if($selectedEntryRequirement->pass_grade == 'A') selected="selected" @endif>A</option>
                                                <option value="B" @if($selectedEntryRequirement->pass_grade == 'B') selected="selected" @endif>B</option>
                                                <option value="C" @if($selectedEntryRequirement->pass_grade == 'C') selected="selected" @endif>C</option>
                                                <option value="D" @if($selectedEntryRequirement->pass_grade == 'D') selected="selected" @endif>D</option>
                                                <option value="E" @if($selectedEntryRequirement->pass_grade == 'E') selected="selected" @endif>E</option>
                                                <option value="F" @if($selectedEntryRequirement->pass_grade == 'F') selected="selected" @endif>F</option>
                                          </select>
                                       </div>
                                       
                                       
                                       <div class="form-group col-4">
                                          {!! Form::label('','Form IV Exclude Subjects') !!}
                                          <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                       </div>
                                       <div class="form-group col-4">
                                          {!! Form::label('','Form IV Must Subjects') !!}
                                          <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                       </div>
                                       <div class="form-group col-4">
                                          {!! Form::label('','Form IV Other Must Subjects') !!}
                                          <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                             @foreach($subjects as $sub)
                                             <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                             @endforeach
                                          </select>
                                       </div>
                                       </div>

                                    </div>
                                    
                                    <div class="card-footer">
                                       <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                       </div>
                                    {!! Form::close() !!}
                                 @elseif(str_contains($selectedEntryRequirement->campusProgram->program->award->name,'Diploma'))
                                    @php
                                       $equivalent_gpa = [
                                          'placeholder'=>'Equivalent GPA',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $equivalent_majors = [
                                          'placeholder'=>'Equivalent Majors',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $equivalent_average_grade = [
                                          'placeholder'=>'Equivalent Average Grade',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $open_equivalent_gpa = [
                                          'placeholder'=>'Open Equivalent GPA',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $open_equivalent_majors = [
                                          'placeholder'=>'Open Equivalent Majors',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $open_equivalent_average_grade = [
                                          'placeholder'=>'Open Equivalent Average Grade',
                                          'class'=>'form-control',
                                          'readonly'=>false
                                       ];

                                       $principle_pass_points = [
                                          'placeholder'=>'Principle Pass Points',
                                          'class'=>'form-control',
                                          'readonly'=>false
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
                                          <div class="form-group col-6">
                                             {!! Form::label('','Programme') !!}
                                             <select name="campus_program_id" class="form-control" required>
                                             <option value="">Select Programme</option>
                                             @foreach($campus_programs as $program)
                                             <option value="{{ $program->id }}" @if($program->id == $selectedEntryRequirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                             @endforeach
                                             </select>
                                          </div>
                                          <div class="form-group col-6">
                                             {!! Form::label('','Certificate Majors') !!}
                                             <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple">
                                                {{--
                                                @foreach($diploma_programs as $prog)

                                                <option value="{{ substr($prog->name,20) }}" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array(substr($prog->name,20),unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>{{ substr($prog->name,20) }}</option>
                                                @endforeach

                                                @foreach($diploma_programs as $prog)
                                                <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                                                @endforeach
                                                --}}
                                                <option value="Marketing" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Marketing',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Marketing</option>
                                                <option value="Financial Administration" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Financial Administration',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Financial Administration</option>
                                                <option value="Accountancy" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Accountancy',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Accountancy</option>
                                                <option value="Finance" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Finance',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Finance</option>
                                                <option value="Nursing" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Nursing',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Nursing</option>
                                                <option value="Youth" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Youth',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Youth</option>
                                                <option value="Clinical Science" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Clinical Science',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Clinical Science</option>
                                                <option value="Police Science" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Police Science',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Police Science</option>
                                                <option value="International Relations" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('International Relations',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>International Relations</option>
                                                <option value="Diplomacy" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Diplomacy',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Diplomacy</option>
                                                <option value="Counselling" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Counselling',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Counselling</option>
                                                <option value="Psychology" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Psychology',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Psychology</option>
                                                <option value="Law" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Law',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Law</option>
                                                <option value="Secretarial Studies" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Secretarial Studies',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Secretarial Studies</option>
                                                <option value="Office Management" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Office Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Office Management</option>
                                                <option value="Public Administration" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Public Administration',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Public Administration</option>
                                                <option value="Journalism" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Journalism',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Journalism</option>
                                                <option value="Education" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Education',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Education</option>
                                                <option value="Economics" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Economics',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Economics</option>
                                                <option value="Procurement" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Procurement',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Procurement</option>
                                                <option value="Human Resource" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Human Resource',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Human Resource</option>
                                                <option value="Records Management" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Records Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Records Management</option>
                                                <option value="Archives" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Archives',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Archives</option>
                                                <option value="Rural Development" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Rural Development',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Rural Development</option>
                                                <option value="Information Management" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Information Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Information Management</option>
                                                <option value="Library" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Library',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Library</option>
                                                <option value="Gender" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Gender',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Gender</option>
                                                <option value="Social Studies" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Social Studies',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Social Studies</option>
                                                <option value="Business Administration" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Business Administration',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Business Administration</option>
                                                <option value="Community Development" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Community Development',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Community Development</option>
                                                <option value="Information Communication Technology" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Information Communication Technology',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Information Communication Technology</option>
                                                <option value="Information Technology" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Information Technology',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Information Technology</option>
                                                <option value="Computer Science" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Computer Science',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Computer Science</option>
                                                <option value="Social Work" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Social Work',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Social Work</option>
                                                <option value="Development Planning" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Development Planning',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Development Planning</option>
                                                <option value="Local Government" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Local Government',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Local Government</option>
                                                <option value="Crop Production" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Crop Production',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Crop Production</option>
                                                <option value="Agriculture Production" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Agriculture Production',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Agriculture Production</option>   
                                                <option value="General Agriculture" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('General Agriculture',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>General Agriculture</option>
                                                <option value="Business Management" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Business Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Business Management</option>
                                                <option value="Insurance And Risk" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Insurance And Risk',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Insurance And Risk</option>
                                                <option value="Tourism" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Tourism',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Tourism</option>
                                                <option value="Hospitality" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Hospitality',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Hospitality</option>
                                                </select>
                                          </div>

                                          {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                          {!! Form::input('hidden','entry_requirement_id',$selectedEntryRequirement->id) !!}
                                          
                                          <div class="form-group col-4">
                                             {!! Form::label('','No. of Principle Pass Subjects') !!}
                                             {!! Form::text('principle_pass_subjects',$selectedEntryRequirement->principle_pass_subjects,$principle_pass_subjects) !!}
                                          </div>
                                          <div class="form-group col-4">
                                             {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                                             {!! Form::text('subsidiary_pass_subjects',$selectedEntryRequirement->subsidiary_pass_subjects,$subsidiary_pass_subjects) !!}
                                          </div>
                                          <div class="form-group col-4">
                                             {!! Form::label('','No. of Pass Subjects') !!}
                                             {!! Form::text('pass_subjects',$selectedEntryRequirement->pass_subjects,$pass_subjects) !!}
                                          </div>                                        
                                          
                                          <div class="form-group col-4">
                                             {!! Form::label('','Pass Grade') !!}
                                             <select name="pass_grade" class="form-control">
                                                <option value="">Select Pass Grade</option>
                                                <option value="A" @if($selectedEntryRequirement->pass_grade == 'A') selected="selected" @endif>A</option>
                                                <option value="B" @if($selectedEntryRequirement->pass_grade == 'B') selected="selected" @endif>B</option>
                                                <option value="C" @if($selectedEntryRequirement->pass_grade == 'C') selected="selected" @endif>C</option>
                                                <option value="D" @if($selectedEntryRequirement->pass_grade == 'D') selected="selected" @endif>D</option>
                                                <option value="E" @if($selectedEntryRequirement->pass_grade == 'E') selected="selected" @endif>E</option>
                                                <option value="F" @if($selectedEntryRequirement->pass_grade == 'F') selected="selected" @endif>F</option>
                                             </select>
                                          </div>
                                          <div class="form-group col-4">
                                             {!! Form::label('','NTA Level') !!}
                                             <select name="nta_level" class="form-control">
                                                <option value="">Select NTA Level</option>
                                                <option value="4" @if($selectedEntryRequirement->nta_level == 4) selected="selected" @endif>4</option>
                                                <option value="5" @if($selectedEntryRequirement->nta_level == 5) selected="selected" @endif>5</option>
                                                <option value="6" @if($selectedEntryRequirement->nta_level == 6) selected="selected" @endif>6</option>
                                                <option value="7" @if($selectedEntryRequirement->nta_level == 7) selected="selected" @endif>7</option>
                                                <option value="8" @if($selectedEntryRequirement->nta_level == 8) selected="selected" @endif>8</option>
                                                <option value="9" @if($selectedEntryRequirement->nta_level == 9) selected="selected" @endif>9</option>
                                                <option value="10" @if($selectedEntryRequirement->nta_level == 10) selected="selected" @endif>10</option>
                                             </select>
                                          </div>
                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Exclude Subjects') !!}
                                             <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="form-group col-4">
                                             {!! Form::label('','Form IV Must Subjects') !!}
                                             <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          
                                          
                                             <div class="form-group col-4">
                                             {!! Form::label('','Form IV Other Must Subjects') !!}
                                             <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="form-group col-4">
                                             {!! Form::label('','Form VI Exclude Subjects') !!}
                                             <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->advance_exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->advance_exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="form-group col-4">
                                             {!! Form::label('','Form VI Must Subjects') !!}
                                             <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->advance_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>

                                          <div class="form-group col-4">
                                             {!! Form::label('','Form VI Other Must Subjects') !!}
                                             <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->other_advance_must_subjects) != '') @if(in_array($sub->subject_name, unserialize($selectedEntryRequirement->other_advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                    
                                       </div>
                                    </div>
                                    
                                       <div class="card-footer">
                                          <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                       </div>
                                       {!! Form::close() !!}
                                 @elseif(str_contains($selectedEntryRequirement->campusProgram->program->award->name,'Bachelor'))
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
                                                <option value="{{ $program->id }}" @if($program->id == $selectedEntryRequirement->campus_program_id) selected="selected" @else disabled="disabled" @endif>{{ $program->program->name }}</option>
                                                @endforeach
                                             </select>
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Equivalent GPA') !!}
                                             {!! Form::text('equivalent_gpa',$selectedEntryRequirement->equivalent_gpa,$equivalent_gpa) !!}
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Min Equivalent GPA') !!}
                                             {!! Form::text('min_equivalent_gpa',$selectedEntryRequirement->min_equivalent_gpa,$min_equivalent_gpa) !!}
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Equivalent Majors') !!}
                                             <select name="equivalent_majors[]" class="form-control ss-select-tags" multiple="multiple">
                                                {{--
                                                @foreach($diploma_programs as $prog)
                                                <option value="{{ substr($prog->name,20) }}" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array(substr($prog->name,20),unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>{{ substr($prog->name,20) }}</option>
                                                @endforeach

                                                @foreach($diploma_programs as $prog)
                                                <option value="{{ substr($prog->name,20) }}">{{ substr($prog->name,20) }}</option>
                                                @endforeach
                                                --}}
                                                <option value="Marketing" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Marketing',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Marketing</option>
                                                <option value="Financial Administration" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Financial Administration',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Financial Administration</option>
                                                <option value="Accountancy" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Accountancy',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Accountancy</option>
                                                <option value="Finance" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Finance',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Finance</option>
                                                <option value="Nursing" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Nursing',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Nursing</option>
                                                <option value="Youth" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Youth',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Youth</option>
                                                <option value="Clinical Science" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Clinical Science',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Clinical Science</option>
                                                <option value="Police Science" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Police Science',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Police Science</option>
                                                <option value="International Relations" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('International Relations',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>International Relations</option>
                                                <option value="Diplomacy" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Diplomacy',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Diplomacy</option>
                                                <option value="Counselling" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Counselling',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Counselling</option>
                                                <option value="Psychology" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Psychology',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Psychology</option>
                                                <option value="Law" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Law',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Law</option>
                                                <option value="Secretarial Studies" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Secretarial Studies',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Secretarial Studies</option>
                                                <option value="Office Management" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Office Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Office Management</option>
                                                <option value="Public Administration" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Public Administration',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Public Administration</option>
                                                <option value="Journalism" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Journalism',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Journalism</option>
                                                <option value="Education" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Education',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Education</option>
                                                <option value="Economics" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Economics',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Economics</option>
                                                <option value="Procurement" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Procurement',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Procurement</option>
                                                <option value="Human Resource" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Human Resource',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Human Resource</option>
                                                <option value="Records Management" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Records Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Records Management</option>
                                                <option value="Archives" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Archives',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Archives</option>
                                                <option value="Rural Development" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Rural Development',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Rural Development</option>
                                                <option value="Information Management" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Information Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Information Management</option>
                                                <option value="Library" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Library',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Library</option>
                                                <option value="Gender" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Gender',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Gender</option>
                                                <option value="Social Studies" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Social Studies',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Social Studies</option>
                                                <option value="Business Administration" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Business Administration',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Business Administration</option>
                                                <option value="Community Development" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Community Development',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Community Development</option>
                                                <option value="Information Communication Technology" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Information Communication Technology',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Information Communication Technology</option>
                                                <option value="Information Technology" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Information Technology',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Information Technology</option>
                                                <option value="Computer Science" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Computer Science',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Computer Science</option>
                                                <option value="Social Work" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Social Work',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Social Work</option>
                                                <option value="Development Planning" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Development Planning',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Development Planning</option>
                                                <option value="Local Government" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Local Government',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Local Government</option>
                                                <option value="Crop Production" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Crop Production',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Crop Production</option>
                                                <option value="Agriculture Production" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Agriculture Production',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Agriculture Production</option>
                                                <option value="General Agriculture" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('General Agriculture',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>General Agriculture</option>
                                                <option value="Business Management"@if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Business Management',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Business Management</option>
                                                <option value="Insurance And Risk"@if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Insurance And Risk',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Insurance And Risk</option>
                                                <option value="Tourism" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Tourism',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Tourism</option>
                                                <option value="Hospitality" @if(unserialize($selectedEntryRequirement->equivalent_majors) != '') @if(in_array('Hospitality',unserialize($selectedEntryRequirement->equivalent_majors))) selected="selected" @endif @endif>Hospitality</option>
                                                </select>
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Equivalent Average Grade') !!}
                                             <select name="equivalent_average_grade" class="form-control">
                                                <option value="">Select Pass Grade</option>
                                                <option value="A" @if($selectedEntryRequirement->equivalent_average_grade == 'A') selected="selected" @endif>A</option>
                                                <option value="B" @if($selectedEntryRequirement->equivalent_average_grade == 'B') selected="selected" @endif>B</option>
                                                <option value="C" @if($selectedEntryRequirement->equivalent_average_grade == 'C') selected="selected" @endif>C</option>
                                                <option value="D" @if($selectedEntryRequirement->equivalent_average_grade == 'D') selected="selected" @endif>D</option>
                                                <option value="E" @if($selectedEntryRequirement->equivalent_average_grade == 'E') selected="selected" @endif>E</option>
                                                <option value="F" @if($selectedEntryRequirement->equivalent_average_grade == 'F') selected="selected" @endif>F</option>
                                             </select>
                                             </div>
                                             {!! Form::input('hidden','application_window_id',$application_window->id) !!}
                                             {!! Form::input('hidden','entry_requirement_id',$selectedEntryRequirement->id) !!}
                                             <div class="form-group col-3">
                                             {!! Form::label('','Equivalent Must Subjects') !!}
                                             <select name="equivalent_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                <option value="ENGLISH" @if(unserialize($selectedEntryRequirement->equivalent_must_subjects) != '') @if(in_array('ENGLISH',unserialize($selectedEntryRequirement->equivalent_must_subjects))) selected="selected" @endif @endif>English</option>
                                                <option value="KISWAHILI" @if(unserialize($selectedEntryRequirement->equivalent_must_subjects) != '') @if(in_array('KISWAHILI',unserialize($selectedEntryRequirement->equivalent_must_subjects))) selected="selected" @endif @endif>Kiswahili</option>
                                                <option value="GEOGRAPHY" @if(unserialize($selectedEntryRequirement->equivalent_must_subjects) != '') @if(in_array('GEOGRAPHY',unserialize($selectedEntryRequirement->equivalent_must_subjects))) selected="selected" @endif @endif>Geography</option>
                                                <option value="HISTORY" @if(unserialize($selectedEntryRequirement->equivalent_must_subjects) != '') @if(in_array('HISTORY',unserialize($selectedEntryRequirement->equivalent_must_subjects))) selected="selected" @endif @endif>History</option>
                                             </select>
                                             </div>
                                          
                                             <div class="form-group col-3">
                                             {!! Form::label('','Open Equivalent GPA') !!}
                                             {!! Form::text('open_equivalent_gpa',$selectedEntryRequirement->open_equivalent_gpa,$open_equivalent_gpa) !!}
                                             </div>
                                             <div class="form-group col-3">
                                                {!! Form::label('','Open Exclude Subjects') !!}
                                                <select name="open_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                   <option value="OFC 017" @if(unserialize($selectedEntryRequirement->open_exclude_subjects) != '') @if(in_array('OFP 018',unserialize($selectedEntryRequirement->open_exclude_subjects))) selected="selected" @endif @endif>Communication Skills</option>
                                                   <option value="OFC 017" @if(unserialize($selectedEntryRequirement->open_exclude_subjects) != '') @if(in_array('OFP 018',unserialize($selectedEntryRequirement->open_exclude_subjects))) selected="selected" @endif @endif>Development Studies</option>
                                                   <option value="OFP 020" @if(unserialize($selectedEntryRequirement->open_exclude_subjects) != '') @if(in_array('OFP 020',unserialize($selectedEntryRequirement->open_exclude_subjects))) selected="selected" @endif @endif>Introduction to ICT</option>
                                                </select>
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Principle Pass Points') !!}
                                             {!! Form::text('principle_pass_points',$selectedEntryRequirement->principle_pass_points,$principle_pass_points) !!}
                                             </div>
                                       
                                             <div class="form-group col-3">
                                                {!! Form::label('','No. of Subsidiary Pass Subjects') !!}
                                                {!! Form::text('subsidiary_pass_subjects',$selectedEntryRequirement->subsidiary_pass_subjects,$min_principle_pass_points) !!}
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','No. of Principle Pass Subjects') !!}
                                             {!! Form::text('principle_pass_subjects',$selectedEntryRequirement->principle_pass_subjects,$principle_pass_subjects) !!}
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','No. of Pass Subjects') !!}
                                             {!! Form::text('pass_subjects',$selectedEntryRequirement->pass_subjects,$pass_subjects) !!}
                                             </div>                                    
                                             <div class="form-group col-3">
                                             {!! Form::label('','Pass Grade') !!}
                                             <select name="pass_grade" class="form-control">
                                                <option value="">Select Pass Grade</option>
                                                <option value="A" @if($selectedEntryRequirement->pass_grade == 'A') selected="selected" @endif>A</option>
                                                <option value="B" @if($selectedEntryRequirement->pass_grade == 'B') selected="selected" @endif>B</option>
                                                <option value="C" @if($selectedEntryRequirement->pass_grade == 'C') selected="selected" @endif>C</option>
                                                <option value="D" @if($selectedEntryRequirement->pass_grade == 'D') selected="selected" @endif>D</option>
                                                <option value="E" @if($selectedEntryRequirement->pass_grade == 'E') selected="selected" @endif>E</option>
                                                <option value="F" @if($selectedEntryRequirement->pass_grade == 'F') selected="selected" @endif>F</option>
                                             </select>
                                             </div>
                                             <div class="form-group col-3">
                                                {!! Form::label('','NTA Level') !!}
                                             <select name="nta_level" class="form-control">
                                                <option value="">Select NTA Level</option>
                                                <option value="4" @if($selectedEntryRequirement->nta_level == 4) selected="selected" @endif>4</option>
                                                <option value="5" @if($selectedEntryRequirement->nta_level == 5) selected="selected" @endif>5</option>
                                                <option value="6" @if($selectedEntryRequirement->nta_level == 6) selected="selected" @endif>6</option>
                                                <option value="7" @if($selectedEntryRequirement->nta_level == 7) selected="selected" @endif>7</option>
                                                <option value="8" @if($selectedEntryRequirement->nta_level == 8) selected="selected" @endif>8</option>
                                                <option value="9" @if($selectedEntryRequirement->nta_level == 9) selected="selected" @endif>9</option>
                                                <option value="10" @if($selectedEntryRequirement->nta_level == 10) selected="selected" @endif>10</option>
                                             </select>
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Form IV Exclude Subjects') !!}
                                             <select name="exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Form IV Must Subjects') !!}
                                             <select name="must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                             </div>
                                          
                                             
                                             <div class="form-group col-3">
                                             {!! Form::label('','Form IV Other Must Subjects') !!}
                                             <select name="other_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->other_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->other_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                             </div>
                                             
                                             <div class="form-group col-3">
                                             {!! Form::label('','Form VI Exclude Subjects') !!}
                                             <select name="advance_exclude_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->advance_exclude_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->advance_exclude_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                             </div>
                                          
                                             
                                             <div class="form-group col-3">
                                             {!! Form::label('','Form VI Must Subjects') !!}
                                             <select name="advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->advance_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
                                                @endforeach
                                             </select>
                                             </div>
                                             <div class="form-group col-3">
                                             {!! Form::label('','Form VI Other Must Subjects') !!}
                                             <select name="other_advance_must_subjects[]" class="form-control ss-select-tags" multiple="multiple">
                                                @foreach($high_subjects as $sub)
                                                <option value="{{ $sub->subject_name }}" @if(unserialize($selectedEntryRequirement->other_advance_must_subjects) != '') @if(in_array($sub->subject_name,unserialize($selectedEntryRequirement->other_advance_must_subjects))) selected="selected" @endif @endif>{{ $sub->subject_name }}</option>
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
                                    @if(!$prog_selection_status)
                                       <p id="ss-confirmation-text">Are you sure you want to delete this entry requirement from the list?</p>
                                       <div class="ss-form-controls">
                                       <button amount="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                       <a href="{{ url('application/entry-requirement/'.$requirement->id.'/destroy') }}" class="btn btn-danger">Delete</a>
                                       </div><!-- end of ss-form-controls -->
                                    </div><!-- end of ss-confirmation-container -->
                                    @else
                                       <p id="ss-confirmation-text">Entry requirement cannot be deleted because it has already been used for programme selection.</p>
                                    @endif
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
                     @endif
                  </td>
               </tr>
            @endforeach
         
         </tbody>
      </table>
   </div>
</div>
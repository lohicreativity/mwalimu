<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>{{ Config::get('constants.SITE_NAME') }}</title>
  <style type="text/css">
      
    body{
       font-family: helvetica;
    }
   .container {
      padding-right: 15px;
      padding-left: 15px;
      margin-right: auto;
      margin-left: auto;
    }
    .container-fluid {
      padding-right: 15px;
      padding-left: 15px;
      margin-right: auto;
      margin-left: auto;
    }
    .row {
      margin-right: -15px;
      margin-left: -15px;
    }
    .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12 {
      float: left;
    }
    .col-md-12 {
      width: 100%;
    }
    .col-md-11 {
      width: 91.66666667%;
    }
    .col-md-10 {
      width: 83.33333333%;
    }
    .col-md-9 {
      width: 75%;
    }
    .col-md-8 {
      width: 66.66666667%;
    }
    .col-md-7 {
      width: 58.33333333%;
    }
    .col-md-6 {
      width: 50%;
    }
    .col-md-5 {
      width: 41.66666667%;
    }
    .col-md-4 {
      width: 33.33333333%;
    }
    .col-md-3 {
      width: 25%;
    }
    .col-md-2 {
      width: 16.66666667%;
    }
    .col-md-1 {
      width: 8.33333333%;
    }
   table {
      background-color: transparent;
    }
    caption {
      padding-top: 8px;
      padding-bottom: 8px;
      color: #777;
      text-align: left;
    }
    th {
      text-align: left;
    }
    .table {
      width: 100%;
      max-width: 100%;
      margin-bottom: 20px;
      border-collapse: collapse;
    }
    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tfoot > tr > th,
    .table > thead > tr > td,
    .table > tbody > tr > td,
    .table > tfoot > tr > td {
      padding: 2px;
      line-height: 1.42857143;
      vertical-align: top;
      border-top: 1px solid #000000;
    }
    .table > thead > tr > th {
      vertical-align: bottom;
      border-bottom: 2px solid #000000;
    }
    .table > caption + thead > tr:first-child > th,
    .table > colgroup + thead > tr:first-child > th,
    .table > thead:first-child > tr:first-child > th,
    .table > caption + thead > tr:first-child > td,
    .table > colgroup + thead > tr:first-child > td,
    .table > thead:first-child > tr:first-child > td {
      border-top: 0;
    }
    .table > tbody + tbody {
      border-top: 2px solid #000000;
    }
    .table .table {
      background-color: #fff;
    }
    .table-condensed > thead > tr > th,
    .table-condensed > tbody > tr > th,
    .table-condensed > tfoot > tr > th,
    .table-condensed > thead > tr > td,
    .table-condensed > tbody > tr > td,
    .table-condensed > tfoot > tr > td {
      padding: 2px;
    }
    .table-bordered {
      border: 1px solid #000000;
    }
    .table-bordered > thead > tr > th,
    .table-bordered > tbody > tr > th,
    .table-bordered > tfoot > tr > th,
    .table-bordered > thead > tr > td,
    .table-bordered > tbody > tr > td,
    .table-bordered > tfoot > tr > td {
      border: 1px solid #000000;
    }
    .table-bordered > thead > tr > th,
    .table-bordered > thead > tr > td {
      border-bottom-width: 2px;
    }


   .page-break {
        page-break-after: always;
    }
    .ss-bold{
       font-weight: bold;
    }
    .ss-center{
       text-align: center;
    }
    .ss-left{
       text-align: left;
    }
    .ss-right{
       text-align: right;
    }
    .ss-italic{
      font-style: italic;
    }
   .ss-no-margin{
      margin: 0px;
   }
   .ss-margin-top-lg{
       margin-top: 80px;
    } 
   .ss-margin-bottom-lg{
       margin-bottom: 50px;
   }
   .ss-margin-bottom{
       margin-bottom: 20px;
   }
   .ss-font-sm{
      font-size: 9px;
   }
   .ss-font-xs{
      font-size: 8px;
   }
   .ss-letter-head{
      /*margin-bottom: 20px;*/
      /*text-align: right;*/
      text-transform: uppercase;
   }
   .ss-letter-head h1, .ss-letter-head h3{
      margin: 0px;
   }
   .ss-sign-column{
      min-width: 120px !important;
   }
   .ss-black-line{
     width: 100%;
     height: 5px;
     background-color: #000;
   }
   .ss-color-danger{
       color: #dc3545;
    }
    .ss-color-info{
       color: #17a2b8;
    }
    .ss-custom-lightblue{
       background-color: lightblue;
    }
    .ss-custom-grey{
       background-color: #898989;
    }
</style>
</head>

      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="ss-letter-head  ss-center">
              <span class="ss-bold" style="font-size:8pt">THE MWALIMU NYERERE MEMORIAL ACADEMY</span> <br>
              <img src="{{ asset('dist/img/logo.png') }}" alt="Config::get('constants.SITE_NAME') }}" class="ss-logo" width="5%">				<br>   
              <span class="ss-bold" style="font-size:7pt">{{ $campus->name }}</span> <br>
              <span class="ss-bold" style="font-size:7pt">{{ $department->name }}</span> <br>
              <span class="ss-bold" style="font-size:7pt">{{ $program->name }} (YEAR {{ $year_of_study }} - {{ strtoupper(substr($intake->name,0,3)) }}) - {{ $study_academic_year->academicYear->year }}</span>
              <p style="font-size:7pt">@if($semester) {{ strtoupper($semester->name) }} @endif SUPPLEMENTARY/CARRY/SPECIAL EXAMINATION RESULTS <span style="font-weight:normal">(CA Weight {{ (round($module_assignments[0]->programModuleAssignment->course_work_min_mark,0)) }}%, FE Weight {{(round($module_assignments[0]->programModuleAssignment->final_min_mark,0))}}%)</span> </p> 
            </div>
               <div class="table-responsive ss-margin-bottom">
                  <table class="table table-condensed table-bordered">
                    <thead>
                      <tr>
                        <th class="ss-bold ss-font-xs" rowspan="3">SN</th>
                        @if($request->get('reg_display_type') == 'SHOW')<th  class="ss-bold ss-font-xs" rowspan="3">REGISTRATION NUMBER</th>@endif
                        @if($request->get('name_display_type') == 'SHOW')<th  class="ss-bold ss-font-xs" rowspan="3">NAME</th>@endif
                        @if($request->get('gender_display_type') == 'SHOW')<th  class="ss-bold ss-font-xs" rowspan="3">SEX</th>@endif
                        @foreach($module_assignments as $assignment)
                        <th class="ss-bold ss-font-xs" colspan="4">{{ $assignment->module->code }} ({{ $assignment->module->credit }})</th>
                        @endforeach
                        <th colspan="5"></th>
                      <tr>
                      <tr>
                        @foreach($module_assignments as $assignment)
                        <th class="ss-bold ss-font-xs">CA</th>
                        <th class="ss-bold ss-font-xs">FE</th>
                        <th class="ss-bold ss-font-xs">TT</th>
                        <th class="ss-bold ss-font-xs">GD</th>
                        @endforeach
                        
                        <th class="ss-center ss-bold ss-font-xs">GPA</th>
                        <th class="ss-center ss-bold ss-font-xs"> PTS</th>
                        <th class="ss-center ss-bold ss-font-xs">CRD</th>
                        <th class="ss-bold ss-font-xs">REMARK</th>
                        <th class="ss-bold ss-font-xs">CLASSIFICATION</th>
                      </tr>
                    </thead>
                    <tbody>
                    
                    @php
                         $count = 1;
                        $male_postponement_cases = $female_postponement_cases = $male_upsecond_class_cases = $female_upsecond_class_cases = $male_first_class_cases = $female_first_class_cases =
                        $male_disco_cases = $female_disco_cases = $male_retake_cases = $female_retake_cases = $male_carry_cases = $female_carry_cases = $male_incomplete_cases = $female_incomplete_cases =
                        $male_failed_cases = $female_failed_cases = $female_pass_cases = $male_pass_cases = $female_lwsecond_class_cases = $male_lwsecond_class_cases = $female_second_class_cases = $male_second_class_cases = 0;
                    @endphp

                    @foreach($students as $key=>$student)
                      @php 
                        $display_student = false;
                        foreach($sem_modules as $mdKey=>$mods){
                          foreach($mods as $assignment){
                            foreach($student->examinationResults as $result){
                              if($result->module_assignment_id == $assignment->id){
                                if(!is_null($result->supp_score)){
                                  
                                    $display_student = true;
                                }else{
                                  foreach($result->moduleAssignment->specialExams as $ex){
                                    if(count($result->moduleAssignment->specialExams) != 0 && $ex->student_id == $student->id){ 
                                      $display_student = true; 
                                
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }
                      @endphp

                    @if($display_student)
                    <tr>
                      <td class="ss-font-xs">{{ $count }}</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td class="ss-font-xs">{{ $student->registration_number }}</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td class="ss-font-xs">{{ $student->surname }}, {{ ucwords(strtolower($student->first_name))  }} {{ substr($student->middle_name, 1, 1)}}</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td class="ss-font-xs">{{ $student->gender }}</td>
                      @endif
                         
                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)
                          
                            <td class="ss-center ss-font-xs">
                                @foreach($student->examinationResults as $result)
                                  @if($result->module_assignment_id == $assignment->id)
                                    @if(!is_null($result->supp_remark))
                                      N/A
                                    @else
                                      @if($assignment->module->course_work_based == 1)
                                        @if($result->course_work_score) {{ round($result->course_work_score) }} @else - @endif
                                      @else
                                        N/A
                                      @endif
                                    @endif
                                  @endif
                                @endforeach
                            </td>
                            <td class="ss-center ss-font-xs">
                              @foreach($student->examinationResults as $result)
                                @if($result->module_assignment_id == $assignment->id)
                                   @if(!is_null($result->supp_remark))
                                    N/A
                                   @else
                                    @if($result->final_score && ($result->final_remark == 'FAIL' || $result->final_remark == 'PASS'))
                                      {{ round($result->final_score) }} 
                         
                                    @else - @endif
                                   @endif
                                @endif
                              @endforeach
                           </td>
                            <td class="ss-center ss-font-xs">
                               @foreach($student->examinationResults as $result)
                                 @if($result->module_assignment_id == $assignment->id)
                                    @if($result->supp_score)
                                      @if($result->supp_score) {{ round($result->supp_score) }} @else {{ $result->supp_score }} @endif
                                    @else
                                      @if($result->total_score) {{ round($result->total_score) }} @else {{ $result->total_score }} @endif
                                    @endif
                                 @endif
                               @endforeach
                            </td>
                            <td class="ss-center ss-font-xs">
                               @foreach($student->examinationResults as $result)
                                 @if($result->module_assignment_id == $assignment->id)
                                    @if($result->supp_score)
                                      {{ $result->grade }} @if($result->grade == 'C')*@endif
                                    @else
                                      {{ $result->grade }}
                                    @endif
                                 @endif
                               @endforeach
                            </td>

                         @endforeach
                         <td class="ss-center ss-font-xs">
                            @if(count($student->semesterRemarks) != 0)   
                              @if($student->semesterRemarks[0]->gpa) {{ bcdiv($student->semesterRemarks[0]->gpa,1,1) }} @else - @endif 
                            @endif
                          </td>
                          <td class="ss-center ss-font-xs">
                            @if(count($student->semesterRemarks) != 0)   
                              @if($student->semesterRemarks[0]->gpa) {{ $student->semesterRemarks[0]->point }} @else - @endif 
                            @endif
                          </td>
                          <td class="ss-center ss-font-xs">
                            @if(count($student->semesterRemarks) != 0)   
                              @if($student->semesterRemarks[0]->gpa) {{ $student->semesterRemarks[0]->credit }} @else - @endif 
                            @endif
                          </td>
                          @foreach($student->semesterRemarks as $rem)
                            @if($rem->semester->name == $mdKey)
                            <td class="ss-center ss-font-xs">
                              {{ $rem->supp_remark }}
                            </td>
                            @endif
                          @endforeach
                          <td class="ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                            @if($student->semesterRemarks[0]->class) {{ strtoupper($student->semesterRemarks[0]->class) }} @else 
                              @if($student->semesterRemarks[0]->remark == 'INCOMPLETE')
                                {{ substr($student->semesterRemarks[0]->remark,0,4) }} 
                              @elseif($student->semesterRemarks[0]->remark == 'POSTPONED EXAM')
                                POSE
                              @elseif($student->semesterRemarks[0]->remark == 'POSTPONED SEMESTER')
                                POSS
                              @elseif($student->semesterRemarks[0]->remark == 'POSTPONED YEAR')
                                POSY
                              @else {{ $student->semesterRemarks[0]->remark }} 
                              @endif
                            @endif 
                          @endif</td>
                        @endforeach
                    </tr>
                     @php
                       $count++;
                     @endphp
                     @endif
                    @endforeach
                  </table>
                </div><!-- end of table-responsive -->
                

          </div><!-- end of col-md-12 -->
        </div><!-- end of row -->
        <div class="row">
        <div class="col-md-12">
             <h3 class="ss-bold">COURSE MODULES RESULTS SUMMARY</h3>
                <div class="table-responsive">
                   <table class="table table-condensed table-bordered">
                      <tr>
                        <td class="ss-bold" rowspan="2">CODE</td>
                        <td class="ss-bold" rowspan="2">NAME</td>
                        <td class="ss-bold" colspan="3">C</td>
                        <td class="ss-bold" colspan="3">F</td>
                        <td class="ss-bold" colspan="3">I</td>
                        <td class="ss-bold" colspan="3">POST</td>
                        <td class="ss-bold" colspan="3">TOTAL</td>
                        <td class="ss-bold" colspan="3">PASS</td>
                        <td class="ss-bold" colspan="3">FAIL</td>
                        <td class="ss-bold" colspan="3">CARRY</td>
                        <td class="ss-bold" colspan="3">RETAKE</td>
                      </tr>
                      <tr>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                      </tr>
                      @foreach($modules as $modKey=>$mod)
                      <tr>
                        <td>{{ $modKey }}</td>
                        <td>{{ $mod['name'] }}</td>
                        <td>{{ $mod['ML']['supp_pass_count'] }}</td>
                        <td>{{ $mod['FL']['supp_pass_count'] }}</td>
                        <td>{{ $mod['supp_pass_count'] }}</td>
                        <td>{{ $mod['ML']['supp_fail_count'] }}</td>
                        <td>{{ $mod['FL']['supp_fail_count'] }}</td>
                        <td>{{ $mod['supp_fail_count'] }}</td>
                        <td>{{ $mod['ML']['supp_inc_count'] }}</td>
                        <td>{{ $mod['FL']['supp_inc_count'] }}</td>
                        <td>{{ $mod['supp_inc_count'] }}</td>
                        <td>{{ $mod['ML']['supp_pst_count'] }}</td>
                        <td>{{ $mod['FL']['supp_pst_count'] }}</td>
                        <td>{{ $mod['supp_pst_count'] }}</td>
                        <td>{{ $mod['ML']['supp_total_count'] }}</td>
                        <td>{{ $mod['FL']['supp_total_count'] }}</td>
                        <td>{{ $mod['supp_total_count'] }}</td>
                        <td>{{ $mod['ML']['supp_pass_count'] }}</td>
                        <td>{{ $mod['FL']['supp_pass_count'] }}</td>
                        <td>{{ $mod['supp_pass_count'] }}</td>
                        <td>{{ $mod['ML']['supp_fail_count'] }}</td>
                        <td>{{ $mod['FL']['supp_fail_count'] }}</td>
                        <td>{{ $mod['supp_fail_count'] }}</td>
                        <td>{{ $mod['ML']['supp_carry_count'] }}</td>
                        <td>{{ $mod['FL']['supp_carry_count'] }}</td>
                        <td>{{ $mod['supp_carry_count'] }}</td>
                        <td>{{ $mod['ML']['supp_retake_count'] }}</td>
                        <td>{{ $mod['FL']['supp_retake_count'] }}</td>
                        <td>{{ $mod['supp_retake_count'] }}</td>
                      </tr>
                      @endforeach
                   </table>
                </div><!-- end of table-responsive -->
        </div>
        </div>

        <div class="row">
          <div class="col-md-12">
              <h3>SPECIAL SEATS 1ST SEMESTER</h3>
              <div class="table-responsive ss-margin-bottom">
                  <table class="table table-condensed table-bordered">
                    <tr>
                      <td class="ss-bold" rowspan="2">SN</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">REGNO</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">NAME</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">GENDER</td>
                      @endif
                      <!-- <td class="ss-bold" rowspan="2">CLASS MODE</td> -->
                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)
                          @if($assignment->programModuleAssignment->semester_id == $first_semester->id)
                      <td class="ss-bold" colspan="4">{{ $assignment->module->code }} ({{ $assignment->module->credit }})</td>
                          @endif
                          @endforeach
                      @endforeach
                      <td colspan="2"></td>
                    </tr>
                    <tr>
                      
                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)
                          @if($assignment->programModuleAssignment->semester_id == $first_semester->id)
                      <td class="ss-bold">CA</td>
                      <td class="ss-bold">FE</td>
                      <td class="ss-bold">TT</td>
                      <td class="ss-bold">GD</td>
                          @endif
                          @endforeach
                      @endforeach
                      
                      <td class="ss-bold">GPA</td>
                      <td class="ss-bold">REMARK</td>
                    </tr>
                    
                    

                    @foreach($special_exam_first_semester_students as $key=>$student)
                    <tr>
                      <td>{{ $key+1 }}</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td>{{ $student->registration_number }}</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td>{{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name}}</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td>{{ $student->gender }}</td>
                      @endif
                         

                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)

                          
                          @if($assignment->programModuleAssignment->semester_id == $first_semester->id)

                      
                          @foreach($student->examinationResults as $result)
                            @if($result->module_assignment_id == $assignment->id)
                             


                      
                            <td @if($result->course_work_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->course_work_score }}</td>
                            <td @if($result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->final_score }}</td>
                            <td @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ round($result->total_score) }}</td>
                            <td @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->grade }}</td>
                            @endif


                          @endforeach

                          @endif
                        
                        @endforeach

                      @endforeach
                      @for($i = 0; $i < count($mods)-count($student->examinationResults); $i++)
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                      @endfor
                      <td>@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->gpa) {{ bcdiv($student->semesterRemarks[0]->gpa,1,1) }} @else N/A @endif 
                      @endif</td>
                      <td>@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->remark) {{ $student->semesterRemarks[0]->remark }} @else N/A @endif 
                      @endif</td>
                    </tr>
                      
                    @endforeach
                  </table>
                </div><!-- end of table-responsive -->
          </div><!-- end of col-md-12 -->
        </div><!-- end of row -->

        <div class="row">
        <div class="col-md-12">
             <h3 class="ss-bold">1ST SEMESTER SPECIAL SEATS RESULTS SUMMARY</h3>
                <div class="table-responsive">
                   <table class="table table-condensed table-bordered">
                      <tr>
                        <td class="ss-bold" rowspan="2">CODE</td>
                        <td class="ss-bold" rowspan="2">NAME</td>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold" colspan="3">{{ $policy->grade }}</td>
                        @endforeach
                        <td class="ss-bold" colspan="3">I</td>
                        <td class="ss-bold" colspan="3">POST</td>
                        <td class="ss-bold" colspan="3">DS</td>
                        <td class="ss-bold" colspan="3">TOTAL</td>
                        <td class="ss-bold" colspan="3">PASS</td>
                        <td class="ss-bold" colspan="3">FAIL</td>
                      </tr>
                      <tr>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        @endforeach
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                      </tr>
                      @foreach($modules as $modKey=>$mod)
                        @if($mod['semester_id'] == $first_semester->id)
                      <tr>
                        <td>{{ $modKey }}</td>
                        <td>{{ $mod['name'] }}</td>
                        @foreach($grading_policies as $pol)
                            <td>{{ $mod['special_grades']['ML'][$pol->grade] }}</td>
                            <td>{{ $mod['special_grades']['FL'][$pol->grade] }}</td>
                            <td>{{ $mod['special_grades'][$pol->grade] }}</td>
                        @endforeach
                        <td>{{ $mod['ML']['special_inc_count'] }}</td>
                        <td>{{ $mod['FL']['special_inc_count'] }}</td>
                        <td>{{ $mod['special_inc_count'] }}</td>
                        <td>{{ $mod['ML']['special_pst_count'] }}</td>
                        <td>{{ $mod['FL']['special_pst_count'] }}</td>
                        <td>{{ $mod['special_pst_count'] }}</td>
                        <td>{{ $mod['ML']['special_ds_count'] }}</td>
                        <td>{{ $mod['FL']['special_ds_count'] }}</td>
                        <td>{{ $mod['special_ds_count'] }}</td>
                        <td>{{ $mod['ML']['special_total_count'] }}</td>
                        <td>{{ $mod['FL']['special_total_count'] }}</td>
                        <td>{{ $mod['special_total_count'] }}</td>
                        <td>{{ $mod['ML']['special_pass_count'] }}</td>
                        <td>{{ $mod['FL']['special_pass_count'] }}</td>
                        <td>{{ $mod['special_pass_count'] }}</td>
                        <td>{{ $mod['ML']['special_fail_count'] }}</td>
                        <td>{{ $mod['FL']['special_fail_count'] }}</td>
                        <td>{{ $mod['special_fail_count'] }}</td>
                      </tr>
                      @endif
                      @endforeach
                   </table>
                </div><!-- end of table-responsive -->
        </div>
        </div>

        <div class="row">
           <div class="col-md-12">
              <h3>SPECIAL SEATS 2ND SEMESTER</h3>
              <div class="table-responsive ss-margin-bottom">
                  <table class="table table-condensed table-bordered">
                    <tr>
                      <td class="ss-bold" rowspan="2">SN</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">REGNO</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">NAME</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">GENDER</td>
                      @endif
                      <!-- <td class="ss-bold" rowspan="2">CLASS MODE</td> -->
                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)
                          @if($assignment->programModuleAssignment->semester_id == $second_semester->id)
                      <td class="ss-bold" colspan="4">{{ $assignment->module->code }} ({{ $assignment->module->credit }})</td>
                          @endif
                          @endforeach
                      @endforeach
                      <td colspan="2"></td>
                    </tr>
                    <tr>
                      
                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)
                          @if($assignment->programModuleAssignment->semester_id == $second_semester->id)
                      <td class="ss-bold">CA</td>
                      <td class="ss-bold">FE</td>
                      <td class="ss-bold">TT</td>
                      <td class="ss-bold">GD</td>
                          @endif
                          @endforeach
                      @endforeach
                      
                          <td class="ss-bold">2ND SEM REMARK</td>
                          <td class="ss-bold">1ST SEM REMARK</td>
                          <td class="ss-bold">GPA</td>
                          <td class="ss-bold">OVERALL REMARK</td>
                      
                    </tr>
                    
                    

                    @foreach($special_exam_second_semester_students as $key=>$student)
                    <tr>
                      <td>{{ $key+1 }}</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td>{{ $student->registration_number }}</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td>{{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name}}</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td>{{ $student->gender }}</td>
                      @endif
                         

                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)

                          @if($assignment->programModuleAssignment->semester_id == $first_semester->id)

                      
                          @foreach($student->examinationResults as $result)
                            @if($result->module_assignment_id == $assignment->id)
                             


                      
                            <td @if($result->course_work_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->course_work_score }}</td>
                            <td @if($result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->final_score }}</td>
                            <td @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->total_score }}</td>
                            <td @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->grade }}</td>
                            @endif
                          @endforeach
                           
                           @endif
                          @endforeach
                      
                      @endforeach
                      @for($i = 0; $i < count($module_assignments)-count($student->examinationResults); $i++)
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                      @endfor

                      @if(count($module_assignments)-count($student->examinationResults) != 0)
                         @if(App\Utils\Util::stripSpacesUpper($request->get('semester_id')) == App\Utils\Util::stripSpacesUpper('Semester 2'))
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                         @endif

                      @endif
                      
                      @if(count($student->semesterRemarks) != 0)
                      <td>
                        @if($student->semesterRemarks[1]->remark) {{ $student->semesterRemarks[1]->remark }} @else N/A @endif
                      </td>
                      <td>
                        @if($student->semesterRemarks[0]->remark) {{ $student->semesterRemarks[0]->remark }} @else N/A @endif
                      </td>
                      @endif
                      @if($student->annualRemarks)
                      @if(count($student->annualRemarks) != 0)
                      <td>
                        @if($student->annualRemarks[0]->gpa) {{ bcdiv($student->annualRemarks[0]->gpa,1,1) }} @else N/A @endif
                      </td>
                      <td>
                        @if($student->annualRemarks[0]->remark) {{ $student->annualRemarks[0]->remark }} @else N/A @endif
                      </td>
                      @endif
                      @endif
                    </tr>
                    @endforeach
                  </table>
                </div><!-- end of table-responsive -->
           </div><!-- end of col-md-12 -->
        </div><!-- end of row -->

                <div class="row">
        <div class="col-md-12">
             <h3 class="ss-bold">2ND SEMESTER SPECIAL SEATS RESULTS SUMMARY</h3>
                <div class="table-responsive">
                   <table class="table table-condensed table-bordered">
                      <tr>
                        <td class="ss-bold" rowspan="2">CODE</td>
                        <td class="ss-bold" rowspan="2">NAME</td>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold" colspan="3">{{ $policy->grade }}</td>
                        @endforeach
                        <td class="ss-bold" colspan="3">I</td>
                        <td class="ss-bold" colspan="3">POST</td>
                        <td class="ss-bold" colspan="3">DS</td>
                        <td class="ss-bold" colspan="3">TOTAL</td>
                        <td class="ss-bold" colspan="3">PASS</td>
                        <td class="ss-bold" colspan="3">FAIL</td>
                      </tr>
                      <tr>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        @endforeach
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                        <td class="ss-bold">M</td>
                        <td class="ss-bold">F</td>
                        <td class="ss-bold">TT</td>
                      </tr>
                      @foreach($modules as $modKey=>$mod)
                        @if($mod['semester_id'] == $second_semester->id)
                      <tr>
                        <td>{{ $modKey }}</td>
                        <td>{{ $mod['name'] }}</td>
                        @foreach($grading_policies as $pol)
                            <td>{{ $mod['special_grades']['ML'][$pol->grade] }}</td>
                            <td>{{ $mod['special_grades']['FL'][$pol->grade] }}</td>
                            <td>{{ $mod['special_grades'][$pol->grade] }}</td>
                        @endforeach
                        <td>{{ $mod['ML']['special_inc_count'] }}</td>
                        <td>{{ $mod['FL']['special_inc_count'] }}</td>
                        <td>{{ $mod['special_inc_count'] }}</td>
                        <td>{{ $mod['ML']['special_pst_count'] }}</td>
                        <td>{{ $mod['FL']['special_pst_count'] }}</td>
                        <td>{{ $mod['special_pst_count'] }}</td>
                        <td>{{ $mod['ML']['special_ds_count'] }}</td>
                        <td>{{ $mod['FL']['special_ds_count'] }}</td>
                        <td>{{ $mod['special_ds_count'] }}</td>
                        <td>{{ $mod['ML']['special_total_count'] }}</td>
                        <td>{{ $mod['FL']['special_total_count'] }}</td>
                        <td>{{ $mod['special_total_count'] }}</td>
                        <td>{{ $mod['ML']['special_pass_count'] }}</td>
                        <td>{{ $mod['FL']['special_pass_count'] }}</td>
                        <td>{{ $mod['special_pass_count'] }}</td>
                        <td>{{ $mod['ML']['special_fail_count'] }}</td>
                        <td>{{ $mod['FL']['special_fail_count'] }}</td>
                        <td>{{ $mod['special_fail_count'] }}</td>
                      </tr>
                      @endif
                      @endforeach
                   </table>
                </div><!-- end of table-responsive -->
        </div>
        </div>

         <div class="row">
        <div class="col-md-8">
             <h3 class="ss-bold">KEYS</h3>
                <div class="table-responsive">
                   <table class="table table-condensed table-bordered">
                     <tr>
                       <td class="ss-bold">KEY NAME</td>
                       <td class="ss-bold">DESCRIPTION</td>
                     </tr>
                     <tr>
                      <td>CA</td>
                      <td>Continous Assessment</td>
                    </tr>
                    <tr>
                      <td>CARRY</td>
                      <td>Repeat Course Semester/Year - With Promotion</td>
                    </tr>
                    <tr>
                      <td>DS</td>
                      <td>Direct SUP</td>
                    </tr>
                    <tr>
                      <td>FAIL&DISCO</td>
                      <td>Failed and Discontinued</td>
                    </tr>
                    <tr>
                      <td>FE</td>
                      <td>Final Exam</td>
                    </tr>
                    <tr>
                      <td>GD</td>
                      <td>Grade</td>
                    </tr>
                    <tr>
                      <td>I</td>
                      <td>Incomplete CW + FE</td>
                    </tr>
                    <tr>
                      <td>IC</td>
                      <td>Incomplete Coursework</td>
                    </tr>
                    <tr>
                      <td>IF</td>
                      <td>Incomplete Final Exam</td>
                    </tr>
                    <tr>
                      <td>INC</td>
                      <td>Incomplete Student Results</td>
                    </tr>
                    <tr>
                      <td>N/A</td>
                      <td>Not Applicable</td>
                    </tr>
                    <tr>
                      <td>PASS</td>
                      <td>Passed all Modules</td>
                    </tr>
                    <tr>
                      <td>POST</td>
                      <td>Postponed</td>
                    </tr>
                    <tr>
                      <td>RETAKE</td>
                      <td>Repeat Course Semester/Year - No Promotion</td>
                    </tr>
                    <tr>
                      <td>SUPP</td>
                      <td>Supplementary Status</td>
                    </tr>
                    <tr>
                      <td>TT</td>
                      <td>Total</td>
                    </tr>
                  </table>
           </div><!-- end of table-responsive -->
          </div><!-- end of col-md-12 -->
        </div><!-- end of row -->

        <div class="row">
          <div class="col-md-6">
             <div class="ss-bold ss-left">
                 <h3>Name of Head of Department: .................................</h3>
                 <h3>Signature: ..............................</h3>
                 <h3>Date: ..................................</h3>
             </div>
          </div><!--end of col-md-6 -->
          <div class="col-md-6">
             <div class="ss-bold ss-left">
                 <h3>Name of Examination Officer: .................................</h3>
                 <h3>Signature: .............................</h3>
                 <h3>Date: ..................................</h3>
             </div>
          </div><!--end of col-md-6 -->
        </div><!-- end of row -->

        

      </div><!-- end of container -->


</body>
</html>


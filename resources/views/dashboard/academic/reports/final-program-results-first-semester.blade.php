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
               <p style="font-size:7pt">@if($semester) {{ strtoupper($semester->name) }} @endif EXAMINATION RESULTS <span style="font-weight:normal">(CA Weight {{ (round($module_assignments[0]->programModuleAssignment->course_work_min_mark,0)) }}%, FE Weight {{(round($module_assignments[0]->programModuleAssignment->final_min_mark,0))}}%)</span> </p> 
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
                        $male_postponement_cases = $female_postponement_cases = $male_upsecond_class_cases = $female_upsecond_class_cases = $male_first_class_cases = $female_first_class_cases =
                        $male_disco_cases = $female_disco_cases = $male_retake_cases = $female_retake_cases = $male_carry_cases = $female_carry_cases = $male_incomplete_cases = $female_incomplete_cases =
                        $male_failed_cases = $female_failed_cases = $female_pass_cases = $male_pass_cases = $female_lwsecond_class_cases = $male_lwsecond_class_cases = $female_second_class_cases = $male_second_class_cases = 0;
                      @endphp

                    @foreach($students as $key=>$student)

                    <tr>
                      <td class="ss-font-xs">{{ $key+1 }}</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td class="ss-font-xs">{{ $student->registration_number }}</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td class="ss-font-xs">{{ $student->surname }}, {{ ucwords(strtolower($student->first_name))  }} {{ substr($student->middle_name, 1, 1)}}</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td class="ss-font-xs">{{ $student->gender }}</td>
                      @endif

                      @foreach($module_assignments as $asKey=>$assignment)

                          @if($asKey == count($module_assignments))
                            @php
                              $results_present = true;
                            @endphp
                          @else
                            @php
                              $results_present = false;
                            @endphp
                          @endif

                          @foreach($student->examinationResults as $result)
                            @if($result->module_assignment_id == $assignment->id)
                              @php
                                $results_present = true;
                              @endphp

                              <td 
                                @if($result->course_work_remark == 'FAIL' && !$result->supp_processed_at) 
                                class="ss-custom-grey ss-center ss-font-xs" 
                                @else 
                                class="ss-center ss-font-xs" 
                                @endif>

                                @if($assignment->module->course_work_based == 1)
                                  @if($result->course_work_score) 
                                  {{ round($result->course_work_score) }} 
                                  @else - @endif
                                @else
                                N/A
                                @endif           
                              </td>

                            <td 
                              @if($result->final_remark == 'FAIL' && !$result->supp_processed_at)
                                @if($result->course_work_remark == 'FAIL')
                                  class="ss-center ss-font-xs" 
                                @else
                                  class="ss-custom-grey ss-center ss-font-xs" 
                                @endif
                              @elseif(count($result->changes) != 0) 
                                class="ss-center ss-custom-lightblue ss-font-xs" 
                              @else 
                                class="ss-center ss-font-xs" 
                              @endif>

                              @if($result->supp_processed_at)
                              N/A
                              @else 
                                @if($result->final_score && ($result->final_remark == 'FAIL' || $result->final_remark == 'PASS'))
 
                                  {{ round($result->final_score) }} 
                           
                                @else - @endif
                              @endif
                              
                            </td>
                            <td 
                              @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') 
                                class="ss-custom-grey-- ss-center ss-font-xs" 
                              @elseif($result->supp_processed_at)
                                class="ss-center ss-font-xs" 
                              @else 
                                class="ss-center ss-font-xs" 
                              @endif>

                              @if($result->supp_processed_at)
                                @if($result->supp_score) 
                                {{ round($result->supp_score) }} 
                                @else - @endif
                              @else 
                                @if($result->total_score) 
                                {{ round($result->total_score) }} 
                                @else - @endif
                              @endif
                              
                          </td>
                            <td 
                              @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') 
                                class="ss-custom-grey-- ss-center ss-font-xs" 
                              @else class="ss-center ss-font-xs" 
                              @endif>
                              @if($student->semesterRemarks[0]->remark == 'DECEASED' || $student->semesterRemarks[0]->remark == 'POSTPONED SEMESTER' || $student->semesterRemarks[0]->remark == 'POSTPONED YEAR')
                              -
                              @else
                                @if($result->supp_processed_at)
                                  
                                  @if($result->grade) 
                                    {{ $result->grade }}* 
                                  @else - @endif


                                @else 
                                  
                                  @if($result->grade) 
                                    {{ $result->grade }} 
                                  @else - @endif
                                @endif
                              @endif
                              
                          </td>
                          
                            @endif
                          @endforeach
                          
                          @if(!$results_present)
                            <td class="ss-font-xs"></td>
                            <td class="ss-font-xs"></td>
                            <td class="ss-font-xs"></td>
                            <td class="ss-font-xs"></td>
                          @endif
                      
                      @endforeach
                      
                      <td class="ss-center ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->gpa) {{ bcdiv($student->semesterRemarks[0]->gpa,1,1) }} @else - @endif 
                      @endif</td>
                      <td class="ss-center ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->gpa) {{ $student->semesterRemarks[0]->point }} @else - @endif 
                      @endif</td>
                      <td class="ss-center ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->gpa) {{ $student->semesterRemarks[0]->credit }} @else - @endif 
                      @endif</td>
                      <td class="ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->remark) 
                          @if($student->semesterRemarks[0]->remark == 'SUPP')
                            @if($student->gender == 'F') @php $female_failed_cases++; @endphp
                            @elseif($student->gender == 'M') @php $male_failed_cases++; @endphp
                            @endif
                          @elseif($student->semesterRemarks[0]->remark == 'INCOMPLETE')
                            @if($student->gender == 'F') @php $female_incomplete_cases++; @endphp
                            @elseif($student->gender == 'M') @php $male_incomplete_cases++; @endphp
                            @endif
                          @elseif($student->semesterRemarks[0]->remark == 'CARRY')
                            @if($student->gender == 'F') @php $female_carry_cases++; @endphp
                            @elseif($student->gender == 'M') @php $male_carry_cases++; @endphp
                            @endif
                          @elseif($student->semesterRemarks[0]->remark == 'RETAKE')
                            @if($student->gender == 'F') @php $female_retake_cases++; @endphp
                            @elseif($student->gender == 'M') @php $male_retake_cases++; @endphp
                            @endif
                          @elseif($student->semesterRemarks[0]->remark == 'DISCO')
                            @if($student->gender == 'F') @php $female_disco_cases++; @endphp
                            @elseif($student->gender == 'M') @php $male_disco_cases++; @endphp
                            @endif
                          @elseif($student->semesterRemarks[0]->remark == 'PASS')
                            @if(str_contains(strtolower($student->semesterRemarks[0]->class),'first'))
                              @if($student->gender == 'F') @php $female_first_class_cases++; @endphp
                              @elseif($student->gender == 'M') @php $male_first_class_cases++; @endphp
                              @endif
                            @elseif(str_contains(strtolower($student->semesterRemarks[0]->class),'upper second'))
                              @if($student->gender == 'F') @php $female_upsecond_class_cases++; @endphp
                              @elseif($student->gender == 'M') @php $male_upsecond_class_cases++; @endphp
                              @endif
                            @elseif(strtolower($student->semesterRemarks[0]->class) == 'second class'))
                              @if($student->gender == 'F') @php $female_second_class_cases++; @endphp
                              @elseif($student->gender == 'M') @php $male_second_class_cases++; @endphp
                              @endif
                            @elseif(str_contains(strtolower($student->semesterRemarks[0]->class),'lower second'))
                              @if($student->gender == 'F') @php $female_lwsecond_class_cases++; @endphp
                              @elseif($student->gender == 'M') @php $male_lwsecond_class_cases++; @endphp
                              @endif
                            @elseif(str_contains(strtolower($student->semesterRemarks[0]->class),'pass'))
                              @if($student->gender == 'F') @php $female_pass_cases++; @endphp
                              @elseif($student->gender == 'M') @php $male_pass_cases++; @endphp
                              @endif
                            @endif
                          @elseif(str_contains($student->semesterRemarks[0]->remark, 'POSTPONE'))
                            @if($student->gender == 'F') @php $female_postponement_cases++; @endphp
                            @elseif($student->gender == 'M') @php $male_postponement_cases++; @endphp
                            @endif
                          @endif
                          @if($student->semesterRemarks[0]->remark == 'INCOMPLETE')
                              {{ substr($student->semesterRemarks[0]->remark,0,4) }} 
                          @elseif($student->semesterRemarks[0]->remark == 'POSTPONED EXAM')
                              POSE
                          @elseif($student->semesterRemarks[0]->remark == 'POSTPONED SEMESTER')
                              POSS
                          @elseif($student->semesterRemarks[0]->remark == 'POSTPONED YEAR')
                              POSY
                          @else {{ $student->semesterRemarks[0]->remark }} @endif @else N/A @endif 
                      @endif</td>
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
                    </tr>
                    @endforeach
                    </tbody>
                  </table>
                </div><!-- end of table-responsive -->
          </div><!-- end of col-md-12 -->
        </div><!-- end of row -->
        <div class="row">
          <div class="col-md-4" style='padding-right:10px'>
            <span class="ss-bold" style="font-size:7pt"> KEYS </span> <br>
                <div class="table-responsive">
                  <table class="table table-condensed table-bordered">
                    <tr>
                      <td class="ss-bold ss-font-sm">Name</td>
                      <td class="ss-bold ss-font-sm">Description</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>CA</td>
                      <td>Continous Assessment</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>CARRY</td>
                      <td>Repeat Course Semester/Year - With Promotion</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>DS</td>
                      <td>Direct SUP</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>FE</td>
                      <td>Final Exam</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>GD</td>
                      <td>Grade</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>I</td>
                      <td>Incomplete CW + FE</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>IC</td>
                      <td>Incomplete Coursework</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>IF</td>
                      <td>Incomplete Final Exam</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>INCO</td>
                      <td>Incomplete Student Results</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>N/A</td>
                      <td>Not Applicable</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>PASS</td>
                      <td>Passed all Modules</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>POSE</td>
                      <td>Postponed Exam</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>POSS</td>
                      <td>Postponed Semester</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>POSY</td>
                      <td>Postponed Year</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>RETAKE</td>
                      <td>Repeat Course Semester/Year - No Promotion</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>SUPP</td>
                      <td>Supplementary Status</td>
                    </tr>
                    <tr class="ss-font-sm">
                      <td>TT</td>
                      <td>Total</td>
                    </tr>
                    </table>
           </div><!-- end of table-responsive -->
          </div>


        <div class="row">
          <div class="col-md-8">
              <div class="ss-left">
                  <p>Head of Department: <span class="font-weight-normal"> {{ strtoupper($staff->surname) }}, {{ ucwords(strtolower($staff->first_name))}} {{ substr($staff->middle_name,0,1)}} </span></p>
                  <p >Signature: ......................................................</p>
                  <p >Date: .............................................................</p>
              </div>
          </div><!--end of col-md-6 -->
          <div class="col-md-4">
             <div class="ss-left">
                 <p >Examination Officer: ......................................</p>
                 <p >Signature: ......................................................</p>
                 <p >Date: ..............................................................</p>
             </div>
          </div><!--end of col-md-6 -->
        </div><!-- end of row -->

      </div><!-- end of container -->


</body>
</html>


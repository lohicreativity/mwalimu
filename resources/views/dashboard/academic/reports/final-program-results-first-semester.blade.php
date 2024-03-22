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
        font-size: 13px;
     }
     .ss-font-xs{
        font-size: 10.7px;
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
               <h3>THE MWALIMU NYERERE MEMORIAL ACADEMY</h3>
			         <img src="{{ asset('dist/img/logo.png') }}" alt="Config::get('constants.SITE_NAME') }}" class="ss-logo" width="10%">				   
               <h4>{{ $campus->name }}</h4>
               <h4>{{ $department->name }}</h4>
               <h5>{{ $program->name }} (YEAR {{ $year_of_study }} - {{ strtoupper(substr($intake->name,0,3)) }}) - {{ $study_academic_year->academicYear->year }}</h5>
               <p class="ss-bold" style="font-size:12pt">@if($semester) {{ strtoupper($semester->name) }} @endif EXAMINATION RESULTS <span style="font-weight:normal">(CA Weight {{ (round($module_assignments[0]->programModuleAssignment->course_work_min_mark,0)) }}%, FE Weight {{(round($module_assignments[0]->programModuleAssignment->final_min_mark,0))}}%)</span> </p> 
              </div>
               <div class="table-responsive ss-margin-bottom">
                  <table class="table table-condensed table-bordered">
                    <thead>
                      <tr>
                        <th class="ss-bold ss-font-xs" rowspan="3">SN</th>
                        @if($request->get('reg_display_type') == 'SHOW')<th  class="ss-bold ss-font-xs" rowspan="3">Registration Number</th>@endif
                        @if($request->get('name_display_type') == 'SHOW')<th  class="ss-bold ss-font-xs" rowspan="3">Name</th>@endif
                        @if($request->get('gender_display_type') == 'SHOW')<th  class="ss-bold ss-font-xs" rowspan="3">Sex</th>@endif
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
                        
                        <th class="ss-bold ss-font-xs">GPA</th>
                        <th class="ss-bold ss-font-xs">Points</th>
                        <th class="ss-bold ss-font-xs">Credits</th>
                        <th class="ss-bold ss-font-xs">Remark</th>
                        <th class="ss-bold ss-font-xs">Classification</th>
                      </tr>
                    </thead>
                    <tbody>
                    
                    

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

                              @if($result->supp_processed_at)
                              N/A
                              @else 
                                @if($assignment->module->course_work_based == 1)
                                  @if($result->course_work_score) 
                                  {{ round($result->course_work_score) }} 
                                  @else - @endif
                                @else
                                N/A
                                @endif  
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
                                  @if($result->course_work_remark == 'FAIL')
                                    -
                                  @else 
                                  {{ round($result->final_score) }} 
                                  @endif
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
                              
                                @if($result->supp_processed_at)
                                  
                                  @if($result->grade) 
                                    {{ $result->grade }}* 
                                  @else - @endif


                                @else 
                                  @if($result->grade) 
                                    {{ $result->grade }} 
                                  @else - @endif
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
                      
                      <td class="ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->gpa) {{ bcdiv($student->semesterRemarks[0]->gpa,1,1) }} @else N/A @endif 
                      @endif</td>
                      <td class="ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->gpa) {{ $student->semesterRemarks[0]->point }} @else N/A @endif 
                      @endif</td>
                      <td class="ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->gpa) {{ $student->semesterRemarks[0]->credit }} @else N/A @endif 
                      @endif</td>
                      <td class="ss-font-xs">@if(count($student->semesterRemarks) != 0)   
                        @if($student->semesterRemarks[0]->remark) 
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
                        @if($student->semesterRemarks[0]->class) {{ $student->semesterRemarks[0]->class }} @else N/A @endif 
                      @endif</td>
                    </tr>
                    @endforeach
                    </tbody>
                  </table>
                </div><!-- end of table-responsive -->
                


          </div><!-- end of col-md-12 -->
        </div><!-- end of row -->
        <div class="row">
        <div class="col-md-12">
             <h3 class="ss-bold">PROGRAMME MODULES RESULTS SUMMARY BY SEX</h3>
                <div class="table-responsive">
                   <table class="table table-condensed table-bordered">
                      <tr>
                        <td class="ss-bold ss-font-sm" rowspan="2">Code</td>
                        <td class="ss-bold ss-font-sm" rowspan="2">Name</td>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold ss-font-sm" colspan="3">{{ $policy->grade }}</td>
                        @endforeach
                        <td class="ss-bold ss-font-sm" colspan="3">IC</td>
                        <td class="ss-bold ss-font-sm" colspan="3">IF</td>
                        <td class="ss-bold ss-font-sm" colspan="3">I</td>
                        <td class="ss-bold ss-font-sm" colspan="3">POST</td>
                        <td class="ss-bold ss-font-sm" colspan="3">DS</td>
                        <td class="ss-bold ss-font-sm" colspan="3">Total</td>
                        <td class="ss-bold ss-font-sm" colspan="3">Pass</td>
                        <td class="ss-bold ss-font-sm" colspan="3">Fail</td>
                        <td class="ss-bold ss-font-sm" colspan="3">Fail FE</td>
                        <td class="ss-bold ss-font-sm" colspan="3">Retake</td>
                      </tr>
                      <tr>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        @endforeach
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                        <td class="ss-bold ss-font-sm">M</td>
                        <td class="ss-bold ss-font-sm">F</td>
                        <td class="ss-bold ss-font-sm">TT</td>
                      </tr>
                      @foreach($modules as $modKey=>$mod)
                      <tr>
                        <td class="ss-bold ss-font-xs">{{ $modKey }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['name'] }}</td>
                        @foreach($grading_policies as $pol)
                            <td class="ss-bold ss-font-xs">{{ $mod['grades']['ML'][$pol->grade] }}</td>
                            <td class="ss-bold ss-font-xs">{{ $mod['grades']['FL'][$pol->grade] }}</td>
                            <td class="ss-bold ss-font-xs">{{ $mod['grades'][$pol->grade] }}</td>
                        @endforeach
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['ic_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['ic_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ic_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['if_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['if_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['if_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['inc_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['inc_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['inc_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['pst_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['pst_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['pst_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['ds_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['ds_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ds_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['total_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['total_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['total_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['pass_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['pass_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['pass_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['fail_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['fail_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['fail_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['fail_fe_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['fail_fe_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['fail_fe_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ML']['retake_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['FL']['retake_count'] }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['retake_count'] }}</td>
                      </tr>
                      @endforeach
                   </table>
                </div><!-- end of table-responsive -->
        </div>
        </div>

        <div class="row">
        <div class="col-md-12">
             <h3 class="ss-bold">PERFORMANCE SUMMARY</h3>
                <div class="table-responsive">
                   <table class="table table-condensed table-bordered">
                      <tr>
                        <td class="ss-bold">Code</td>
                        <td class="ss-bold">Name</td>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold">{{ $policy->grade }}</td>
                        @endforeach
                        <td class="ss-bold ss-font-sm">I</td>
                        <td class="ss-bold ss-font-sm">IC</td>
                        <td class="ss-bold ss-font-sm">IF</td>
                        <td class="ss-bold ss-font-sm">POST</td>
                        <td class="ss-bold ss-font-sm">DS</td>
                        <td class="ss-bold ss-font-sm">Pass</td>
                        <td class="ss-bold ss-font-sm">Fail</td>
                      </tr>
                      @foreach($modules as $modKey=>$mod)
                      <tr>
                        <td class="ss-bold ss-font-xs">{{ $modKey }}</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['name'] }}</td>
                        @foreach($grading_policies as $pol)
                            <td class="ss-bold ss-font-xs">{{ $mod['grades'][$pol->grade] }}({{ round($mod['grades_perc'][$pol->grade],0) }}%)</td>
                        @endforeach
                        <td class="ss-bold ss-font-xs">{{ $mod['inc_count'] }}({{ round($mod['inc_rate'],0) }}%)</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ic_count'] }}({{ round($mod['ic_rate'],0) }}%)</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['if_count'] }}({{ round($mod['if_rate'],0) }}%)</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['pst_count'] }}({{ round($mod['pst_rate'],0) }}%)</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['ds_count'] }}({{ round($mod['ds_rate'],0) }}%)</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['pass_count'] }}({{ round($mod['pass_rate'],0) }}%)</td>
                        <td class="ss-bold ss-font-xs">{{ $mod['fail_count'] }}({{ round($mod['fail_rate'],0) }}%)</td>
                      </tr>
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
                      <td class="ss-bold">Key Name</td>
                      <td class="ss-bold">Description</td>
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
                      <td>INCO</td>
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
                      <td>POSE</td>
                      <td>Postponed Exam</td>
                    </tr>
                    <tr>
                      <td>POSS</td>
                      <td>Postponed Semester</td>
                    </tr>
                    <tr>
                      <td>POSY</td>
                      <td>Postponed Year</td>
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
          </div>
        </div>

        <div class="row">
        <div class="col-md-9">
             <div class="ss-left">
                 <p class="ss-bold">Name of Head of Department: .................................</p>
                 <p class="ss-bold">Signature: ..............................</p>
                 <p class="ss-bold">Date: ..................................</p>
             </div>
          </div><!--end of col-md-6 -->
          <div class="col-md-3">
             <div class="ss-left">
                 <p class="ss-bold">Name of Examination Officer: .................................</p>
                 <p class="ss-bold">Signature: .............................</p>
                 <p class="ss-bold">Date: ..................................</p>
             </div>
          </div><!--end of col-md-6 -->
        </div><!-- end of row -->

      </div><!-- end of container -->


</body>
</html>


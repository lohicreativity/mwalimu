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
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
      }
      .table > thead > tr > th {
        vertical-align: bottom;
        border-bottom: 2px solid #ddd;
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
        border-top: 2px solid #ddd;
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
        padding: 5px;
      }
      .table-bordered {
        border: 1px solid #ddd;
      }
      .table-bordered > thead > tr > th,
      .table-bordered > tbody > tr > th,
      .table-bordered > tfoot > tr > th,
      .table-bordered > thead > tr > td,
      .table-bordered > tbody > tr > td,
      .table-bordered > tfoot > tr > td {
        border: 1px solid #ddd;
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
        font-size: 14px;
     }
     .ss-font-xs{
        font-size: 12px;
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
               <h3>{{ $campus->name }}</h3>
               <h3>{{ $department->name }}</h3>
               <h3>{{ $program->name }} (YEAR {{ $year_of_study }} - {{ strtoupper(substr($intake->name,0,3)) }}) - {{ $study_academic_year->academicYear->year }}</h3>
               <h3>@if($semester) {{ strtoupper($semester->name) }} @endif EXAMINATION RESULTS</h3>
              </div>
               <div class="table-responsive ss-margin-bottom">
                  <table class="table table-condensed table-bordered">
                    <tr>
                      <td class="ss-bold" rowspan="2">SN</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">REG. NO.</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">NAME</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="2">SEX</td>
                      @endif
                      <!-- <td class="ss-bold" rowspan="2">CLASS MODE</td> -->
                      @foreach($module_assignments as $assignment)
                      <td class="ss-bold" colspan="4">{{ $assignment->module->code }} ({{ $assignment->module->credit }})</td>
                      @endforeach
                      <td colspan="5"></td>
                    </tr>
                    <tr>
                      
                      @foreach($module_assignments as $assignment)
                      <td class="ss-bold">CA</td>
                      <td class="ss-bold">FE</td>
                      <td class="ss-bold">TT</td>
                      <td class="ss-bold">GD</td>
                      @endforeach
                      
                      
                      @if($semester)
                         @if(App\Utils\Util::stripSpacesUpper($semester->name) == App\Utils\Util::stripSpacesUpper('Semester 2'))
                          <td class="ss-bold">2ND SEM REMARK</td>
                          <td class="ss-bold">1ST SEM REMARK</td>
                          <td class="ss-bold">GPA</td>
                          <td class="ss-bold">POINTS</td>
                          <td class="ss-bold">CREDITS</td>
                          <td class="ss-bold">OVERALL REMARK</td>
                          <td class="ss-bold">CLASS</td>
                         @else
                          <td class="ss-bold">REMARK</td>
                          <td class="ss-bold">GPA</td>
                          <td class="ss-bold">POINTS</td>
                          <td class="ss-bold">CREDITS</td>
                          <td class="ss-bold">CLASS</td>
                         @endif
                      @endif
                      
                    </tr>
                    
                    

                    @foreach($students as $key=>$student)
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
                              class="ss-custom-grey ss-center" 
                              @else 
                              class="ss-center" 
                              @endif>

                              @if($result->supp_processed_at)
                              N/A
                              @else 
                                @if($result->course_work_score) 
                                {{ $result->course_work_score }} 
                                @else - @endif
                              @endif
                              
                          </td>
                            <td 
                              @if($result->final_remark == 'FAIL' && !$result->supp_processed_at) 
                              class="ss-custom-grey ss-center" 
                              @elseif(count($result->changes) != 0) 
                              class="ss-center ss-custom-lightblue" 
                              @else 
                              class="ss-center" 
                              @endif>

                              @if($result->supp_processed_at)
                              N/A
                              @else 
                                @if($result->final_score) 
                                {{ $result->final_score }} 
                                @else - @endif
                              @endif
                              
                            </td>
                            <td 
                              @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') 
                                class="ss-custom-grey-- ss-center" 
                              @elseif($result->supp_processed_at)
                                class="ss-center" 
                              @else 
                                class="ss-center" 
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
                                class="ss-custom-grey-- ss-center" 
                              @else class="ss-center" 
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
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                          @endif
                      @endforeach
                      
                      @if(count($student->semesterRemarks) != 0)
                      <td>
                        @if(isset($student->semesterRemarks[1])) {{ $student->semesterRemarks[1]->remark }} @else N/A @endif
                      </td>
                      <td>
                        @if(isset($student->semesterRemarks[0])) {{ $student->semesterRemarks[0]->remark }} @else N/A @endif
                      </td>
                      @endif
                      @if($student->annualRemarks)
                      @if(count($student->annualRemarks) != 0)
                      <td>
                        @if($student->annualRemarks[0]->gpa) {{ bcdiv($student->annualRemarks[0]->gpa,1,1) }} @else N/A @endif
                      </td>
                      <td>
                        @if($student->annualRemarks[0]->gpa) {{ $student->annualRemarks[0]->point }} @else N/A @endif
                      </td>
                      <td>
                        @if($student->annualRemarks[0]->gpa) {{ $student->annualRemarks[0]->credit }} @else N/A @endif
                      </td>
                      <td>
                        @if($student->annualRemarks[0]->remark) @if($student->semesterRemarks[0]->remark == 'POSTPONED' && $student->semesterRemarks[1]->remark != 'POSTPONED') N/A @else {{ $student->annualRemarks[0]->remark }} @endif @else N/A @endif
                      </td>
                      <td>
                        @if($student->annualRemarks[0]->class) {{ $student->annualRemarks[0]->class }} @else N/A @endif
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
             <h3 class="ss-bold">PROGRAMME MODULES RESULTS SUMMARY BY SEX</h3>
                <div class="table-responsive">
                   <table class="table table-condensed table-bordered">
                      <tr>
                        <td class="ss-bold" rowspan="2">CODE</td>
                        <td class="ss-bold" rowspan="2">NAME</td>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold" colspan="3">{{ $policy->grade }}</td>
                        @endforeach
                        <td class="ss-bold" colspan="3">IC</td>
                        <td class="ss-bold" colspan="3">IF</td>
                        <td class="ss-bold" colspan="3">I</td>
                        <td class="ss-bold" colspan="3">POST</td>
                        <td class="ss-bold" colspan="3">DS</td>
                        <td class="ss-bold" colspan="3">TOTAL</td>
                        <td class="ss-bold" colspan="3">PASS</td>
                        <td class="ss-bold" colspan="3">FAIL</td>
                        <td class="ss-bold" colspan="3">FAIL FE</td>
                        <td class="ss-bold" colspan="3">RETAKE</td>
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
                        @foreach($grading_policies as $pol)
                            <td>{{ $mod['grades']['ML'][$pol->grade] }}</td>
                            <td>{{ $mod['grades']['FL'][$pol->grade] }}</td>
                            <td>{{ $mod['grades'][$pol->grade] }}</td>
                        @endforeach
                        <td>{{ $mod['ML']['ic_count'] }}</td>
                        <td>{{ $mod['FL']['ic_count'] }}</td>
                        <td>{{ $mod['ic_count'] }}</td>
                        <td>{{ $mod['ML']['if_count'] }}</td>
                        <td>{{ $mod['FL']['if_count'] }}</td>
                        <td>{{ $mod['if_count'] }}</td>
                        <td>{{ $mod['ML']['inc_count'] }}</td>
                        <td>{{ $mod['FL']['inc_count'] }}</td>
                        <td>{{ $mod['inc_count'] }}</td>
                        <td>{{ $mod['ML']['pst_count'] }}</td>
                        <td>{{ $mod['FL']['pst_count'] }}</td>
                        <td>{{ $mod['pst_count'] }}</td>
                        <td>{{ $mod['ML']['ds_count'] }}</td>
                        <td>{{ $mod['FL']['ds_count'] }}</td>
                        <td>{{ $mod['ds_count'] }}</td>
                        <td>{{ $mod['ML']['total_count'] }}</td>
                        <td>{{ $mod['FL']['total_count'] }}</td>
                        <td>{{ $mod['total_count'] }}</td>
                        <td>{{ $mod['ML']['pass_count'] }}</td>
                        <td>{{ $mod['FL']['pass_count'] }}</td>
                        <td>{{ $mod['pass_count'] }}</td>
                        <td>{{ $mod['ML']['fail_count'] }}</td>
                        <td>{{ $mod['FL']['fail_count'] }}</td>
                        <td>{{ $mod['fail_count'] }}</td>
                        <td>{{ $mod['ML']['fail_fe_count'] }}</td>
                        <td>{{ $mod['FL']['fail_fe_count'] }}</td>
                        <td>{{ $mod['fail_fe_count'] }}</td>
                        <td>{{ $mod['ML']['retake_count'] }}</td>
                        <td>{{ $mod['FL']['retake_count'] }}</td>
                        <td>{{ $mod['retake_count'] }}</td>
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
                        <td class="ss-bold">CODE</td>
                        <td class="ss-bold">NAME</td>
                        @foreach($grading_policies as $policy)
                        <td class="ss-bold">{{ $policy->grade }}</td>
                        @endforeach
                        <td class="ss-bold">I</td>
                        <td class="ss-bold">IC</td>
                        <td class="ss-bold">IF</td>
                        <td class="ss-bold">POST</td>
                        <td class="ss-bold">DS</td>
                        <td class="ss-bold">PASS</td>
                        <td class="ss-bold">FAIL</td>
                      </tr>
                      @foreach($modules as $modKey=>$mod)
                      <tr>
                        <td>{{ $modKey }}</td>
                        <td>{{ $mod['name'] }}</td>
                        @foreach($grading_policies as $pol)
                            <td>{{ round($mod['grades_perc'][$pol->grade],2) }}%</td>
                        @endforeach
                        <td>{{ round($mod['inc_rate'],2) }}%</td>
                        <td>{{ round($mod['ic_rate'],2) }}%</td>
                        <td>{{ round($mod['if_rate'],2) }}%</td>
                        <td>{{ round($mod['pst_rate'],2) }}%</td>
                        <td>{{ round($mod['ds_rate'],2) }}%</td>
                        <td>{{ round($mod['pass_rate'],2) }}%</td>
                        <td>{{ round($mod['fail_rate'],2) }}%</td>
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
                       <td class="ss-bold">KEY NAME</td>
                       <td class="ss-bold">DESCRIPTION</td>
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
                       <td>DS</td>
                       <td>Direct SUP</td>
                     </tr>
                     <tr>
                       <td>I</td>
                       <td>Incomplete CW + FE</td>
                     </tr>
                     <tr>
                       <td>INC</td>
                       <td>Incomplete Student Results</td>
                     </tr>
                     <tr>
                       <td>SUPP</td>
                       <td>Supplementary Status</td>
                     </tr>
                     <tr>
                       <td>PASS</td>
                       <td>Passed all Modules</td>
                     </tr>
                     <tr>
                       <td>RETAKE</td>
                       <td>Repeat Course Semester/Year - No Promotion</td>
                     </tr>
                     <tr>
                       <td>CARRY</td>
                       <td>Repeat Course Semester/Year - With Promotion</td>
                     </tr>
                     <tr>
                       <td>FE</td>
                       <td>Final Exam</td>
                     </tr>
                     <tr>
                       <td>CA</td>
                       <td>Continous Assessment</td>
                     </tr>
                     <tr>
                       <td>GD</td>
                       <td>Grade</td>
                     </tr>
                     <tr>
                       <td>TT</td>
                       <td>Total</td>
                     </tr>
                     <tr>
                       <td>FAIL&DISCO</td>
                       <td>Failed and Discontinued</td>
                     </tr>
                  </table>
           </div><!-- end of table-responsive -->
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
             <div class="ss-bold ss-left">
                 <h3>Name of Head of Department: .................................</h3>
                 <h3>Signature of Head of Department: ..............................</h3>
                 <h3>Date: ..................................</h3>
             </div>
          </div><!--end of col-md-6 -->
          <div class="col-md-6">
             <div class="ss-bold ss-left">
                 <h3>Name of Examination Officer: .................................</h3>
                 <h3>Signature of Examination Officer: .............................</h3>
                 <h3>Date: ..................................</h3>
             </div>
          </div><!--end of col-md-6 -->
        </div><!-- end of row -->

      </div><!-- end of container -->


</body>
</html>


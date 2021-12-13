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
         background-color: #F5F5F5;
      }
  </style>
</head>

      <div class="container">
        <div class="row">
          <div class="col-md-12">
              <div class="ss-letter-head  ss-center">
               <h3>MWALIMU NYERERE MEMORIAL ACADEMY</h3>
               <h3>{{ $campus->name }}</h3>
               <h3>{{ $department->name }}</h3>
               <h3>{{ $program->name }} ({{ $study_academic_year->academicYear->year }}) (Year {{ $year_of_study }}) @if($semester) - {{ $semester->name }} @endif</h3>
               <h3>EXAMINATION RESULTS</h3>
              </div>
               <div class="table-responsive ss-margin-bottom">
                  <table class="table table-condensed table-bordered">
                    <tr>
                      <td class="ss-bold" rowspan="4">SN</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="4">REGNO</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="4">NAME</td>
                      @endif
                      @if($request->get('gender_display_type') == 'SHOW')
                      <td class="ss-bold" rowspan="4">GENDER</td>
                      @endif
                      <!-- <td class="ss-bold" rowspan="2">CLASS MODE</td> -->
                      
                      @foreach($sem_modules as $mdKey=>$mod)
                      <td class="ss-bold" colspan="{{ 2*count($mod)+1 }}">{{ $mdKey }}</td>
                      @endforeach
                      <td class="ss-bold">ANNUAL</td>
                    </tr>
                    <tr>
                      <!-- <td class="ss-bold" rowspan="2">CLASS MODE</td> -->
                      @foreach($sem_modules as $mdKey=>$mod)
                      <td class="ss-bold" colspan="{{ 2*count($mod) }}">SUBJECTS</td>
                      <td class="ss-bold" rowspan="3">STATUS</td>
                      @endforeach
                      <td class="ss-bold" rowspan="3">STATUS</td>
                    </tr>
                    <tr>
                      <!-- <td class="ss-bold" rowspan="2">CLASS MODE</td> -->
                      @foreach($module_assignments as $assignment)
                      <td class="ss-bold" colspan="2">{{ $assignment->module->code }}</td>
                      @endforeach
                      
                    </tr>
                    
                    <tr>
                      
                      @foreach($module_assignments as $assignment)
                      <td class="ss-bold">MRK</td>
                      <td class="ss-bold">GRD</td>
                      @endforeach
                      
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
                         

                      @foreach($sem_modules as $mdKey=>$mods)
                          @foreach($mods as $assignment)
                            <td>
                               @foreach($student->examinationResults as $result)
                                 @if($result->module_assignment_id == $assignment->id)
                                    {{ $result->supp_score }}
                                 @endif
                               @endforeach
                            </td>
                            <td>
                               @foreach($student->examinationResults as $result)
                                 @if($result->module_assignment_id == $assignment->id)
                                    @if($result->supp_score)
                                    {{ $result->grade }}
                                    @endif
                                 @endif
                               @endforeach
                            </td>

                         @endforeach
                        @foreach($student->semesterRemarks as $rem)
                        @if($rem->semester->name == $mdKey)
                        <td>
                           {{ $rem->remark }}
                        </td>
                        @endif
                        @endforeach
                        @if(count($student->semesterRemarks) == 0)
                         <td></td>
                        @endif
                      @endforeach

                      @if($student->annualRemarks)
                      @if(count($student->annualRemarks) != 0)
                      <td>
                        @if($student->annualRemarks[0]->gpa) [GPA:{{ bcdiv($student->annualRemarks[0]->gpa,1,1) }}] @else [GPA:N/A] @endif

                        @if($student->annualRemarks[0]->remark) {{ $student->annualRemarks[0]->remark }} @else N/A @endif
                      </td>
                      @else
                      <td></td>
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
                       <td>Incomplete CWK + FE</td>
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
                       <td>Passed all Courses</td>
                     </tr>
                     <tr>
                       <td>RETAKE</td>
                       <td>Repeat Course Semester/Year No Promotion</td>
                     </tr>
                     <tr>
                       <td>CARRY</td>
                       <td>Repeat Course Semester/Year with Promotion</td>
                     </tr>
                     <tr>
                       <td>FE</td>
                       <td>Final Exam/University Exam</td>
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

      </div><!-- end of container -->


</body>
</html>


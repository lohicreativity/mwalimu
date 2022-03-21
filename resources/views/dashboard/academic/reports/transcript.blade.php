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
        padding: 2px 5px;
        font-size: 12px;
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
      .ss-float-right{
         float: right;
      }
      .ss-float-left{
        float: left;
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
        font-size: 10px;
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
     .ss-photo{
       width: 80px;
     }
     .ss-logo{
       width: 120px;
       height: auto;
     }
     .ss-line-bottom{
       border-bottom: 2px solid #000;
     }
     .ss-color-red{
        color: #f75b43;
     }
  
  </style>
</head>

      <div class="container">
        <div class="row">
            <div class="ss-letter-head  ss-center">
               <h2>THE MWALIMU NYERERE MEMORIAL ACADEMY</h2>
              </div>
        </div><!-- end of row -->
        <div class="row">
          <div class="col-md-4 ss-center">
              <div class="ss-right">
               <p class="ss-no-margin ss-font-xs">Tel: +255 (22) 2820041</p>
               <p class="ss-no-margin ss-font-xs">Fax: +255 (0) 22 2152496</p>
               <p class="ss-no-margin ss-font-xs">Email: rector@mnma.ac.tz</p>
              </div>
          </div><!-- end of col-md-3 -->
          <div class="col-md-3 ss-right">
              <img src="{{ asset('dist/img/logo.png') }}" alt="Config::get('constants.SITE_NAME') }}" class="ss-logo">
          </div><!-- end of col-md-6 -->
          <div class="col-md-3 ss-center">
              <div class="ss-right">
               <p class="ss-no-margin ss-font-xs">P. O. Box 9193</p>
               <p class="ss-no-margin ss-font-xs">Dar Es Salaam</p>
               <p class="ss-no-margin ss-font-xs">TANZANIA</p>
               <p class="ss-no-margin ss-font-xs">www.mnma.ac.tz</p>
              </div>
          </div><!-- end of col-md-3 -->
          <div class="col-md-2 ss-center">
             <img class="ss-photo" src="{{ asset('avatars/'.$student->image) }}"  onerror="this.src='{{ asset("img/user-avatar.png") }}'">
          </div><!-- end of col-md-3 -->
        </div><!-- end of row -->
        <div class="row">
          <div class="col-md-12">
            <div class="ss-letter-head  ss-center">
               <h4 class="ss-color-red">TRANSCRIPT OF EXAMINATION RESULTS</h4>
              </div>
            </div>
        </div><!-- end of row -->
             <div class="row">
                <div class="col-md-12"> 

                 <table class="table table-bordered table-condensed">
                    <tr>
                      <td><strong>NAME:</strong> {{ strtoupper($student->first_name) }} {{ strtoupper($student->middle_name) }} {{ strtoupper($student->surname) }}</td>
                      <td><strong>SEX:</strong> @if($student->gender == 'M') MALE @else FEMALE @endif</td>
                      <td><strong>Reg. No:</strong> {{ $student->registration_number }}</td>
                    </tr>
                    <tr>
                      <td><strong>CITIZENSHIP:</strong> {{ $student->nationality }}</td>
                      <td colspan="2"><strong>ADDRESS:</strong> {{ $student->applicant->address }}</td>
                    </tr>
                    <tr>
                      <td><strong>DATE OF BIRTH:</strong> {{ App\Utils\DateMaker::toStandardDate(App\Utils\DateMaker::toDashedDate($student->birth_date)) }}</td>
                      <td colspan="2"><strong>ADMITTED:</strong> {{ $student->registration_year }}</td>
                    </tr>
                    <tr>
                      <td colspan="3"><strong>CAMPUS:</strong> {{ strtoupper($student->campusProgram->campus->name) }}</td>
                    </tr>
                    <tr>
                      <td colspan="3"><strong>PROGRAMME:</strong> {{ strtoupper($student->campusProgram->program->name) }}</td>
                    </tr>
                    <tr>
                      <td colspan="3"><strong>AWARD LEVEL:</strong> {{ strtoupper($student->campusProgram->program->ntaLevel->name ) }} <span class="ss-italic">(Programme Accredited by the National Council of Technical Education)</span></td>
                    </tr>
                 </table>
                </div><!-- end of col-md-12 -->
             </div><!-- end of row -->
                
                
                @foreach($years_of_studies as $yk=>$year) 
                 @foreach($semesters as $key=>$semester)

                <div class="row">
                <div class="col-md-12"> 

                 <table class="table table-bordered table-condensed">
                    <thead>
                      <tr>
                        <th colspan="6">@if($yk == 1) FIRST @elseif($yk == 2) SECOND @elseif($yk == 3) THIRD @endif YEAR EXAMINATION RESULTS: {{ strtoupper($semester->name) }}</th>
                      </tr>
                      <tr>
                        <th>Code</th>
                        <th>Module Name</th>
                        <th>Credits</th>
                        <th>Grade</th>
                        <th>Points</th>
                        <th>GPA</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $count = 1;
                      @endphp
                      @foreach($year[$semester->name]['results'] as $result)
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id)

                         @if($result->retakeHistory)
                           @if(count($result->retakeHistory->retakableResults) != 0)

                           @foreach($result->retakeHistory->retakableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>{{ $res->moduleAssignment->module->credit }}</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ ($res->point*$res->moduleAssignment->module->credit) }}</td>
                                    <td></td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @elseif($result->carryHistory)
                           @if(count($result->carryHistory->carrableResults) != 0)

                           @foreach($result->carryHistory->carrableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>{{ $res->moduleAssignment->module->credit }}</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ ($res->point*$res->moduleAssignment->module->credit) }}</td>
                                    <td></td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @else
                         <tr>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          <td>{{ $result->moduleAssignment->module->credit }}</td>
                          <td>{{ $result->grade }}</td>
                          <td>{{ ($result->point*$result->moduleAssignment->module->credit) }}</td>
                          <td></td>
                        </tr>
                         @endif
                         @endif
                      @endforeach

                      
                     @foreach($semester->remarks as $remark)
                     @if($remark->student_id == $student->id && $remark->semester_id == $semester->id && $yk == $remark->year_of_study)
                      <tr>
                        <td colspan="2" class="ss-bold">SUB TOTAL</td>
                        <td class="ss-bold">{{ $remark->credit }}</td>
                        <td></td>
                        <td class="ss-bold">{{ $remark->point }}</td>
                        <td class="ss-bold">{{ bcdiv($remark->gpa,1,1) }}</td>
                      </tr>
                      @endif
                      @endforeach
                    </tbody>
                 </table>
               </div><!-- end of col-md-12 -->
             </div><!-- end of row -->
             

                 @endforeach
                 @endforeach
                 <div class="row">
                   <div class="col-md-12">
                    <p class="ss-bold ss-line-bottom">OVERALL GPA: @if($overall_gpa) {{ $overall_gpa }} @else N/A @endif <span class="ss-float-right">CLASSFICATION: {{ $overall_remark }}</span></p>
                    <br>
                   </div><!-- end of col-md-12 -->
                 </div><!-- end of row -->

                 <div class="row ss-center">
                   <div class="col-md-4">
                     <p class="ss-margin-top">...........................</p>
                     <p class="ss-bold">Deputy Rector Academic</p>
                   </div>
                   <div class="col-md-4">
                     <p class="ss-margin-top">...........................</p>
                     <p class="ss-bold">Date</p>
                   </div>
                   <div class="col-md-4">
                     <p class="ss-margin-top">...........................</p>
                     <p class="ss-bold">Examination Officer</p>
                   </div>
                </div>

                 <div class="row">
                   <div class="col-md-12">
                     <h3>KEYS</h3>
                     <p>1. This transcript is valid if and only if it bears the Academy Stamp</p>
                     <p>2. Key for Subject/Module Units: ONE UNIT IS EQUIVALENT TO TEN CONTACT HOURS. POINTS = GRADE MULTIPLIED BY NUMBER OF UNITS</p>
                     <p>3. Key to the Grades and other symbols: SEE THE TABLE BELOW</p>
                     <table class="table table-bordered table-condensed ss-center">
                        <tr>
                          <td class="ss-bold ss-left">Grade</td>
                          @foreach($grading_policies as $policy)
                          <td class="ss-bold">{{ $policy->grade }}</td>
                          @endforeach
                        </tr>
                        <tr>
                          <td class="ss-bold ss-left">Marks</td>
                          @foreach($grading_policies as $policy)
                          <td>{{ round($policy->min_score) }}-{{ round($policy->max_score) }}</td>
                          @endforeach
                        </tr>
                        <tr>
                          <td class="ss-bold ss-left">Grade Points</td>
                          @foreach($grading_policies as $policy)
                          <td>{{ $policy->point }}</td>
                          @endforeach
                        </tr>
                        <tr>
                          <td class="ss-bold ss-left">Remarks</td>
                          @foreach($grading_policies as $policy)
                          <td>{{ $policy->remark }}</td>
                          @endforeach
                        </tr>
                     </table>
                     <br>
                     <p>4. Key to classification of Awards: SEE THE TABLE BELOW</p>
                     <table class="table table-condensed table-bordered ss-center">
                       <tr>
                          <td class="ss-bold">Overall GPA</td>
                          <td class="ss-bold">3.5 - 4.0</td>
                          <td class="ss-bold">3.0 - 3.4</td>
                          <td class="ss-bold">2.0 - 2.9</td>
                       </tr>
                       <tr>
                         <td>Class</td>
                         <td>FIRST CLASS</td>
                         <td>SECOND CLASS</td>
                         <td>PASS</td>
                       </tr>
                     </table>
                   </div><!-- end of col-md-12 -->
                 </div><!-- end of row -->


      </div><!-- end of container -->


</body>
</html>

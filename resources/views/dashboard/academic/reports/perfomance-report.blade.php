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
     .ss-photo{
       width: 120px;
     }
     .ss-logo{
       width: 120px;
       height: auto;
     }
  
  </style>
</head>

      <div class="container">
        <div class="row">
          <div class="col-md-3 ss-center">
             <img src="{{ asset('dist/img/logo.png') }}" alt="Config::get('constants.SITE_NAME') }}" class="ss-logo">
          </div><!-- end of col-md-3 -->
          <div class="col-md-6 ss-center">
             <div class="ss-letter-head  ss-center">
               <h3>MWALIMU NYERERE MEMORIAL ACADEMY</h3>
               <h3>{{ $student->campusProgram->program->department->name }}</h3>
               <h3>{{ $student->campusProgram->program->name }}</h3>
               <h3>STATEMENT OF EXAMINATION RESULTS</h3>
              </div>
          </div><!-- end of col-md-6 -->
          <div class="col-md-3 ss-center">
             <img class="ss-photo" src="{{ asset('avatars/'.$student->image) }}"  onerror="this.src='{{ asset("img/user-avatar.png") }}'">
          </div><!-- end of col-md-3 -->
        </div><!-- end of row -->
                 @php
                    $programIds = [];
                 @endphp
                 @foreach($results as $res)
                   @php
                      $programIds[] = $res->moduleAssignment->programModuleAssignment->id;
                   @endphp
                 @endforeach
                
                 
                 @foreach($semesters as $key=>$semester)

                   @if(count($semester->remarks) != 0)
                <div class="row">
                <div class="col-md-12">
                 <h4 class="ss-no-margin">{{ $semester->name }}</h4>
                 <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>SN</th>
                        <th>Code</th>
                        <th>Module Name</th>
                        <th>C/Work</th>
                        <th>Final</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Remark</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $count = 1;
                      @endphp
                    @foreach($core_programs as $program)
                        @if($semester->id == $program->semester_id && !in_array($program->id,$programIds))
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $program->module->code }}</td>
                          <td>{{ $program->module->name }}</td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                        @php
                          $count += 1;
                        @endphp
                       @endif
                      @foreach($results as $result)
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)

                         @if($result->retakeHistory)
                           @if(count($result->retakeHistory->retakableResults) != 0)

                           @foreach($result->retakeHistory->retakableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
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
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @else
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->course_work_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->final_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->total_score }} @else {{ $result->supp_score }}@endif</td>
                          <td>{{ $result->grade }}</td>
                          <td>{{ $result->final_exam_remark }}</td>
                        </tr>
                          @php
                            $count += 1;
                          @endphp
                         @endif
                         @endif
                      @endforeach
                    @endforeach
                    @foreach($optional_programs as $program)
                        @if($semester->id == $program->semester_id && !in_array($program->id,$programIds))
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $program->module->code }}</td>
                          <td>{{ $program->module->name }}</td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                        @php
                          $count += 1;
                        @endphp
                       @endif
                       @foreach($results as $result)
                         @if($result->moduleAssignment->programModuleAssignment->semester_id == $semester->id && $result->moduleAssignment->programModuleAssignment->id == $program->id)

                         @if($result->retakeHistory)
                           @if(count($result->retakeHistory->retakableResults) != 0)

                           @foreach($result->retakeHistory->retakableResults as $key=>$res)
                              @if($key == 0)
                                 <tr>
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
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
                                    <td>{{ $count }}</td>
                                    <td>{{ $res->moduleAssignment->module->code }}</td>
                                    <td>{{ $res->moduleAssignment->module->name }}</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->course_work_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->final_score }} @else N/A @endif</td>
                                    <td>@if(!$res->supp_processed_at) {{ $res->total_score }} @else {{ $res->supp_score }}@endif</td>
                                    <td>{{ $res->grade }}</td>
                                    <td>{{ $res->final_exam_remark }}</td>
                                  </tr>
                                    @php
                                      $count += 1;
                                    @endphp
                              @endif
                           @endforeach

                           @endif
                         @else
                         <tr>
                          <td>{{ $count }}</td>
                          <td>{{ $result->moduleAssignment->module->code }}</td>
                          <td>{{ $result->moduleAssignment->module->name }}</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->course_work_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->final_score }} @else N/A @endif</td>
                          <td>@if(!$result->supp_processed_at) {{ $result->total_score }} @else {{ $result->supp_score }}@endif</td>
                          <td>{{ $result->grade }}</td>
                          <td>{{ $result->final_exam_remark }}</td>
                        </tr>
                          @php
                            $count += 1;
                          @endphp
                         @endif
                         @endif
                      @endforeach
                     @endforeach
                    </tbody>
                 </table>
               </div><!-- end of col-md-12 -->
             </div><!-- end of row -->
                 <div class="row">
                 <div class="col-md-6">
                 <p class="ss-bold">Summary: {{ $semester->name }}</p>
                 <table class="table table-bordered">
                   <thead>
                      <tr>
                        <th>Remark</th>
                        <th>GPA</th>
                      </tr>
                   </thead>
                   <tbody>
                      @foreach($semester->remarks as $remark)

                      <tr>
                         <td><strong>{{ $remark->remark }}</strong>
                             @if($remark->serialized) @if(!empty(unserialize($remark->serialized)['supp_exams'])) [{{ implode(', ',unserialize($remark->serialized)['supp_exams']) }}] @endif @endif
                             @if($remark->serialized) @if(!empty(unserialize($remark->serialized)['retake_exams'])) [{{ implode(', ',unserialize($remark->serialized)['retake_exams']) }}] @endif @endif
                             @if($remark->serialized) @if(!empty(unserialize($remark->serialized)['carry_exams'])) [{{ implode(', ',unserialize($remark->serialized)['carry_exams']) }}] @endif @endif
                         </td>
                         <td>@if($remark->gpa) {{ bcdiv($remark->gpa,1,1) }} @else N/A @endif</td>
                      </tr>
                      @endforeach
                   </tbody>
                 </table>
               </div>
                 
                   @if($annual_remark && $key == (count($semesters)-1))
                   <div class="col-md-6">
                    <p class="ss-bold">Annual Remark</p>
                     <table class="table table-bordered">
                   <thead>
                      <tr>
                        <th>Remark</th>
                        <th>GPA</th>
                      </tr>
                   </thead>
                   <tbody>
                      <tr>
                        <td><strong>{{ $annual_remark->remark }}</strong></td>
                        <td>@if($annual_remark->gpa) {{ bcdiv($annual_remark->gpa,1,1) }} @else N/A @endif</td>
                       </tr>
                   </tbody>
                 </table>
                 <br>
               </div><!-- end of col-md-6 -->
                 
                  @endif
                  </div><!-- end of row -->
                 @endif
                 @endforeach

                 <div class="row">
                   <div class="col-md-12">
                     <h3>KEYS</h3>
                     <p>1. This transcript is valid if and only if it bears the University Stamp</p>
                     <p>2. Key for Subject/Module Units: ONE UNIT IS EQUIVALENT TO TEN CONTACT HOURS. POINTS = GRADE MULTIPLIED BY NUMBER OF UNITS</p>
                     <p>3. Key to the Grades and other symbols for University Examinations: SEE THE TABLE BELOW</p>
                     <table class="table table-bordered table-condensed ss-center">
                        <tr>
                          <td class="ss-bold">Grade</td>
                          @foreach($grading_policies as $policy)
                          <td class="ss-bold">{{ $policy->grade }}</td>
                          @endforeach
                        </tr>
                        <tr>
                          <td class="ss-bold">Marks</td>
                          @foreach($grading_policies as $policy)
                          <td>{{ $policy->min_score }}-{{ $policy->max_score }}</td>
                          @endforeach
                        </tr>
                        <tr>
                          <td class="ss-bold">Grade Points</td>
                          @foreach($grading_policies as $policy)
                          <td>{{ $policy->point }}</td>
                          @endforeach
                        </tr>
                        <tr>
                          <td class="ss-bold">Remarks</td>
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
                          <td class="ss-bold">3.50 - 4.00</td>
                          <td class="ss-bold">3.00 - 3.49</td>
                          <td class="ss-bold">2.00 - 2.99</td>
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

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
               <h3>{{ $program->name }} ({{ $study_academic_year->academicYear->year }})</h3>
               <h3>EXAMINATION RESULTS</h3>
              </div>
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
                      <!-- <td class="ss-bold" rowspan="2">CLASS MODE</td> -->
                      @foreach($module_assignments as $assignment)
                      <td class="ss-bold" colspan="4">{{ $assignment->module->code }} ({{ $assignment->module->credit }})</td>
                      @endforeach
                      <td colspan="2"></td>
                    </tr>
                    <tr>
                      
                      @foreach($module_assignments as $assignment)
                      <td class="ss-bold">CW</td>
                      <td class="ss-bold">FN</td>
                      <td class="ss-bold">TT</td>
                      <td class="ss-bold">GD</td>
                      @endforeach
                      
                      <td class="ss-bold">GPA</td>
                      <td class="ss-bold">REMARK</td>
                    </tr>
                    @php
                       $modules = [];
                    @endphp

                    @foreach($students as $key=>$student)
                    <tr>
                      <td>{{ $key+1 }}</td>
                      @if($request->get('reg_display_type') == 'SHOW')
                      <td>{{ $student->registration_number }}</td>
                      @endif
                      @if($request->get('name_display_type') == 'SHOW')
                      <td>{{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name}}</td>
                      @endif
                      @foreach($module_assignments as $assignment)

                          @php
                            $modules[$assignment->module->code]['name'] = $assignment->module->name; 
                            $modules[$assignment->module->code]['grades'] = $assignment->module->name; 
                          @endphp
                      
                          @foreach($student->examinationResults as $result)
                            @if($result->module_assignment_id == $assignment->id)
                      
                            <td @if($result->course_work_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->course_work_score }}</td>
                            <td @if($result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->final_score }}</td>
                            <td @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->total_score }}</td>
                            <td @if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL') class="ss-custom-grey" @endif>{{ $result->grade }}</td>
                            @endif
                          @endforeach
                      
                      @endforeach
                      @for($i = 0; $i < count($module_assignments)-count($student->examinationResults); $i++)
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

      </div><!-- end of container -->


</body>
</html>


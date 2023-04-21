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
  
  </style>
</head>

      <div class="container">
        <div class="row">
          <div class="col-md-12">
              <div class="ss-letter-head  ss-center">
               <h3>THE MWALIMU NYERERE MEMORIAL ACADEMY</h3>
               <h3>{{ $campus->name }}</h3>
               <h3>{{ $department->name }}</h3>
               <h3>{{ $program->name }} - YEAR {{ $year_of_study }}</h3>
			   <h3>ACADEMIC YEAR {{ $study_academic_year->academicYear->year }} - SEMESTER {{$semester}}</h3>
               <h3>{{ $module->name }} - {{ $module->code }}</h3>
              </div>
               <div class="table-responsive ss-margin-bottom">
                  <table class="table table-condensed table-bordered">
                    <tr>
                      <td class="ss-bold" colspan="3"></td>
                      <td class="ss-bold ss-center" colspan="{{ count($assessment_plans) }}">COURSEWORK COMPONENTS</td>
                      <td class="ss-bold"></td>
                      <td class="ss-bold"></td>
                    </tr>
                    <tr>
                      <td class="ss-bold">SN</td>
                      <td class="ss-bold">Name</td>
                      <td class="ss-bold">Reg. No.</td>
                      @foreach($assessment_plans as $plan)
                      <td class="ss-bold">{{ $plan->name }}</td>
                      @endforeach
                      <td class="ss-bold">Total</td>
                      <td class="ss-bold">Remarks</td>					  
                      <td class="ss-bold ss-center">Signature</td>
                    </tr>
                    @foreach($registrations as $key=>$reg)
                    <tr>
                      <td>{{ $key+1 }}</td>
                      <td>{{ $reg->student->surname }}, {{ ($reg->student->first_name) }} {{ substr($reg->student->middle_name,0,1) }}</td>
                      <td>{{ $reg->student->registration_number }}</td>
                        @php 
                            $cw_available = true;
                            $empty_columns = count($assessment_plans) - count($reg->student->courseWorkResults);
                            $cw_total = 0;
                        @endphp
                      @foreach($assessment_plans as $plan)
                        @php 
							$cw_available = false; 
						@endphp
                        @foreach($reg->student->courseWorkResults as $result)
                          @if($result->assessment_plan_id == $plan->id)
                          <td>{{ $result->score }}</td>
                            @php 
                               $cw_available = true; 
                               $cw_total += $result->score;
                            @endphp
                          @endif
                        @endforeach
                         @if(!$cw_available)
                            <td>-</td>
                         @endif
                      @endforeach
                    
                      <td>@if(count($reg->student->courseWorkResults) == count($assessment_plans) && $course_work_processed) {{ $cw_total }} @else - @endif</td>
					  <td>
						@if($cw_total < $cw_pass_mark) Fail @else Pass @endif
					  </td>
                      <td></td>
                    </tr>
                    @endforeach
                  </table>
                </div><!-- end of table-responsive -->
                
                


          </div><!-- end of col-md-12 -->
        </div><!-- end of row -->

      </div><!-- end of container -->


</body>
</html>


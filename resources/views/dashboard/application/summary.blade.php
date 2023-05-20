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
     .ss-line-bottom{
       border-bottom: 2px solid #000;
     }
  
  </style>
</head>
<body>
      <div class="container">
        <div class="row">
           <div class="col-md-12 ss-center">
           <h3 class="ss-bold">THE MWALIMU NYERERE MEMORIAL ACADEMY</h3>
             <img src="{{ asset('dist/img/logo.png') }}" alt="Config::get('constants.SITE_NAME') }}" width="100px">
             <h3 class="ss-bold">APPLICATION SUMMARY</h3>
             <h3 class="ss-bold">{{ strtoupper($applicant->intake->name) }} INTAKE - {{ date('Y',strtotime($applicant->applicationWindow->begin_date)) }}</h3>
           </div>
        </div>
        <div class="row">
          <div class="col-md-12">
             <br><span style="font-size: 16pt; font-weight:bold">Personal Information</span><hr>

             <table class="table table-hover">
               <tr>     
                 <td style="font-weight:bold">Names:</td>
                 <td>{{ $applicant->surname }}, {{ ucwords(strtolower($applicant->first_name)) }} {{ ucwords(strtolower($applicant->middle_name)) }}</td>
               </tr>
               <tr>            
                 <td style="font-weight:bold">Sex:</td>
                 <td> @if($applicant->gender == 'F') Female @elseif($applicant->gender == 'M') Male @endif</td>
               </tr> 
               <tr>
                 <td style="font-weight:bold">Nationality:</td>
                 <td>{{ ucwords(strtolower($applicant->nationality)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Address:</td>
                 <td>{{ $applicant->address }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Country:</td>
                 <td>{{ ucwords(strtolower($applicant->country->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Region:</td>
                 <td>{{ ucwords(strtolower($applicant->region->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">District:</td>
                 <td>{{ ucwords(strtolower($applicant->district->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Ward:</td>
                 <td>{{ ucwords(strtolower($applicant->ward->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Street:</td>
                 <td>{{ ucwords(strtolower($applicant->street)) }}</td>
               </tr>
             </table>

             <br><span style="font-size: 16pt; font-weight:bold">Next of Kin Information</span><hr>
             <table class="table table-hover">
               <tr>
                 <td style="font-weight:bold">Names:</td>
                 <td>{{ $applicant->nextOfKin->surname }}, {{ ucwords(strtolower($applicant->nextOfKin->first_name)) }} {{ ucwords(strtolower($applicant->nextOfKin->middle_name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Sex:</td>
                 <td>@if($applicant->nextOfKin->gender == 'F') Female @elseif($applicant->nextOfKin->gender == 'M') Male @endif</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Relationship:</td>
                 <td>{{ $applicant->nextOfKin->relationship }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Nationality:</td>
                 <td>{{ ucwords(strtolower($applicant->nextOfKin->nationality)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Email:</td>
                 <td>@if(empty($applicant->nextOfKin->email))N/A @else $applicant->nextOfKin->email @endif</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Phone:</td>
                 <td>{{ $applicant->nextOfKin->phone }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Address:</td>
                 <td>{{ $applicant->nextOfKin->address }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Country:</td>
                 <td>{{ ucwords(strtolower($applicant->nextOfKin->country->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Region:</td>
                 <td>{{ ucwords(strtolower($applicant->nextOfKin->region->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">District:</td>
                 <td>{{ ucwords(strtolower($applicant->nextOfKin->district->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Ward:</td>
                 <td>{{ ucwords(strtolower($applicant->nextOfKin->ward->name)) }}</td>
               </tr>
               <tr>
                 <td style="font-weight:bold">Street:</td>
                 <td>{{ ucwords(strtolower($applicant->street)) }}</td>
               </tr>
               
             </table>

             <br><span style="font-size: 16pt; font-weight:bold">Programmes Selected</span><hr>
             <table class="table table-hover">
              @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->selections,1))
               <tr>
                 <td>1<sup>st</sup> Choice</td>
                 @foreach($selections as $selection)
                   @if($selection->order == 1)
                  <td>{{ $selection->campusProgram->program->name }}</td>
                   @endif
                 @endforeach
               </tr>
              @endif
              @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->selections,2))
               <tr>
                 <td>2<sup>nd</sup> Choice</td>
                 @foreach($selections as $selection)
                   @if($selection->order == 2)
                 <td>{{ $selection->campusProgram->program->name }}</td>
                   @endif
                 @endforeach
               </tr>
               @endif
               @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->selections,3))
               <tr>
                 <td>3<sup>rd</sup> Choice</td>
                 @foreach($selections as $selection)
                   @if($selection->order == 3)
                 <td>{{ $selection->campusProgram->program->name }}</td>
                   @endif
                 @endforeach
               </tr>
               @endif
               @if(App\Domain\Application\Models\ApplicantProgramSelection::hasSelectedChoice($applicant->selections,4))
               <tr>
                 <td>4<sup>th</sup> Choice</td>
                 @foreach($selections as $selection)
                   @if($selection->order == 4)
                 <td>{{ $selection->campusProgram->program->name }}</td>
                   @endif
                 @endforeach
               </tr>
               @endif
              </table>

              @if(count($applicant->nectaResultDetails) != 0)

              <br><span style="font-size: 16pt; font-weight:bold">Results</span><hr>
              <table class="table table-condensed">
                @foreach($applicant->nectaResultDetails as $detail)
                <tr>
                  <td>Index Number - @if($detail->exam_id == 1) 0-Level @elseif($detail->exam_id == 2) A-Level @endif </td>
                  <td>Division</td>
                  <td>Points</td>
                </tr>
                <tr>
                  <td>{{ $detail->index_number }}</td>
                  <td>{{ $detail->division }}</td>
                  <td>{{ $detail->points }}</td>
                </tr>
                @endforeach
              </table>
              @endif

              @if(count($applicant->nacteResultDetails) != 0)
              <h3>NACTE Results</h3>
              <table class="table table-bordered table-condensed">
                @foreach($applicant->nacteResultDetails as $detail)
                <tr>
                  <td>AVN</td>
                  <td>GPA</td>
                </tr>
                <tr>
                  <td>{{ $detail->avn }}</td>
                  <td>{{ $detail->diploma_gpa }}</td>
                </tr>
                @endforeach
              </table>
              @endif
         </div><!-- end of col-md-12 -->
       </div><!-- end of row -->
      </div><!-- end of container -->


</body>
</html>

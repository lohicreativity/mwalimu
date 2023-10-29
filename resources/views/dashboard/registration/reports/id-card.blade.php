<!DOCTYPE html>
<html>
<head>
  <title></title>
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
     .ss-signature{
        height: 120px;
        width: auto;
     }
     .ss-line-bottom{
       border-bottom: 2px solid #000;
     }
     .ss-uppercase{
        text-transform: uppercase;
     }
     .ss-margin-top{
        margin-top: 20px;
     }
     .ss-color-blue{
        color: #371261;
     }
     p, li{
        text-align: justify;
        margin-bottom: 10px;
     }
     @page {
        header: page-header;
        footer: page-footer;
        text-align: center;
        border-top: 2px solid #000;
      }
  </style>
</head>
<body>

   <div id="ss-id-card" class="ss-id-card" style="width: 750px; height: 400px; background-image: url({{ asset('img/mnma-id-bg.png') }});">

     <div class="row" style="padding:20px;">
        <div class="col-md-3 ss-center" style="text-align: center; padding-top: -5px;">
          <img src="{{ asset('dist/img/logo.png')}}" class="ss-logo" style="width: 80px; text-align: center;">
        </div>
        <div class="col-md-9">
           <h3 style="margin-top: 0px;" class="text-center">THE MWALIMU NYERERE MEMORIAL ACADEMY</h3>
        </div>
     </div>
     <hr style="border:20px solid #1b2066; margin-top: -20px;">
     <!-- <div class="row">
        <div class="col-md-12">
            
        </div>
     </div> -->
     <div class="container" style="position: relative; z-index: 1000;">
     <div class="row" style="margin-top: -20px;">
        <div class="col-md-3" style="padding: 5px;">
          @if(file_exists(public_path().'/avatars/'.$student->image))
          <img src="{{ asset('avatars/'.$student->image)}}" class="ss-logo" style="text-align: center; width: 100px;">
          @elseif(file_exists(public_path().'/uploads/'.$student->image))
          <img src="{{ asset('uploads/'.$student->image)}}" class="ss-logo" style="text-align: center; width: 100px;">
          @endif
        </div>
        <div class="col-md-9" style="float:right; padding: 5px; margin-top: -120px;">
           <h5 style="margin: 0px 0px 0px 25px;">REGNO: <span style="font-style: italic;">{{ $student->registration_number }}</span></h5>
           <h5 style="margin: 0px 0px 0px 25px;">NAME: <span style="font-style: italic;">{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</span></h5>
           <h5 style="margin: 0px 0px 0px 25px;">MOBILE: <span style="font-style: italic;">{{ $student->phone }}</i></h5>
           <h5 style="margin: 0px 0px 0px 25px;">VALID TO: <span style="font-style: italic;">{{ App\Utils\DateMaker::toStandardDate($study_academic_year->end_date) }}</span></h5>
           <h5 style="margin: 0px 0px 0px 25px;">SIGNATURE:
           <img src="{{ asset('signatures/'.$student->signature) }}" style="width: 100px; height: auto; margin-top: 5px;"></h5>
        </div>
     </div> 
   </div>
    </div>
    <div class="row" style="background-color:#1b2066; width:100%; margin-top: -15px; position:absolute;">
        <div class="col-md-6" style=" margin-top: -20px;"> @if($semester->name == 'Semester 1')
            <h2 style="text-align:left; color: white; font-weight: bold; padding-left: 5px; font-size: 20px;">Semester One</h2>
            @else
                <h2 style="text-align:left; color: white; font-weight: bold; padding-left: 5px; font-size: 20px;">Semester Two</h2>
            @endif
        </div>
        <div class="col-md-6" style="text-align: right;">
            <h2 style="float: right;  -webkit-text-stroke: 1px white; font-weight: bold; color: red; padding-right:5px; font-size: 20px;">{{ $student->campusProgram->campus->name }}</h2>
        </div>
    </div>  
   <!-- </div>
    <div id="semester" style="width: 710px; background-color:#1b2066; padding: 0px;">
        <div class="row">
                <div class="col-md-6"> @if($semester->name == 'Semester 1')
                    <h5 style="text-align:left; text-shadow: 0px 0px 5px blue; font-weight: bold; margin: 20px 0px 0px 0px; color:white;">Semester One</h5>
                    @else
                        <h5 style="text-align:left;text-shadow: 0px 0px 5px blue; font-weight: bold; margin: 20px 0px 0px 0px; color:white;">Semester Two</h5>
                    @endif
                </div>
                <div class="col-md-6" style="text-align: right;">
                    <h5 style="float: right; text-shadow: 0px 0px 5px brown; font-weight: bold; color: red; margin: 20px 0px 0px 0px;">{{ $student->campusProgram->campus->name }}</h5>
                </div>
        </div>
    </div> -->
   <pagebreak>
     <div id="ss-id-card-back" class="ss-id-card" style="width: 750px; height: 400px; background-color: #FFF; ">
       <div class="container">
       <div class="row">
          <div class="col-md-12">
             <h3 style="margin: 0px 0px 0px 10px; text-align:center;">CAUTION</h1>
          </div>
       </div>
       <div class="row">
          <div class="col-md-8">
            <p style="margin: 0px 0px 0px 10px; font-size: 12px;">This identity card is the property of</p>
            <h6 style="margin: 0px 0px 10px 10px; font-size: 12px;">THE MWALIMU NYERERE MEMORIAL ACADEMY</h6>
            <p style="margin: 0px 0px 0px 10px; font-size: 12px;">1. Use of this card is subject to the card holder agreement</p>
            <p style="margin: 0px 0px 0px 10px; font-size: 12px;">2. Card should be returned at the beginning of each semester</p>

          </div>
          <div class="col-md-4">
            @php
            $courseCode = explode('.', $student->campusProgram->code);
            $yearofstudy = $student->year_of_study;
            $yearValue = '';
            if($yearofstudy = 1){
                $yearValue = $yearofstudy."st";
            }elseif($yearofstudy == 2){
                $yearValue = $yearofstudy."nd";
            }elseif($yearofstudy == 3){
                $yearValue = $yearofstudy."rd";
            }
            $qrCodeData = "Time: ".Carbon\Carbon::parse($student->created_at)->format('m/d/Y H:i')."\n"." ID:".$student->registration_number."\n".$student->surname.", ".$student->first_name." ".$student->middle_name."\n"." Course:".$courseCode[0].$courseCode[1]."\n".substr($student->registration_year, 2,2)."-".strtoupper(substr($student->applicant->intake->name, 0, 3))."-".$yearValue."\n".$student->phone;

            @endphp

            <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($qrCodeData)) !!} " style="margin-left: 20px; ">

          </div>
       </div>
       <div class="row">
            <p style="text-align:center;">  
             @php

             $footer = "PHONE NO: ".str_replace('255', '0',$student->phone)." ";
             $reg_no = str_replace('/', '-', $registration_no)
             @endphp
            <strong>{{ $footer }} <span style="font-size:13px;"> {{ $reg_no }} </span></strong>
            </p>
       </div>
     </div>
     </div>

    <script type="text/javascript">
       document.getElementById('ss-id-card').addEventListener('click',function(e){
             window.print();
       });
    </script>
</body>
</html>

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
  
  </style>
</head>

      <div class="container">
        <div class="row">
          <div class="col-md-12">
             <div class="ss-letter-head  ss-center">
               <h2>THE UNITED REPUBLIC OF TANZANIA</h2>
              </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-3 ss-center">
             <img src="{{ public_path('/img/coa-tz.png') }}" alt="Config::get('constants.SITE_NAME') }}" class="ss-logo">
          </div><!-- end of col-md-3 -->
          <div class="col-md-6 ss-center">
             <div class="ss-center">
               <h4>THE MINISTRY OF EDUCATION, SCIENCE AND TECHNOLOGY</h4>
               <h4>THE MWALIMU NYERERE MEMORIAL ACADEMY</h4>
              </div>
          </div><!-- end of col-md-6 -->
          <div class="col-md-3 ss-center">
             <img src="{{ public_path('/dist/img/logo.png') }}" alt="Config::get('constants.SITE_NAME') }}" class="ss-logo">
          </div><!-- end of col-md-3 -->
        </div><!-- end of row -->

        
        <div class="row">
            <div class="col-md-3">
               <p>Our Ref: MNMA/ADMN/GEN/45</p>
               <p>.............................................</p>
               <p>.............................................</p>              
            </div>
            <div class="col-md-6">

            </div>
            <div class="col-md-3">
              <p>31st August 2021</p>
            </div>
        </div><!-- end of row -->
        
        <div class="row">
           <div class="col-md-12">
              <h4>RE: ADMISSION INTO {{ $program_name }} FOR THE ACADEMIC YEAR {{ $study_year }}</h4>

              <p>I am pleased to inform you that you have been selected to join the Academy for {{ $program_duration }} year pursuing {{ $program_name }} You are required to report at the Academy on Monday 25th October 2021 ready for registration and One Week for Orientation Programme that will commence on Monday 25th October 2021. Please note that all 1st year students are required to attend the Orientation Programme. Classes will commence immediately on 1st November 2021.</p>

              <p>Please observe the following instructions.</p>
              <ol>
<li>  That you should pay annual tuition fee of {{ $program_fee_words }} ({{ $currency }} {{ number_format($program_fee) }} ) only (accommodation exclusive)*. Fees may be paid either in one or two installments at the rate of 60%, equivalent to {{ $currency }} {{ number_format(0.6*$program_fee) }} (payable during the First Semester) and 40% equivalent to {{ $currency }} {{ number_format(0.4*$program_fee) }} (Second Semester). Each installment should be paid at the beginning of each Semester. Also you should pay compulsory fee of {{ $currency }} {{ number_format($medical_insurance_fee) }} for Medical Insurance (Control number to be obtained from NHIF website) as well as {{ $currency }} {{ number_format($nacte_quality_assurance_fee) }} for NACTE quality assurance fee, {{ $currency }} {{ number_format($practical_training_fee) }} for Practical Training, {{ $currency }} {{ number_format($students_union_fee) }} for students union, {{ $currency }} {{ number_format($caution_money_fee) }} Caution Money, {{ $currency }} {{ number_format($medical_examination_fee) }} for Medical Examination, {{ $currency }} {{ number_format($registration_fee) }} for Registration and {{ $currency }} {{ number_format($identity_card_fee) }} for Identity Card at the beginning of first semester once a year. Parents/Sponsors/Students are requested to pay the fees through the Online System+ available at the Academy Website www.mnma.ac.tz.</li>

<li>  That you should present yourself to the Admissions Office for formal registration after paying the required fees. Failure to do so will result in the withdrawal of your admission. Admission can neither be postponed nor deferred to the next academic year. Please note that no student will be allowed to attend classes without paying the required fees and completing the registration process.</li>

<li>  That you should produce your medical check-up form duly filled by a qualified Government Medical Officer to the Admissions Officer.</li>

<li>  That you should bring your original certificates plus two copies of each of your certificates and transcripts (the original certificate will be returned to you after verification). Certificates should include birth certificate, Form 4, 6, and/or Diploma.</li>

<li>  That you will be a full time student and if you are a Government or Public Institution/Organization employee, you will have to produce evidence that your employer has released you and is ready to continue supporting you.</li>

<li>  That you should submit two stamp size photographs to the Admissions Officer.</li>

<li>  Kindly note that candidates who will fail to register within the period of two weeks lasting from 25th October, 2021 to 7th November, 2021 will be required to pay late registration fee of {{ $currency }} {{ number_format($late_registration_fee) }} per day for a maximum of 7 days.</li>

<li>  Failure to register will lead to automatic cancellation of your admission; hence you will therefore be required to re-apply.</li>

<li>  Accommodation on campus is not guaranteed; hence candidates are not advised to effect payment of the fees of the same in advance. However, when necessary, the office of the Dean of Students shall assist students in securing their off campus accommodation.</li>

</ol>
<p>I am looking forward to your registration and a successful period of study at the Academy.</p>

<img src="{{ public_path('/img/adm-lt-sign.png') }}" alt="Signature" class="ss-signature">
           </div>
        </div><!-- end of row -->

      </div><!-- end of container -->


</body>
</html>

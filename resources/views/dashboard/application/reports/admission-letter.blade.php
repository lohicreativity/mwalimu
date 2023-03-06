<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
     .text-right {
      text-align: right;
     }
     .footer-text {
      font-size: 14px;
      border-top: 2px solid #000;
      padding-bottom: 20px;
     }
     p, li{
        text-align: justify;
        margin-bottom: 10px;
     }

     @page {
        header: page-header;
        footer: page-footer;
        padding-bottom: 20px;
      }
     
  </style>
</head>

<body>

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

        <p class="text-right">{{ now()->format('jS F Y') }}</p>
        
        <div class="row">
            <div class="col-md-6">
              <p>Our Ref: {{ strtoupper($reference_number) }}</p>
              <p>{{ $applicant->address }}</p>
              <p>{{ $applicant->region->name }}</p>    
            </div>
        </div>
        <!-- end of row -->
        
        <div class="row">
           <div class="col-md-12">
              <h4>RE: ADMISSION INTO {{ strtoupper($program_code_name) }}</h4>

              <p>Dear <strong>{{ $applicant_name }}</strong>, I am pleased to inform you that you have been selected to join the Academy for {{ $program_duration }} year(s) pursuing <strong>{{ $program_name }}</strong> at the <strong>{{ $campus_name }}</strong>. You are required to report at the Academy on <strong>{{ Carbon\Carbon::parse($commencement_date)->format('l jS F Y') }}</strong> ready for registration and a one-week orientation programme that will commence on <strong>{{ Carbon\Carbon::parse($commencement_date)->format('l jS F Y') }}</strong>. Please note that all 1<sup>st</sup> year students are required to attend the Orientation Programme. Classes will commence immediately on <strong>{{ Carbon\Carbon::parse($commencement_date)->addDays(7)->format('l jS F Y') }}</strong>.</p>

              <p>Please observe the following instructions.</p>
              <ol>
<li>  That you should pay annual tuition fee of <strong>{{ $program_fee_words }} ({{ $currency }} {{ number_format($program_fee) }}/= ) only</strong> (accommodation exclusive)*. Fees may be paid either in one or two installments at the rate of 60%, equivalent to <strong>{{ $currency }} {{ number_format(0.6*$program_fee) }}/=</strong> (First Semester) and 40% equivalent to <strong>{{ $currency }} {{ number_format(0.4*$program_fee) }}/=</strong> (Second Semester). Each installment should be paid at the beginning of each Semester.</li>

<li>That you should also pay <strong>{{ $currency }} {{ number_format($nacte_quality_assurance_fee) }}/=</strong> for Quality Assurance fee, <strong>{{ $currency }} {{ number_format($practical_training_fee) }}/=</strong> for Practical Training, <strong>{{ $currency }} {{ number_format($students_union_fee) }}/=</strong> for students union, <strong>{{ $currency }} {{ number_format($caution_money_fee) }}/=</strong> for Caution Money, <strong>{{ $currency }} {{ number_format($medical_examination_fee) }}/=</strong> for Medical Examination, <strong>{{ $currency }} {{ number_format($registration_fee) }}/=</strong> for Registration and <strong>{{ $currency }} {{ number_format($identity_card_fee) }}/=</strong> for Identity Card at the beginning of first semester.</li>

<li>That you should revisit your system account to indicate your insurance status. It is mandatory for a student to have medical insurance that will be valid for a year, starting from <strong>{{ Carbon\Carbon::parse($commencement_date)->format('l jS F Y') }}</strong>. If you do not have valid medical insurance, you may wish to pay <strong>{{ $currency }} {{ number_format($medical_insurance_fee) }}/=</strong> for medical insurance from NHIF.</li>

<li>That you should revist your system account and indicate if you would like to be considered for on campus accommodation. Please note, accommodation on campus is not guaranteed and therefore you will only be required to pay for accommodation when a room has been allocated for you. When necessary, the office of the Dean of Students shall assist you in securing your off campus accommodation.</li>

<li>That you should present yourself to the Admissions Office for formal registration after paying the required fees. Failure to do so will result in the withdrawal of your admission. Admission can neither be postponed nor deferred to the next academic year. Please note that no student will be allowed to attend classes without paying the required fees and completing the registration process.</li>

<li>  That you should bring your <strong>original certificates</strong> plus two certified copies of each of your certificates and transcripts (the original certificate will be returned to you after verification). Certificates should include birth certificate, Form 4, 6, and/or Diploma.</li>

<li>  That you should produce your <strong>medical check-up</strong> form duly filled by a qualified Government Medical Officer to the Admissions Officer.</li>

<li>  That you should submit to the Admissions Officer two recent stamp size photographs with blue background.</li>

<li>  That you will be a full time student. If you are a Government or Public Institution/Organisation employee, you will have to produce evidence that your employer has released you and is ready to continue supporting you.</li>


<li>  Kindly note that candidates who will fail to register within the period of two weeks lasting from <strong>{{ Carbon\Carbon::parse($commencement_date)->format('jS F, Y') }}</strong> to <strong>{{ Carbon\Carbon::parse($commencement_date)->addDays(14)->format('jS F, Y') }}</strong> will be required to pay late registration fee of <strong>{{ $currency }} {{ number_format($late_registration_fee) }}/=</strong> per day for a maximum of 7 days. Failure to register within this period will lead to <strong>automatic cancellation</strong> of your admission; hence you will therefore be required to re-apply.</li>

<li>The Academy is a government owned higher learning institution and therefore all payments shall be paid using control numbers. Control numbers for all the payments should be requested and obtained from your system account <strong>two weeks</strong> before commencement of the academic year.</li>

</ol>
<p>I am looking forward to your registration and a successful period of study at the Academy.</p>

<img src="{{ public_path('/img/adm-lt-sign.png') }}" alt="Signature" class="ss-signature">
           </div>
        </div><!-- end of row -->

      </div><!-- end of container -->

<htmlpagefooter name="page-footer" class="ss-center">
  <p class="footer-text">
    Kigamboni, Ferry Street, MNMA P. O. Box 9193, Dar Es Salaam. Tel: +255 (22) 2820041/47, Fax: +255 (22) 2820816. Email: rector@mnma.ac.tz Website: www.mnma.ac.tz
  </p>
</htmlpagefooter>

</body>
</html>

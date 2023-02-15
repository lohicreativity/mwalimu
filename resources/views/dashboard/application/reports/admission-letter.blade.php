<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title></title>
  
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

        <div class="d-flex justify-content-between">
            <div>
              <p>Our Ref: {{ strtoupper($reference_number) }}</p>
              <p>.............................................</p>
              <p>.............................................</p> 
            </div>
            <div>
              <span class="text-right">{{ now()->format('jS F Y') }}</span>
            </div>
        </div>


        
        <!-- <div class="row">
            <div class="col-md-4">
                     
            </div>
          

            <div class="col-md-4">
              
            </div>
        </div> -->
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

<hr>
<htmlpagefooter name="page-footer" class="ss-center">
  Kigamboni, Ferry Street, MNMA P. O. Box 9193, Dar Es Salaam. Tel: +255 (22) 2820041/47, Fax: +255 (22) 2820816. Email: rector@mnma.ac.tz Website: www.mnma.ac.tz
</htmlpagefooter>

</body>
</html>

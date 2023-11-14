<!DOCTYPE html>
<html lang="en">
<head>
  <title></title>
  <style>
      body{
         font-family: Tahoma, sans-serif;
      }
      html,body{ margin: 0; padding: 0;}
      @media print {
          @page {
              size: 3.4in 2.1in;
              margin: 0;
          }
      }
      table {border-collapse: collapse;}
      table td {padding: 0}
  </style>
</head>
<body>

<div>
    @if ($student->applicant->campus_id == 1)
        @if ($tuition_payment_check)
            <img style="position:absolute;top:0.00in;left:0.00in;width:3.4in;height:2.1in" src="{{ asset('img/mnma-id-bg-semi 1&2 Kivukoni.png') }}" />
        @else
            <img style="position:absolute;top:0.00in;left:0.00in;width:3.4in;height:2.1in" src="{{ asset('img/IMG-20231029-WA0018.jpg') }}" />
        @endif
    @elseif($student->applicant->campus_id == 2)
        @if ($tuition_payment_check)
            <img style="position:absolute;top:0.00in;left:0.00in;width:3.4in;height:2.1in" src="{{ asset('img/mnma-id-bg-semi 1&2 Karume.png') }}" />
        @else
            <img style="position:absolute;top:0.00in;left:0.00in;width:3.4in;height:2.1in" src="{{ asset('img/WhatsApp Image 2023-10-16 at 14.42.44_9a95e42e.png') }}" />
        @endif
    @endif
        @if(file_exists(public_path().'/avatars/'.$student->image))
            <img style="position:absolute;top:0.75in;left:0.11in;width:0.85in;height:1.01in" src="{{ asset('avatars/'.$student->image)}}" />
        @elseif(file_exists(public_path().'/uploads/'.$student->image))
            <img style="position:absolute;top:0.75in;left:0.11in;width:0.85in;height:1.01in" src="{{ asset('uploads/'.$student->image)}}" />
        @endif

    <div style="position:absolute;top:0.75in;left:1.07in;width:1.94in;line-height:0.20in;">
        <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#100d48">NAME:  </span>
        <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#000000"> {{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</span>
        <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#000000"> </span><br/>
        <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#100d48">PROGRAMME:  </span>
        <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#000000">{{ str_replace('.','-',$student->campusProgram->program->code) }}</span>
        <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#000000"> </span><br/>
        <span style="font-style:normal;font-weight:bold;font-size:7pt;color:#100d48">REG No:  REG No:  </span>
    <span style="font-style:normal;font-weight:bold;font-size:7pt;color:#000000">{{ $student->registration_number }}</span>
    <span style="font-style:normal;font-weight:bold;font-size:7pt;color:#000000"> </span><br/>
    <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#100d48">VALID TO:  VALID TO:  </span>
    <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#000000">{{ str_replace('-', '/', App\Utils\DateMaker::toStandardDate($study_academic_year->end_date)) }}</span>
    <span style="font-style:normal;font-weight:bold;font-size:8pt;color:#000000"> </span><br/></div>
    <div style="position:absolute;top:1.75in;left:1.22in;width:0.65in;line-height:0.13in;">
        <span style="font-style:italic;font-weight:bold;font-size:7pt;color:#100d48">SIGNATURE</span>
        <span style="font-style:italic;font-weight:bold;font-size:7pt;color:#100d48"> </span><br/>
    </div>
    <img style="position:absolute;top:1.64in;left:1.88in;width:1.29in;height:0.32in" src="{{ asset('signatures/'.$student->signature) }}" />
</div>

<p style="page-break-after: always;">&nbsp;</p>

<div style="position: relative;width:3.4in; height:2.1in;">
    <div style="width:3.4in; height:2.1in;">
        <h1 style="font-size:14pt; color:#ffffff">&nbsp;</h1>
        <h1 style="text-align:center; font-weight:bold;font-size:14pt;color:#000000">CAUTION</h1>
        <p style="font-size: 12px; margin-left: 10px;">
            This Identity card is a property of
        </p>
        <h1 style="font-weight:bold;font-size:6pt; color:#000000;margin-left: 10px;" >THE MWALIMU NYERERE MEMORIAL ACADEMY</h1>
        <p style="font-size: 12px;  margin-left: 10px;">
            1. Use of this card is subject to the card <br> holder agreement <br>
            2. Card should be returned at the beginning<br> of each semester
        </p>
        <h5 style="font-size: 10px;  margin-left: 10px; margin-top: 20px; margin-bottom: 0;">
            @php

                $footer = "PHONE NO: ".str_replace('255', '0',$student->phone)." ";
                $reg_no = str_replace('/', '-', $registration_no)
            @endphp
            {{ $footer }} <span style="font-size:9px;"> {{ $reg_no }} </span>
        </h5>
    </div>
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
        $qrCodeData = "Time: ".Carbon\Carbon::parse($student->created_at)->format('m/d/Y H:i')."\n"
        ."ID:".$student->registration_number."\n".$student->surname.", ".$student->first_name." ".$student->middle_name."\n"
        ."Course:".$courseCode[0].$courseCode[1]."\n".substr($student->registration_year, 2,2)."-".strtoupper(substr($student->applicant->intake->name, 0, 3))."-".$yearValue."\n".$student->phone;
    @endphp

    <img style="position:absolute;top:0.7in;left:2.4in;width:0.94in;height:0.94in" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($qrCodeData)) !!}" />
</div>

<script type="text/javascript">
   document.getElementById('ss-id-card').addEventListener('click',function(e){
         window.print();
   });
</script>
</body>
</html>

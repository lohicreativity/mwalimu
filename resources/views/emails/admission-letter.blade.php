<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{{ config('constants.SITE_NAME') }}</title>
</head>

<body bgcolor="#ffffff">
     <table  width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f5f5f5; font-family: 'Segoe UI', 'Aerial', Verdana; padding: 50px;">
        <tr>
          <td>
            
               <table class="container" align="center" bgcolor="#ddd" width="640" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #ddd;">
                 <tr bgcolor="#ffffff" style="border-bottom: 1px solid #C0C0C0;">
                   <td class="logo" valign="top" style="padding: 40px 20px 20px 20px; text-align: center;" width="100%">
                      <a href="{{ config('constants.SITE_URL') }}" target="_blank"><img src="{{ config('constants.SITE_URL') }}/dist/img/logo.png" border="0" width"auto" height="50" alt="{{ config('constants.SITE_NAME') }}"></a>
                   </td>
                 </tr>

                 <tr height="200" bgcolor="#ffffff" style="padding: 10px 20px;">
                   <td style="padding: 10px 20px; min-height: 400px; width: 100%; color: #000;">
                     <h3 style="font-size: 20px; text-align: center;">{{ $heading }}</h3>
                     <p style="font-size: 18px;">Dear {!! ucwords(strtolower($name)) !!},</p>
                     <p style="font-size: 18px;">{!! $notification_message !!}</p><br>
              
                     <p style="font-weight: bold; font-size: 18px; margin-top:30px;">The Mwalimu Nyerere Memorial Academy.</p>
                   </td>
                 </tr>

                 <tr style="border-radius: 0px 0px 20px 20px; color: #fff;">
                   <td height="50" bgcolor="#ddd" style="padding: 10px 20px; font-size: 12px; text-align: center;">
                     <p>Visit: <a href="{{ config('constants.SITE_URL') }}" style="color: #fcf8e3; text-align: right; color: #FFF !important;" target="_blank">{{ config('constants.SITE_DOMAIN') }}</a></p>
                     <p style="color: #FFF !important;">For support email: {{ config('constants.ADMISSION_EMAIL') }} Or call: {{ config('constants.PHONE_LINE_ONE') }}</span></p>
                   </td>
                 </tr>
               </table>

          </td>
        </tr>
     </table>
</body>
</html>

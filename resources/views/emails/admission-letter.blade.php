<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{{ Config::get('constants.SITE_NAME') }}</title>
</head>

<body bgcolor="#ffffff">
     <table  width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f5f5f5; font-family: 'Segoe UI', 'Aerial', Verdana; padding: 50px;">
        <tr>
          <td>
            
               <table class="container" align="center" bgcolor="#006E8C" width="640" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #ddd;">
                 <tr bgcolor="#ffffff" style="border-bottom: 1px solid #C0C0C0;">
                   <td class="logo" valign="top" style="padding: 40px 20px 20px 20px; text-align: center;" width="100%">
                      <a href="{{ Config::get('constants.SITE_URL') }}" target="_blank"><img src="{{ Config::get('constants.SITE_URL') }}/assets/img/site-logo.png" border="0" width"auto" height="50" alt="{{ Config::get('constants.SITE_NAME') }}"></a>
                   </td>
                 </tr>

                 <tr height="200" bgcolor="#ffffff" style="padding: 10px 20px;">
                   <td style="padding: 10px 20px; min-height: 400px; width: 100%; color: #000;">
                     <h3 style="font-size: 20px; text-align: center;">{{ $heading }}</h3>
                     <p style="font-size: 18px;">Hi, {!! $name !!}</p>
                     
                     <!-- <p style="margin-top: 18px; font-size: 18px; font-weight: bold;"><a href="{{ URL::to('users/activate/'.$activation_code) }}" target="_blank" style="padding: 10px; color: #FFF; background-color: #006E8C; border: 1px solid #CCC; text-decoration: none; font-family: arial;">Activate Account</a></p>
                     <p style="font-weight: bold; font-size: 18px; margin-top:30px;">{{ Config::get('constants.SITE_NAME') }} team.</p> -->
                   </td>
                 </tr>

                 <tr style="border-radius: 0px 0px 20px 20px; color: #fff;">
                   <td height="50" bgcolor="#006E8C" style="padding: 10px 20px; font-size: 12px; text-align: center;">
                     <p>Visit: <a href="{{ Config::get('constants.SITE_URL') }}" style="color: #fcf8e3; text-align: right; color: #FFF !important;" target="_blank">{{ Config::get('constants.SITE_DOMAIN') }}</a></p>
                     <p style="color: #FFF !important;">For support email: {{ Config::get('constants.SUPPORT_EMAIL') }} Or call: {{ Config::get('constants.PHONE_LINE_ONE') }}</span></p>
                     <p>Follow us:</p>
                     <p>
                       <a href="{{ Config::get('constants.FACEBOOK_PAGE_URL') }}" target="_blank" style="margin-right: 20px;"><img style="border-radius: 50%;" src="{{ Config::get('constants.SITE_URL') }}/assets/img/fade-icon-fb.png" width="30" height="30" alt="Follow us on Facebook"></a>
                       <a href="{{ Config::get('constants.TWITTER_PAGE_URL') }}" target="_blank" style="margin-right: 20px;"><img style="border-radius: 50%;" src="{{ Config::get('constants.SITE_URL') }}/assets/img/fade-icon-tt.png" width="30" height="30" alt="Follow us on Twitter"></a>
                       <a href="{{ Config::get('constants.INSTAGRAM_PAGE_URL') }}" target="_blank" style="margin-right: 20px;"><img style="border-radius: 50%;" src="{{ Config::get('constants.SITE_URL') }}/assets/img/fade-icon-ig.png" width="30" height="30" alt="Follow us on Instagram"></a>
                       <a href="{{ Config::get('constants.LINKEDIN_PAGE_URL') }}" target="_blank"><img style="border-radius: 50%;" src="{{ Config::get('constants.SITE_URL') }}/assets/img/fade-icon-in.png" width="30" height="30" alt="Follow us on Linkedin"></a>
                     </p>
                   </td>
                 </tr>
               </table>

          </td>
        </tr>
     </table>
</body>
</html>

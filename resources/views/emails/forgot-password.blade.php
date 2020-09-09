<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
    <p>Hi {!! $user->first_name !!} {!! $user->last_name !!},</p>

    <p> You initiated a request to help with your Account Password. Click the link below to set a new password.<p>

    <button style="background-color:#004DC5;color: #FBFBFB;border: 1px solid #004DC5;padding: 10px;"><a href="{!! $forgotPasswordUrl !!}" style="text-decoration:none;color:#ffffff;">RESET  PASSWORD</a></button>

    <p>For security reasons this link will not work after 24 Hrs.</p>

    <p>Please ignore this email if it wasn&#39;t you who requested help with your password - your current password will remain unchanged.</p>


<p>Best regards,<br/>
Passport Team</p>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contact Form Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4f46e5;">Thanks for Reaching Out!</h2>

        <p>Dear {{ $name }},</p>

        <p>I appreciate you taking the time to contact me. I've received your message and will get back to you personally as soon as I can.</p>

        <p>For reference, here's what you wrote:</p>

        <div style="background-color: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #4f46e5;">
            {{ $note }}
        </div>

        <p>In the meantime, feel free to:</p>
        <ul style="list-style: none; padding-left: 0;">
            <li style="margin-bottom: 8px;">• Connect with me on <a href="{{ config('social.linkedin_url') }}" style="color: #4f46e5; text-decoration: none;">LinkedIn</a></li>
            <li style="margin-bottom: 8px;">• Check out my projects on <a href="{{ config('social.github_url') }}" style="color: #4f46e5; text-decoration: none;">GitHub</a></li>
        </ul>

        <p style="margin-top: 20px;">Best regards,<br>
        <strong>João Estima</strong><br>
        <span style="color: #666; font-size: 0.9em;">Software Developer</span></p>
    </div>
</body>
</html>

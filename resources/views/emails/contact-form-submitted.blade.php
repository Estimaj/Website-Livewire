<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>New Contact Form Submission</h2>

        <div style="background-color: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px;">
            <p><strong>Name:</strong> {{ $data['firstName'] }} {{ $data['lastName'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
            <p><strong>Phone:</strong> {{ $data['phone'] }}</p>
            <p><strong>Message:</strong><br>
            {{ $data['message'] }}</p>
        </div>

        <p style="color: #666; font-size: 0.9em;">
            This message was sent from your website's contact form.
        </p>
    </div>
</body>
</html>

<?php
// Retrieve form data
$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

// Set up email parameters
$to = 'christopher.hull@eng.ox.ac.uk'; // Replace with your own email address
$headers = "From: $name <$email>" . "\r\n";
$headers .= "Reply-To: $email" . "\r\n";
$message = wordwrap($message, 70);

// Send the email
mail($to, $subject, $message, $headers);

// Redirect the user to a thank you page
// header('Location: thank_you.html');
?>

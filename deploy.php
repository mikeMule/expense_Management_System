<?php
// Secure GitHub Webhook Deployment Script

// 1. Define your secret key here (It must match the secret in your GitHub Webhook settings)
$secret = "your_strong_secret_key_here"; 

// Verify GitHub signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die('Unauthorized access.');
}

// 2. Change this path to match your exact cPanel path where the project is located.
// Example: '/home/gebetaya/public_html' or '/home/gebetaya/public_html/expense-manager'
$target_dir = '/home/gebetaya/public_html';

// 3. Run git pull (Ensure your branch name is correct, usually 'main' or 'master')
$output = shell_exec("cd {$target_dir} && git pull origin main 2>&1");

// Output the result
echo "<pre>Deployment Output:\n\n$output</pre>";

<?php
session_start();

// Create email logs directory if not exists
$email_logs_dir = __DIR__ . '/email_logs';
if (!file_exists($email_logs_dir)) {
    mkdir($email_logs_dir, 0777, true);
}

// Get list of all emails
$email_files = glob($email_logs_dir . '/email_*.html');
rsort($email_files); // Sort by newest first

// Get the email content if an ID is provided
$email_content = '';
$email_id = isset($_GET['id']) ? $_GET['id'] : '';

if (!empty($email_id) && file_exists($email_logs_dir . '/' . $email_id)) {
    $email_content = file_get_contents($email_logs_dir . '/' . $email_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Viewer - VetCare Development</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            display: flex;
        }
        .email-list {
            width: 30%;
            padding-right: 20px;
            overflow-y: auto;
            max-height: calc(100vh - 100px);
        }
        .email-content {
            width: 70%;
            border: 1px solid #ddd;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .email-item {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .email-item:hover {
            background-color: #f0f0f0;
        }
        .email-item.active {
            background-color: #e0e0e0;
            border-color: #999;
        }
        .email-meta {
            font-size: 0.8em;
            color: #666;
        }
        .email-view {
            white-space: pre-wrap;
            border-top: 1px solid #ddd;
            margin-top: 20px;
            padding-top: 20px;
        }
        .iframe-container {
            margin-top: 20px;
            border: 1px solid #ddd;
            height: 600px;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Development Email Viewer</h1>
        <div>
            <a href="index.php">Back to Home</a> | 
            <a href="user_verification.php">Verification Page</a>
        </div>
    </div>
    
    <p><strong>Note:</strong> This is a development tool. Emails are not actually sent but stored locally for viewing.</p>
    
    <div class="container">
        <div class="email-list">
            <h2>Stored Emails (<?php echo count($email_files); ?>)</h2>
            
            <?php if (empty($email_files)): ?>
                <p>No emails found.</p>
            <?php else: ?>
                <?php foreach ($email_files as $file): ?>
                    <?php 
                        $filename = basename($file);
                        $timestamp = filemtime($file);
                        $date = date('Y-m-d H:i:s', $timestamp);
                        
                        // Extract recipient from file content
                        $content = file_get_contents($file);
                        preg_match('/^To: (.+?)$/m', $content, $matches);
                        $recipient = isset($matches[1]) ? $matches[1] : 'Unknown';
                        
                        // Extract subject
                        preg_match('/^Subject: (.+?)$/m', $content, $matches);
                        $subject = isset($matches[1]) ? $matches[1] : 'No Subject';
                        
                        $active = ($email_id === $filename) ? 'active' : '';
                    ?>
                    <div class="email-item <?php echo $active; ?>" onclick="location.href='?id=<?php echo $filename; ?>'">
                        <div><strong><?php echo htmlspecialchars($subject); ?></strong></div>
                        <div>To: <?php echo htmlspecialchars($recipient); ?></div>
                        <div class="email-meta">Date: <?php echo $date; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="email-content">
            <h2>Email Preview</h2>
            
            <?php if (empty($email_content)): ?>
                <p>Select an email from the list to view its contents.</p>
            <?php else: ?>
                <?php
                    // Extract email headers and body
                    $parts = explode("\n\n", $email_content, 2);
                    $headers = isset($parts[0]) ? $parts[0] : '';
                    $body = isset($parts[1]) ? $parts[1] : '';
                ?>
                
                <div class="email-headers">
                    <pre><?php echo htmlspecialchars($headers); ?></pre>
                </div>
                
                <div class="iframe-container">
                    <iframe id="email-body-frame"></iframe>
                </div>
                
                <script>
                    // Write the HTML content to the iframe
                    document.addEventListener('DOMContentLoaded', function() {
                        const frame = document.getElementById('email-body-frame');
                        const doc = frame.contentDocument || frame.contentWindow.document;
                        doc.open();
                        doc.write(<?php echo json_encode($body); ?>);
                        doc.close();
                    });
                </script>
                
                <h3>Raw Content</h3>
                <div class="email-view">
                    <pre><?php echo htmlspecialchars($email_content); ?></pre>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
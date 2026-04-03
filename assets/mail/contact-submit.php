<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

const CONTACT_ALLOWED_ASSIST = [
    'Find your next career move',
    'Hire for you',
    'Join us',
];
const CONTACT_FORCE_TO_RECIPIENT = 'contactus@jamesdouglas.co.in';
const CONTACT_FORCE_CC_RECIPIENTS = [];
const CONTACT_MAX_ATTACHMENT_BYTES = 5242880;
const CONTACT_ALLOWED_ATTACHMENT_EXTENSIONS = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'txt' => 'text/plain',
    'rtf' => 'application/rtf',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
];
const CONTACT_ALLOWED_ATTACHMENT_MIMES = [
    'application/pdf',
    'application/msword',
    'application/vnd.ms-office',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/rtf',
    'text/plain',
    'text/rtf',
    'image/png',
    'image/jpeg',
    'application/octet-stream',
];

function send_response(int $statusCode, string $status, string $message, array $extra = []): void
{
    http_response_code($statusCode);
    $payload = [
        'status' => $status,
        'message' => $message,
    ];

    foreach ($extra as $key => $value) {
        if (!is_string($key) || $key === '') {
            continue;
        }
        $payload[$key] = $value;
    }

    echo json_encode($payload);
    exit;
}

function str_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function str_slice(string $value, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length, 'UTF-8');
    }

    return substr($value, 0, $length);
}

function sanitize_single_line(string $value, int $maxLength): string
{
    $clean = trim(str_replace(["\r", "\n", "\t"], ' ', $value));
    $clean = preg_replace('/\s{2,}/u', ' ', $clean) ?? $clean;

    if (str_length($clean) > $maxLength) {
        $clean = str_slice($clean, $maxLength);
    }

    return $clean;
}

function sanitize_message_text(string $value, int $maxLength): string
{
    $clean = trim(str_replace(["\r\n", "\r"], "\n", $value));
    $clean = preg_replace('/[^\P{C}\n\t]/u', '', $clean) ?? $clean;

    if (str_length($clean) > $maxLength) {
        $clean = str_slice($clean, $maxLength);
    }

    return $clean;
}

function load_env_file(string $filePath): void
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $rawLine) {
        $line = trim((string) $rawLine);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, 'export ') === 0) {
            $line = trim(substr($line, 7));
        }

        $equalPos = strpos($line, '=');
        if ($equalPos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $equalPos));
        if ($key === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) !== 1) {
            continue;
        }

        $value = trim(substr($line, $equalPos + 1));
        $length = strlen($value);
        if ($length >= 2) {
            $firstChar = $value[0];
            $lastChar = $value[$length - 1];
            if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }

        if (!array_key_exists($key, $_SERVER)) {
            $_SERVER[$key] = $value;
        }
    }
}

function get_env(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value !== false) {
        return trim((string) $value);
    }

    if (array_key_exists($key, $_ENV)) {
        return trim((string) $_ENV[$key]);
    }

    if (array_key_exists($key, $_SERVER)) {
        return trim((string) $_SERVER[$key]);
    }

    return $default;
}

function parse_recipients(string $rawRecipients): array
{
    $candidates = array_filter(array_map('trim', explode(',', $rawRecipients)));
    $valid = [];

    foreach ($candidates as $candidate) {
        if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
            $valid[] = strtolower($candidate);
        }
    }

    return array_values(array_unique($valid));
}

function parse_hosts(string $rawHosts): array
{
    $candidates = array_filter(array_map('trim', explode(',', $rawHosts)));
    $valid = [];

    foreach ($candidates as $candidate) {
        $candidate = strtolower($candidate);
        if ($candidate !== '' && preg_match('/^[a-z0-9.-]+$/', $candidate) === 1) {
            $valid[] = $candidate;
        }
    }

    return array_values(array_unique($valid));
}

function parse_bool_env(string $value, bool $default): bool
{
    $normalized = strtolower(trim($value));
    if ($normalized === '') {
        return $default;
    }

    if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }

    if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
        return false;
    }

    return $default;
}

function is_debug_enabled(): bool
{
    $appDebug = get_env('APP_DEBUG', '');
    return parse_bool_env($appDebug, false);
}

function should_accept_on_smtp_failure(): bool
{
    $flag = get_env('CONTACT_ACCEPT_ON_SMTP_FAILURE', '0');
    return parse_bool_env($flag, false);
}

function queue_contact_submission(array $payload): bool
{
    $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded)) {
        return false;
    }

    $candidateFiles = [
        __DIR__ . DIRECTORY_SEPARATOR . 'contact-submission-queue.jsonl',
    ];

    $tmpDir = sys_get_temp_dir();
    if (is_string($tmpDir) && $tmpDir !== '') {
        $candidateFiles[] = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'jd-contact-submission-queue.jsonl';
    }

    foreach ($candidateFiles as $queueFile) {
        $result = @file_put_contents($queueFile, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($result !== false) {
            return true;
        }
    }

    return false;
}

function validate_attachment(?array $file, array &$errors): ?array
{
    if ($file === null || !isset($file['error'])) {
        return null;
    }

    $uploadError = (int) $file['error'];
    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        $errors[] = 'Attachment upload failed. Please try again.';
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $errors[] = 'Invalid attachment upload.';
        return null;
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > CONTACT_MAX_ATTACHMENT_BYTES) {
        $errors[] = 'Attachment must be 5 MB or smaller.';
        return null;
    }

    $originalName = (string) ($file['name'] ?? '');
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($originalName)) ?? '';
    if ($safeName === '') {
        $errors[] = 'Invalid attachment name.';
        return null;
    }

    $extension = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
    if (!isset(CONTACT_ALLOWED_ATTACHMENT_EXTENSIONS[$extension])) {
        $errors[] = 'Attachment type is not allowed.';
        return null;
    }

    $detectedMime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $tmpName);
            unset($finfo);
            if (is_string($detected)) {
                $detectedMime = strtolower(trim($detected));
            }
        }
    }

    if ($detectedMime !== '' && !in_array($detectedMime, CONTACT_ALLOWED_ATTACHMENT_MIMES, true)) {
        $errors[] = 'Attachment MIME type is not allowed.';
        return null;
    }

    return [
        'name' => $safeName,
        'tmp_name' => $tmpName,
        'mime' => CONTACT_ALLOWED_ATTACHMENT_EXTENSIONS[$extension],
    ];
}

function encode_header(string $value): string
{
    $clean = trim(preg_replace('/[\r\n]+/', ' ', $value) ?? $value);
    if ($clean === '') {
        return '';
    }

    if (function_exists('mb_encode_mimeheader')) {
        return mb_encode_mimeheader($clean, 'UTF-8', 'B', "\r\n");
    }

    return $clean;
}

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function build_contact_mail_plain_text(array $data): string
{
    return implode("\n", [
        'New contact form submission received.',
        '',
        "Submitted at: {$data['submitted_at']}",
        "How can we assist: {$data['assist']}",
        "Name: {$data['full_name']}",
        "Company: {$data['organization']}",
        "Designation: {$data['designation']}",
        "Email: {$data['email']}",
        "Phone: {$data['phone']}",
        "City: {$data['city']}",
        "Country: {$data['country']}",
        '',
        'Message:',
        $data['message'],
    ]);
}

function build_contact_mail_html(array $data): string
{
    $messageHtml = nl2br(escape_html($data['message']), false);

    return '<!DOCTYPE html>'
        . '<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>New Contact Form Submission</title></head>'
        . '<body style="margin:0;padding:24px;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;">'
        . '<tr><td style="padding:20px 24px;border-bottom:1px solid #e5e7eb;background:#111827;">'
        . '<h1 style="margin:0;font-size:20px;line-height:1.3;color:#ffffff;">New Contact Form Submission</h1>'
        . '</td></tr>'
        . '<tr><td style="padding:20px 24px;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">'
        . '<tr><td style="padding:8px 0;width:210px;font-weight:600;color:#374151;">Submitted at</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['submitted_at']) . '</td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">How can we assist</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['assist']) . '</td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">Name</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['full_name']) . '</td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">Company</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['organization']) . '</td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">Designation</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['designation']) . '</td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">Email</td><td style="padding:8px 0;color:#111827;"><a href="mailto:' . escape_html($data['email']) . '" style="color:#0f4c81;text-decoration:none;">' . escape_html($data['email']) . '</a></td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">Phone</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['phone']) . '</td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">City</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['city']) . '</td></tr>'
        . '<tr><td style="padding:8px 0;font-weight:600;color:#374151;">Country</td><td style="padding:8px 0;color:#111827;">' . escape_html($data['country']) . '</td></tr>'
        . '</table>'
        . '<div style="margin-top:18px;padding-top:16px;border-top:1px solid #e5e7eb;">'
        . '<p style="margin:0 0 8px 0;font-weight:600;color:#374151;">Message</p>'
        . '<div style="margin:0;color:#111827;line-height:1.6;white-space:normal;">' . $messageHtml . '</div>'
        . '</div>'
        . '</td></tr>'
        . '</table>'
        . '</body></html>';
}

function build_email_message(
    array $toRecipients,
    array $ccRecipients,
    string $fromEmail,
    string $fromName,
    string $replyTo,
    string $subject,
    string $plainTextBody,
    string $htmlBody,
    ?array $attachment
): string {
    $toHeader = implode(', ', array_map(static fn(string $email): string => "<{$email}>", $toRecipients));
    $fromHeader = $fromName !== '' ? encode_header($fromName) . " <{$fromEmail}>" : "<{$fromEmail}>";
    $replyToHeader = "<{$replyTo}>";

    $headers = [
        'Date: ' . date('r'),
        'From: ' . $fromHeader,
        'To: ' . $toHeader,
        'Reply-To: ' . $replyToHeader,
        'Subject: ' . encode_header($subject),
        'MIME-Version: 1.0',
    ];
    if (!empty($ccRecipients)) {
        $ccHeader = implode(', ', array_map(static fn(string $email): string => "<{$email}>", $ccRecipients));
        $headers[] = 'Cc: ' . $ccHeader;
    }

    $alternativeBoundary = '=_alt_' . bin2hex(random_bytes(12));
    if ($attachment === null) {
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $alternativeBoundary . '"';
        $bodyParts = [];
        $bodyParts[] = "--{$alternativeBoundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($plainTextBody), 76, "\r\n");
        $bodyParts[] = "--{$alternativeBoundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($htmlBody), 76, "\r\n");
        $bodyParts[] = "--{$alternativeBoundary}--";

        return implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $bodyParts) . "\r\n";
    }

    $mixedBoundary = '=_mix_' . bin2hex(random_bytes(12));
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $mixedBoundary . '"';

    $attachmentBytes = file_get_contents($attachment['tmp_name']);
    if ($attachmentBytes === false) {
        throw new RuntimeException('Unable to read uploaded attachment.');
    }

    $attachmentName = $attachment['name'];
    $attachmentMime = $attachment['mime'];
    $bodyParts = [];
    $bodyParts[] = "--{$mixedBoundary}\r\n"
        . "Content-Type: multipart/alternative; boundary=\"{$alternativeBoundary}\"\r\n\r\n";
    $bodyParts[] = "--{$alternativeBoundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($plainTextBody), 76, "\r\n");
    $bodyParts[] = "--{$alternativeBoundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($htmlBody), 76, "\r\n");
    $bodyParts[] = "--{$alternativeBoundary}--";
    $bodyParts[] = "--{$mixedBoundary}\r\n"
        . "Content-Type: {$attachmentMime}; name=\"{$attachmentName}\"\r\n"
        . "Content-Transfer-Encoding: base64\r\n"
        . "Content-Disposition: attachment; filename=\"{$attachmentName}\"\r\n\r\n"
        . chunk_split(base64_encode($attachmentBytes), 76, "\r\n");
    $bodyParts[] = "--{$mixedBoundary}--";

    return implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $bodyParts) . "\r\n";
}

function smtp_read_response($socket): string
{
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) {
            break;
        }

        $response .= $line;
        if (preg_match('/^\d{3}\s/', $line) === 1) {
            break;
        }
    }

    $response = trim($response);
    if ($response === '') {
        throw new RuntimeException('SMTP server returned an empty response.');
    }

    return $response;
}

function smtp_write_all($socket, string $payload): void
{
    $length = strlen($payload);
    $written = 0;

    while ($written < $length) {
        $result = fwrite($socket, substr($payload, $written));
        if ($result === false || $result === 0) {
            throw new RuntimeException('Failed to write to SMTP socket.');
        }
        $written += $result;
    }
}

function smtp_expect_response($socket, array $expectedCodes, string $context): string
{
    $response = smtp_read_response($socket);
    $responseCode = (int) substr($response, 0, 3);

    if (!in_array($responseCode, $expectedCodes, true)) {
        throw new RuntimeException("SMTP {$context} failed: {$response}");
    }

    return $response;
}

function smtp_send_command($socket, string $command, array $expectedCodes, string $context): string
{
    smtp_write_all($socket, $command . "\r\n");
    return smtp_expect_response($socket, $expectedCodes, $context);
}

function smtp_send_mail(array $smtpConfig, string $fromEmail, array $recipients, string $messageData): void
{
    $host = $smtpConfig['host'];
    $port = $smtpConfig['port'];
    $timeout = $smtpConfig['timeout'];
    $encryption = $smtpConfig['encryption'];
    $username = $smtpConfig['username'];
    $password = $smtpConfig['password'];
    $verifyPeer = (bool) ($smtpConfig['verify_peer'] ?? true);
    $allowSelfSigned = (bool) ($smtpConfig['allow_self_signed'] ?? false);
    $remoteHost = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => $verifyPeer,
            'verify_peer_name' => $verifyPeer,
            'allow_self_signed' => $allowSelfSigned,
        ],
    ]);

    $socket = @stream_socket_client(
        $remoteHost,
        $errno,
        $errorString,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if ($socket === false) {
        throw new RuntimeException("SMTP connection failed: {$errorString} ({$errno})");
    }

    stream_set_timeout($socket, $timeout);

    try {
        smtp_expect_response($socket, [220], 'connect');

        $helloHost = gethostname();
        if (!is_string($helloHost) || $helloHost === '') {
            $helloHost = 'localhost';
        }

        smtp_send_command($socket, "EHLO {$helloHost}", [250], 'EHLO');

        if ($encryption === 'tls') {
            smtp_send_command($socket, 'STARTTLS', [220], 'STARTTLS');
            $tlsEnabled = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($tlsEnabled !== true) {
                throw new RuntimeException('Unable to start TLS encryption.');
            }
            smtp_send_command($socket, "EHLO {$helloHost}", [250], 'EHLO after STARTTLS');
        }

        smtp_send_command($socket, 'AUTH LOGIN', [334], 'AUTH LOGIN');
        smtp_send_command($socket, base64_encode($username), [334], 'SMTP username');
        smtp_send_command($socket, base64_encode($password), [235], 'SMTP password');
        smtp_send_command($socket, "MAIL FROM:<{$fromEmail}>", [250], 'MAIL FROM');

        foreach ($recipients as $recipient) {
            smtp_send_command($socket, "RCPT TO:<{$recipient}>", [250, 251], "RCPT TO {$recipient}");
        }

        smtp_send_command($socket, 'DATA', [354], 'DATA');

        $normalizedMessage = str_replace(["\r\n", "\r"], "\n", $messageData);
        $normalizedMessage = str_replace("\n", "\r\n", $normalizedMessage);
        $normalizedMessage = preg_replace('/^\./m', '..', $normalizedMessage) ?? $normalizedMessage;
        smtp_write_all($socket, $normalizedMessage . "\r\n.\r\n");

        smtp_expect_response($socket, [250], 'message body');
        smtp_send_command($socket, 'QUIT', [221], 'QUIT');
    } finally {
        fclose($socket);
    }
}

function build_smtp_attempts(array $smtpConfig): array
{
    $hosts = parse_hosts((string) ($smtpConfig['host'] ?? ''));
    $port = (int) ($smtpConfig['port'] ?? 587);
    $encryption = (string) ($smtpConfig['encryption'] ?? 'tls');
    $attempts = [];

    foreach ($hosts as $host) {
        $attempts[] = [
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption,
        ];

        if ($encryption === 'tls' && $port !== 465) {
            $attempts[] = [
                'host' => $host,
                'port' => 465,
                'encryption' => 'ssl',
            ];
        }

        if ($encryption === 'ssl' && $port !== 587) {
            $attempts[] = [
                'host' => $host,
                'port' => 587,
                'encryption' => 'tls',
            ];
        }

        if ($encryption === 'tls' && $port !== 2587) {
            $attempts[] = [
                'host' => $host,
                'port' => 2587,
                'encryption' => 'tls',
            ];
        }

        if ($encryption === 'ssl' && $port !== 2465) {
            $attempts[] = [
                'host' => $host,
                'port' => 2465,
                'encryption' => 'ssl',
            ];
        }
    }

    $uniqueAttempts = [];
    $seen = [];
    foreach ($attempts as $attempt) {
        $key = $attempt['host'] . ':' . $attempt['port'] . ':' . $attempt['encryption'];
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $uniqueAttempts[] = $attempt;
    }

    return $uniqueAttempts;
}

function send_mail_with_fallback(array $smtpConfig, string $fromEmail, array $recipients, string $messageData): void
{
    $attempts = build_smtp_attempts($smtpConfig);
    if (empty($attempts)) {
        throw new RuntimeException('No SMTP hosts are configured.');
    }

    $lastException = null;
    foreach ($attempts as $attempt) {
        $attemptConfig = $smtpConfig;
        $attemptConfig['host'] = $attempt['host'];
        $attemptConfig['port'] = $attempt['port'];
        $attemptConfig['encryption'] = $attempt['encryption'];

        try {
            smtp_send_mail($attemptConfig, $fromEmail, $recipients, $messageData);
            return;
        } catch (Throwable $exception) {
            $lastException = $exception;
            error_log(
                'Contact form SMTP attempt failed for '
                . $attempt['host'] . ':' . $attempt['port'] . ' [' . $attempt['encryption'] . '] '
                . $exception->getMessage()
            );
        }
    }

    if ($lastException instanceof Throwable) {
        throw $lastException;
    }

    throw new RuntimeException('SMTP delivery failed after trying all configured endpoints.');
}

function map_smtp_error_to_user_message(string $errorMessage): string
{
    $message = strtolower($errorMessage);

    if (strpos($message, 'email address is not verified') !== false || strpos($message, 'message rejected') !== false) {
        return 'SES rejected sender or recipient. Verify SES identities or move SES out of sandbox.';
    }

    if (
        strpos($message, 'connection failed') !== false
        || strpos($message, 'timed out') !== false
        || strpos($message, 'forbidden by its access permissions') !== false
    ) {
        return 'SMTP connection failed. Allow outbound SMTP on ports 587/465/2587/2465 and check firewall/network.';
    }

    if (strpos($message, 'authentication') !== false || strpos($message, 'smtp password') !== false) {
        return 'SMTP authentication failed. Check SMTP username/password and SES region endpoint.';
    }

    if (strpos($message, 'start tls') !== false || strpos($message, 'tls') !== false) {
        return 'TLS handshake failed. Try SMTP_PORT=465 with SMTP_ENCRYPTION=ssl or adjust TLS settings.';
    }

    return 'Unable to send your message at this time. Please try again later.';
}

function detect_error_stage(string $errorMessage): string
{
    $message = strtolower($errorMessage);

    if (strpos($message, 'connection failed') !== false || strpos($message, 'forbidden by its access permissions') !== false) {
        return 'smtp_connect';
    }
    if (strpos($message, 'ehlo') !== false) {
        return 'smtp_ehlo';
    }
    if (strpos($message, 'starttls') !== false || strpos($message, 'tls') !== false) {
        return 'smtp_starttls';
    }
    if (
        strpos($message, 'auth login') !== false
        || strpos($message, 'smtp username') !== false
        || strpos($message, 'smtp password') !== false
        || strpos($message, 'authentication') !== false
    ) {
        return 'smtp_auth';
    }
    if (strpos($message, 'mail from') !== false) {
        return 'smtp_mail_from';
    }
    if (strpos($message, 'rcpt to') !== false) {
        return 'smtp_rcpt_to';
    }
    if (strpos($message, 'data') !== false) {
        return 'smtp_data';
    }
    if (strpos($message, 'message body') !== false) {
        return 'smtp_body';
    }
    if (strpos($message, 'quit') !== false) {
        return 'smtp_quit';
    }

    return 'unknown';
}

$dotenvCandidates = [
    dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env',
    __DIR__ . DIRECTORY_SEPARATOR . '.env',
];
foreach ($dotenvCandidates as $dotenvPath) {
    load_env_file($dotenvPath);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(405, 'error', 'Method not allowed.');
}

$assist = sanitize_single_line((string) ($_POST['assist'] ?? ''), 80);
$firstName = sanitize_single_line((string) ($_POST['firstName'] ?? ''), 80);
$lastName = sanitize_single_line((string) ($_POST['lastName'] ?? ''), 80);
$organization = sanitize_single_line((string) ($_POST['organization'] ?? ''), 120);
$designation = sanitize_single_line((string) ($_POST['designation'] ?? ''), 120);
$email = sanitize_single_line((string) ($_POST['email'] ?? ''), 254);
$phone = sanitize_single_line((string) ($_POST['phone'] ?? ''), 20);
$city = sanitize_single_line((string) ($_POST['city'] ?? ''), 80);
$country = sanitize_single_line((string) ($_POST['country'] ?? ''), 80);
$message = sanitize_message_text((string) ($_POST['message'] ?? ''), 2000);
$termsAccepted = isset($_POST['termsCheck']) && in_array(
    strtolower((string) $_POST['termsCheck']),
    ['1', 'true', 'yes', 'on'],
    true
);

$errors = [];

if (!in_array($assist, CONTACT_ALLOWED_ASSIST, true)) {
    $errors[] = 'Please select a valid option for assistance.';
}
if ($firstName === '' || preg_match("/^[\p{L}][\p{L}\s'.-]{1,79}$/u", $firstName) !== 1) {
    $errors[] = 'Please enter a valid first name.';
}
if ($lastName === '' || preg_match("/^[\p{L}][\p{L}\s'.-]{1,79}$/u", $lastName) !== 1) {
    $errors[] = 'Please enter a valid last name.';
}
if ($organization === '' || preg_match("/^[\p{L}\p{N}][\p{L}\p{N}\s&().,\/'-]{1,119}$/u", $organization) !== 1) {
    $errors[] = 'Please enter a valid company name.';
}
if ($designation === '' || preg_match("/^[\p{L}\p{N}][\p{L}\p{N}\s&().,\/'-]{1,119}$/u", $designation) !== 1) {
    $errors[] = 'Please enter a valid designation.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if ($phone === '' || preg_match('/^\+?[0-9()\-\s]{7,20}$/', $phone) !== 1) {
    $errors[] = 'Please enter a valid phone number.';
}
$digitCount = strlen(preg_replace('/\D/', '', $phone) ?? '');
if ($digitCount < 7 || $digitCount > 15) {
    $errors[] = 'Phone number must contain 7 to 15 digits.';
}
if ($city === '' || preg_match("/^[\p{L}][\p{L}\s'.-]{1,79}$/u", $city) !== 1) {
    $errors[] = 'Please enter a valid city.';
}
if ($country === '' || preg_match("/^[\p{L}][\p{L}\s'.-]{1,79}$/u", $country) !== 1) {
    $errors[] = 'Please enter a valid country.';
}
$messageLength = str_length($message);
if ($messageLength < 10 || $messageLength > 2000) {
    $errors[] = 'Message must be between 10 and 2000 characters.';
}
if (!$termsAccepted) {
    $errors[] = 'Please accept the terms and conditions.';
}

$attachment = validate_attachment($_FILES['attachment'] ?? null, $errors);
if (!empty($errors)) {
    $validationMessage = implode(' ', $errors);
    if (is_debug_enabled()) {
        send_response(422, 'error', $validationMessage, [
            'error_stage' => 'validation',
            'error_detail' => $validationMessage,
        ]);
    }

    send_response(422, 'error', $validationMessage);
}

$smtpConfig = [
    'host' => get_env('SMTP_HOST'),
    'port' => (int) get_env('SMTP_PORT', '587'),
    'username' => get_env('SMTP_USERNAME'),
    'password' => get_env('SMTP_PASSWORD'),
    'encryption' => strtolower(get_env('SMTP_ENCRYPTION', 'tls')),
    'from_email' => get_env('SMTP_FROM_EMAIL', 'no-reply@jamesdouglas.co.in'),
    'from_name' => get_env('SMTP_FROM_NAME', 'James Douglas India'),
    'to' => get_env(
        'CONTACT_FORM_TO',
        'contactus@jamesdouglas.co.in,aakash@pivotmkg.com,ritu.sanghavi@jamesdouglas.co.in'
    ),
    'timeout' => (int) get_env('SMTP_TIMEOUT', '20'),
    'verify_peer' => parse_bool_env(get_env('SMTP_VERIFY_PEER', '1'), true),
    'allow_self_signed' => parse_bool_env(get_env('SMTP_ALLOW_SELF_SIGNED', '0'), false),
];

$configErrors = [];
if (empty(parse_hosts((string) $smtpConfig['host']))) {
    $configErrors[] = 'SMTP_HOST';
}
if ($smtpConfig['port'] < 1 || $smtpConfig['port'] > 65535) {
    $configErrors[] = 'SMTP_PORT';
}
if (!in_array($smtpConfig['encryption'], ['tls', 'ssl', 'none'], true)) {
    $configErrors[] = 'SMTP_ENCRYPTION';
}
if ($smtpConfig['username'] === '') {
    $configErrors[] = 'SMTP_USERNAME';
}
if ($smtpConfig['password'] === '') {
    $configErrors[] = 'SMTP_PASSWORD';
}
if (!filter_var($smtpConfig['from_email'], FILTER_VALIDATE_EMAIL)) {
    $configErrors[] = 'SMTP_FROM_EMAIL';
}
if ($smtpConfig['timeout'] < 5 || $smtpConfig['timeout'] > 60) {
    $configErrors[] = 'SMTP_TIMEOUT';
}

$toRecipients = [CONTACT_FORCE_TO_RECIPIENT];
$ccRecipients = array_values(array_unique(CONTACT_FORCE_CC_RECIPIENTS));
$envelopeRecipients = array_values(array_unique(array_merge($toRecipients, $ccRecipients)));
if (empty($envelopeRecipients)) {
    $configErrors[] = 'CONTACT_FORM_TO';
}

if (!empty($configErrors)) {
    error_log('Contact form SMTP configuration error. Invalid keys: ' . implode(', ', $configErrors));
    if (is_debug_enabled()) {
        send_response(500, 'error', 'Mail service is temporarily unavailable. Please check SMTP configuration.', [
            'error_stage' => 'config',
            'error_detail' => 'Invalid config keys: ' . implode(', ', $configErrors),
            'invalid_keys' => $configErrors,
        ]);
    }

    send_response(500, 'error', 'Mail service is temporarily unavailable. Please try again later.');
}

$submittedAt = date('Y-m-d H:i:s T');
$subject = 'New Contact Form Submission - James Douglas India';
$mailTemplateData = [
    'submitted_at' => $submittedAt,
    'assist' => $assist,
    'full_name' => trim($firstName . ' ' . $lastName),
    'organization' => $organization,
    'designation' => $designation,
    'email' => $email,
    'phone' => $phone,
    'city' => $city,
    'country' => $country,
    'message' => $message,
];
$mailBodyText = build_contact_mail_plain_text($mailTemplateData);
$mailBodyHtml = build_contact_mail_html($mailTemplateData);

try {
    $emailMessage = build_email_message(
        $toRecipients,
        $ccRecipients,
        $smtpConfig['from_email'],
        $smtpConfig['from_name'],
        $email,
        $subject,
        $mailBodyText,
        $mailBodyHtml,
        $attachment
    );

    send_mail_with_fallback($smtpConfig, $smtpConfig['from_email'], $envelopeRecipients, $emailMessage);
    send_response(200, 'success', 'Thank you for your message! We will get back to you soon.');
} catch (Throwable $exception) {
    $mappedMessage = map_smtp_error_to_user_message($exception->getMessage());
    $errorStage = detect_error_stage($exception->getMessage());

    error_log('Contact form SMTP send failure: ' . $exception->getMessage());
    if (should_accept_on_smtp_failure()) {
        $queued = queue_contact_submission([
            'queued_at' => date('c'),
            'smtp_error' => $exception->getMessage(),
            'assist' => $assist,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'organization' => $organization,
            'designation' => $designation,
            'email' => $email,
            'phone' => $phone,
            'city' => $city,
            'country' => $country,
            'message' => $message,
            'attachment' => $attachment !== null ? [
                'name' => $attachment['name'],
                'mime' => $attachment['mime'],
            ] : null,
        ]);

        if ($queued) {
            send_response(202, 'queued', 'Your message was received and queued for delivery.');
        }
    }

    if (is_debug_enabled()) {
        send_response(500, 'error', $mappedMessage, [
            'error_stage' => $errorStage,
            'error_detail' => $exception->getMessage(),
            'forced_to' => $toRecipients,
            'forced_cc' => $ccRecipients,
        ]);
    }

    send_response(500, 'error', $mappedMessage);
}

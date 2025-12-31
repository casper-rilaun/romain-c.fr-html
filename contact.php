<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

/**
 * Minimal .env loader (no dependency)
 */
function loadEnv(string $path): void {
  if (!is_readable($path)) return;
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $k = trim($k);
    $v = trim($v);
    $v = trim($v, "\"'");
    if ($k !== '' && getenv($k) === false) {
      putenv("$k=$v");
      $_ENV[$k] = $v;
    }
  }
}

function env(string $key, ?string $default = null): string {
  $v = getenv($key);
  if ($v === false || $v === '') return (string)$default;
  return (string)$v;
}

function fail(int $code, string $msg): void {
  http_response_code($code);
  echo "<!doctype html><html lang='fr'><meta charset='utf-8'><title>Contact</title>
        <body style='font-family:system-ui;padding:24px;max-width:720px;margin:0 auto;'>
        <h2>Contact</h2><p>$msg</p><p><a href='./contact.html'>← Retour</a></p></body></html>";
  exit;
}

// Load .env (local), you can also use real env vars at runtime.
loadEnv('/home/rnymo/.env');

// Only POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  fail(405, "Méthode non autorisée.");
}

// Rate limit (file-based per IP)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$bucketDir = __DIR__ . '/_rate';
if (!is_dir($bucketDir)) @mkdir($bucketDir, 0755, true);
$bucketFile = $bucketDir . '/' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $ip) . '.json';

$now = time();
$windowSeconds = 60; // 60s
$maxRequests = 3;    // 3 requests/min
$data = ['ts' => []];

if (file_exists($bucketFile)) {
  $raw = @file_get_contents($bucketFile);
  $decoded = json_decode($raw ?: '', true);
  if (is_array($decoded) && isset($decoded['ts']) && is_array($decoded['ts'])) {
    $data = $decoded;
  }
}
$data['ts'] = array_values(array_filter($data['ts'], fn($t) => is_int($t) && ($now - $t) < $windowSeconds));
if (count($data['ts']) >= $maxRequests) {
  fail(429, "Trop de messages envoyés. Réessaie dans une minute 🙂");
}
$data['ts'][] = $now;
@file_put_contents($bucketFile, json_encode($data));

// Honeypot (must be empty)
$website = trim((string)($_POST['website'] ?? ''));
$companyFax = trim((string)($_POST['company_fax'] ?? ''));
if ($companyFax !== '') {
  fail(400, "HP non vide");
}

// Timing (client timestamp) — tolère un décalage d'horloge
$t = (int)($_POST['t'] ?? 0);
$nowMs = (int)floor(microtime(true) * 1000);

if ($t <= 0) {
  fail(400, "Timing KO: t manquant");
}

// Si le client est "trop" dans le futur par rapport au serveur, c'est suspect
$maxFutureSkewMs = 5 * 60 * 1000; // 5 minutes
if ($t > ($nowMs + $maxFutureSkewMs)) {
  fail(400, "Timing KO: horloge invalide");
}

// Anti-bot "trop rapide" : uniquement si le client n'est PAS dans le futur
$delta = $nowMs - $t;
if ($delta >= 0 && $delta < 300) { // < 0.3s
  fail(400, "Timing KO: trop rapide");
}

// Inputs
$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

// Validate
if (mb_strlen($name) < 2 || mb_strlen($subject) < 3 || mb_strlen($message) < 10) {
  fail(400, "Merci de remplir tous les champs correctement.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  fail(400, "Email invalide.");
}

// SMTP config
$smtpHost = env('SMTP_HOST');
$smtpPort = (int)env('SMTP_PORT', '587');
$smtpSecure = env('SMTP_SECURE', 'tls'); // tls|ssl|''

$smtpUser = env('SMTP_USER');
$smtpPass = env('SMTP_PASS');

$mailTo = env('MAIL_TO');
$mailFrom = env('MAIL_FROM', $smtpUser);
$mailFromName = env('MAIL_FROM_NAME', 'CV');
$subjectPrefix = env('MAIL_SUBJECT_PREFIX', '[CV]');

if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '' || $mailTo === '' || $mailFrom === '') {
  fail(500, "Configuration serveur incomplète (SMTP).");
}

$fullSubject = trim($subjectPrefix . ' ' . $subject);

// Build body
$body = "Nouveau message depuis le formulaire CV\n\n"
      . "Nom: {$name}\n"
      . "Email: {$email}\n"
      . "IP: {$ip}\n"
      . "Date: " . date('c') . "\n\n"
      . "Message:\n{$message}\n";

try {
  $mail = new PHPMailer(true);
  $mail->CharSet = 'UTF-8';

  // SMTP
  $mail->isSMTP();
  $mail->Host = $smtpHost;
  $mail->SMTPAuth = true;
  $mail->Username = $smtpUser;
  $mail->Password = $smtpPass;
  $mail->Port = $smtpPort;

  if ($smtpSecure === 'tls') {
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  } elseif ($smtpSecure === 'ssl') {
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  }

  // From must be YOUR domain (deliverability)
  $mail->setFrom($mailFrom, $mailFromName);

  // Reply-To = user (so "Reply" answers them)
  $mail->addReplyTo($email, $name);

  $mail->addAddress($mailTo);

  $mail->Subject = $fullSubject;
  $mail->Body = $body;

  // Optional: improve deliverability
  $mail->addCustomHeader('X-Contact-Form', 'cv-site');

  $mail->send();
} catch (Exception $e) {
  // Avoid leaking server info to user
  fail(500, "Oups, l'envoi a échoué. Tu peux me contacter via LinkedIn.");
}

echo "<!doctype html><html lang='fr'><meta charset='utf-8'><title>Message envoyé</title>
      <body style='font-family:system-ui;padding:24px;max-width:720px;margin:0 auto;'>
      <h2>✅ Message envoyé</h2>
      <p>Merci ! Je reviens vers toi rapidement.</p>
      <p><a href='./index.html#top'>← Retour au CV</a></p>
      </body></html>";

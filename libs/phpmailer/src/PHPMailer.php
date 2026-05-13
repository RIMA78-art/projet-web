<?php
namespace PHPMailer\PHPMailer;

class PHPMailer {
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS    = 'ssl';

    public $Host        = 'localhost';
    public $Port        = 25;
    public $Username    = '';
    public $Password    = '';
    public $SMTPAuth    = false;
    public $SMTPSecure  = '';
    public $SMTPDebug   = 0;
    public $Timeout     = 30;
    public $CharSet     = 'UTF-8';
    public $From        = '';
    public $FromName    = '';
    public $Subject     = '';
    public $Body        = '';
    public $AltBody     = '';
    public $ErrorInfo   = '';
    public $SMTPKeepAlive = false;

    protected $mailer  = 'mail';
    protected $to      = [];
    protected $cc      = [];
    protected $bcc     = [];
    protected $smtp    = null;
    protected $htmlMsg = false;

    private $exceptions;

    public function __construct($exceptions = false) {
        $this->exceptions = $exceptions;
    }

    public function isSMTP() { $this->mailer = 'smtp'; }
    public function isMail() { $this->mailer = 'mail'; }

    public function isHTML($v = true) { $this->htmlMsg = $v; }

    public function setFrom($addr, $name = '') {
        $this->From     = $addr;
        $this->FromName = $name;
        return true;
    }

    public function addAddress($addr, $name = '') {
        $this->to[] = [$addr, $name];
        return true;
    }

    public function addCC($addr, $name = '')  { $this->cc[]  = [$addr, $name]; return true; }
    public function addBCC($addr, $name = '') { $this->bcc[] = [$addr, $name]; return true; }

    public function clearAddresses()     { $this->to  = []; }
    public function clearAllRecipients() { $this->to = $this->cc = $this->bcc = []; }

    public function send() {
        try {
            if (empty($this->From)) {
                throw new Exception('Adresse expéditeur manquante');
            }
            if (empty($this->to)) {
                throw new Exception('Aucun destinataire');
            }
            return $this->mailer === 'smtp' ? $this->envoyerSMTP() : $this->envoyerMail();
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            if ($this->exceptions) throw $e;
            return false;
        }
    }

    // ─── Construction du message ─────────────────────────────────────────────

    protected function buildHeader() {
        $h  = 'Date: ' . date('r') . "\r\n";
        $h .= 'From: ' . $this->encodeNom($this->FromName) . ' <' . $this->From . ">\r\n";
        foreach ($this->to as [$addr, $name]) {
            $h .= 'To: ' . ($name ? $this->encodeNom($name) . ' <' . $addr . '>' : $addr) . "\r\n";
        }
        foreach ($this->cc as [$addr, $name]) {
            $h .= 'Cc: ' . ($name ? $this->encodeNom($name) . ' <' . $addr . '>' : $addr) . "\r\n";
        }
        $h .= 'Subject: ' . $this->encodeNom($this->Subject) . "\r\n";
        $h .= 'MIME-Version: 1.0' . "\r\n";
        $h .= 'X-Mailer: NutriNova-Mailer' . "\r\n";
        return $h;
    }

    protected function buildMessage() {
        if ($this->htmlMsg && $this->AltBody !== '') {
            $b = md5(uniqid('', true));
            $msg  = 'Content-Type: multipart/alternative; boundary="' . $b . '"' . "\r\n\r\n";
            $msg .= '--' . $b . "\r\n";
            $msg .= 'Content-Type: text/plain; charset=' . $this->CharSet . "\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $msg .= chunk_split(base64_encode($this->AltBody)) . "\r\n";
            $msg .= '--' . $b . "\r\n";
            $msg .= 'Content-Type: text/html; charset=' . $this->CharSet . "\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $msg .= chunk_split(base64_encode($this->Body)) . "\r\n";
            $msg .= '--' . $b . "--\r\n";
            return $msg;
        }
        $type = $this->htmlMsg ? 'text/html' : 'text/plain';
        $text = $this->htmlMsg ? $this->Body : ($this->AltBody !== '' ? $this->AltBody : $this->Body);
        $msg  = 'Content-Type: ' . $type . '; charset=' . $this->CharSet . "\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($text));
        return $msg;
    }

    // ─── Envoi SMTP ──────────────────────────────────────────────────────────

    protected function envoyerSMTP() {
        $this->smtp = new SMTP();
        $this->smtp->do_debug = $this->SMTPDebug;

        $host = $this->Host;
        if ($this->SMTPSecure === self::ENCRYPTION_SMTPS) {
            $host = 'ssl://' . $host;
        }

        if (!$this->smtp->connect($host, $this->Port, $this->Timeout)) {
            $err = $this->smtp->getError();
            throw new Exception('Connexion SMTP impossible : ' . $err['error']);
        }

        $this->smtp->hello(gethostname() ?: 'localhost');

        if ($this->SMTPSecure === self::ENCRYPTION_STARTTLS) {
            if (!$this->smtp->startTLS()) {
                throw new Exception('Impossible de démarrer TLS');
            }
            $this->smtp->hello(gethostname() ?: 'localhost');
        }

        if ($this->SMTPAuth) {
            if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                $err = $this->smtp->getError();
                throw new Exception('SMTP Error: Password: ' . $err['error']);
            }
        }

        if (!$this->smtp->mail($this->From)) {
            throw new Exception('MAIL FROM échoué');
        }

        $tous = array_merge($this->to, $this->cc, $this->bcc);
        foreach ($tous as [$addr]) {
            if (!$this->smtp->recipient($addr)) {
                throw new Exception('RCPT TO échoué pour : ' . $addr);
            }
        }

        $message = $this->buildHeader() . $this->buildMessage();
        if (!$this->smtp->data($message)) {
            throw new Exception('Envoi du message échoué');
        }

        if (!$this->SMTPKeepAlive) {
            $this->smtp->quit();
        }
        return true;
    }

    // ─── Envoi mail() natif (fallback) ───────────────────────────────────────

    protected function envoyerMail() {
        $to      = implode(', ', array_map(fn($r) => $r[0], $this->to));
        $subject = $this->encodeNom($this->Subject);
        $body    = $this->buildMessage();
        $headers = $this->buildHeader();
        return mail($to, $subject, $body, $headers);
    }

    // ─── Utilitaires ─────────────────────────────────────────────────────────

    protected function encodeNom($str) {
        if (!$str) return '';
        if (preg_match('/[^\x20-\x7E]/', $str)) {
            return '=?UTF-8?B?' . base64_encode($str) . '?=';
        }
        return $str;
    }
}
?>

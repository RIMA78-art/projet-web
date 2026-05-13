<?php
namespace PHPMailer\PHPMailer;

class SMTP {
    const DEBUG_OFF    = 0;
    const DEBUG_CLIENT = 1;
    const DEBUG_SERVER = 2;

    public $do_debug    = self::DEBUG_OFF;
    public $Debugoutput = 'echo';

    protected $smtp_conn  = null;
    protected $last_reply = '';
    protected $error      = ['error' => ''];
    protected $server_caps = null;

    public function connect($host, $port = 25, $timeout = 30, $options = []) {
        $this->error = ['error' => ''];
        if ($this->connected()) {
            $this->close();
        }
        $this->smtp_conn = @stream_socket_client(
            $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            stream_context_create($options)
        );
        if (!is_resource($this->smtp_conn)) {
            $this->error = ['error' => 'Failed to connect: ' . $errstr, 'detail' => $errno];
            return false;
        }
        stream_set_timeout($this->smtp_conn, $timeout);
        $this->get_lines();
        return true;
    }

    public function startTLS() {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }
        $crypto = stream_socket_enable_crypto(
            $this->smtp_conn,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        );
        return (bool) $crypto;
    }

    public function authenticate($username, $password, $authtype = 'LOGIN', $OAuth = null) {
        switch (strtoupper($authtype)) {
            case 'LOGIN':
                if (!$this->sendCommand('AUTH LOGIN', 'AUTH LOGIN', 334)) return false;
                if (!$this->sendCommand('Username', base64_encode($username), 334)) return false;
                if (!$this->sendCommand('Password', base64_encode($password), 235)) return false;
                return true;
            case 'PLAIN':
                if (!$this->sendCommand('AUTH PLAIN',
                    'AUTH PLAIN ' . base64_encode("\0" . $username . "\0" . $password), 235)) {
                    return false;
                }
                return true;
            default:
                return false;
        }
    }

    public function connected() {
        if (is_resource($this->smtp_conn)) {
            $info = stream_get_meta_data($this->smtp_conn);
            return !$info['eof'];
        }
        return false;
    }

    public function close() {
        $this->server_caps = null;
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }

    public function hello($host = '') {
        if ($this->sendCommand('EHLO', 'EHLO ' . $host, 250)) {
            $this->parseCapabilities();
            return true;
        }
        return $this->sendCommand('HELO', 'HELO ' . $host, 250);
    }

    protected function parseCapabilities() {
        $this->server_caps = [];
        foreach (explode("\n", $this->last_reply) as $line) {
            $line = trim(substr($line, 4));
            if ($line === '') continue;
            $parts = explode(' ', $line);
            $cap   = array_shift($parts);
            $this->server_caps[$cap] = count($parts) ? $parts : true;
        }
    }

    public function getServerExt($name) {
        return $this->server_caps[$name] ?? null;
    }

    public function mail($from) {
        return $this->sendCommand('MAIL FROM', 'MAIL FROM:<' . $from . '>', 250);
    }

    public function recipient($to) {
        return $this->sendCommand('RCPT TO', 'RCPT TO:<' . $to . '>', [250, 251]);
    }

    public function data($msg) {
        if (!$this->sendCommand('DATA', 'DATA', 354)) return false;

        // Normaliser les fins de ligne
        $msg = str_replace(["\r\n", "\r", "\n"], "\n", $msg);
        $msg = str_replace("\n", "\r\n", $msg);

        // Dot-stuffing : doubler tout point en début de ligne (RFC 5321)
        $msg = str_replace("\r\n.", "\r\n..", $msg);

        // Envoyer le message puis la séquence de fin "CRLF.CRLF"
        $this->client_send($msg . "\r\n.\r\n");

        // Lire la réponse 250 du serveur
        $this->last_reply = $this->get_lines();
        if (preg_match('/^250/m', $this->last_reply)) {
            return true;
        }
        $this->error = ['error' => 'DATA END: ' . trim($this->last_reply)];
        return false;
    }

    public function quit($close_on_error = true) {
        $ok = $this->sendCommand('QUIT', 'QUIT', 221);
        $this->close();
        return $ok;
    }

    public function reset() {
        return $this->sendCommand('RSET', 'RSET', 250);
    }

    public function sendCommand($cmd, $commandstring, $expect) {
        if (!$this->connected()) {
            $this->error = ['error' => $cmd . ': not connected'];
            return false;
        }
        if ($commandstring !== '') {
            $this->client_send($commandstring . "\r\n");
        }
        $this->last_reply = $this->get_lines();
        $codes = (array) $expect;
        if (preg_match('/^(\d{3})[ -]/m', $this->last_reply, $m)) {
            if (in_array((int) $m[1], $codes)) return true;
        }
        $this->error = ['error' => $cmd . ': ' . trim($this->last_reply)];
        return false;
    }

    public function client_send($data) {
        return fwrite($this->smtp_conn, $data);
    }

    public function getError() {
        return $this->error;
    }

    public function getLastReply() {
        return $this->last_reply;
    }

    protected function get_lines() {
        if (!is_resource($this->smtp_conn)) return '';
        $data    = '';
        $timeout = time() + 60;
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
            $line = fgets($this->smtp_conn, 515);
            if ($line === false) break;
            $data .= $line;
            // La réponse se termine quand le 4e caractère est un espace
            if (strlen($line) >= 4 && $line[3] === ' ') break;
            if (time() > $timeout) break;
        }
        return $data;
    }
}
?>

<?php

$port = (int) getenv('SMTP_PORT');
$server = stream_socket_server("tcp://127.0.0.1:{$port}", $errorCode, $errorMessage);

if ($server === false) {
    fwrite(STDERR, "No se pudo iniciar SMTP: {$errorMessage} ({$errorCode}).\n");
    exit(1);
}

fwrite(STDOUT, "READY\n");
fflush(STDOUT);

$connection = stream_socket_accept($server, 10);

if ($connection === false) {
    fwrite(STDERR, "No se recibió una conexión SMTP.\n");
    exit(2);
}

stream_set_timeout($connection, 10);
fwrite($connection, "220 localhost ESMTP prueba\r\n");

$recipient = '';
$receivingData = false;

while (($line = fgets($connection)) !== false) {
    $command = rtrim($line, "\r\n");

    if ($receivingData) {
        if ($command === '.') {
            fwrite($connection, "250 2.0.0 Mensaje aceptado\r\n");
            fwrite(STDOUT, "RECIPIENT:{$recipient}\n");
            fflush(STDOUT);
            break;
        }

        continue;
    }

    if (preg_match('/^EHLO |^HELO /i', $command) === 1) {
        fwrite($connection, "250-localhost\r\n250 8BITMIME\r\n");
    } elseif (str_starts_with(strtoupper($command), 'MAIL FROM:')) {
        fwrite($connection, "250 2.1.0 Remitente aceptado\r\n");
    } elseif (preg_match('/^RCPT TO:<([^>]+)>/i', $command, $matches) === 1) {
        $recipient = $matches[1];
        fwrite($connection, "250 2.1.5 Destinatario aceptado\r\n");
    } elseif (strtoupper($command) === 'DATA') {
        $receivingData = true;
        fwrite($connection, "354 Termina con punto\r\n");
    } elseif (strtoupper($command) === 'QUIT') {
        fwrite($connection, "221 2.0.0 Adiós\r\n");
        break;
    } else {
        fwrite($connection, "250 2.0.0 OK\r\n");
    }
}

fclose($connection);
fclose($server);

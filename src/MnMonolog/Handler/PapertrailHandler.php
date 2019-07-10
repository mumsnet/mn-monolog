<?php declare(strict_types=1);

namespace MnMonolog\Handler;

use Monolog\Logger;
use Monolog\Handler\SyslogUdp\UdpSocket;
use Monolog\Handler\AbstractSyslogHandler;

/**
 * A Handler for logging Papertrail.
 * Based on SyslogUdpHandler from Monolog.
 */
class PapertrailHandler extends AbstractSyslogHandler {
    const RFC5424 = 1;
    private $dateFormats = array(
        self::RFC5424 => \DateTime::RFC3339
    );
    protected $socket;
    protected $localhost;
    protected $ident;
    protected $rfc;

    /**
     * @param string $host the hostname of the Papertrail server
     * @param int $port the UDP port number of the Papertrail server
     * @param string $localhost the hostname of your environment eg: stage.company.com or www.company.com
     * @param string $ident Program name or tag for each log message.
     * @param string|int $level The minimum logging level at which this handler will be triggered
     */
    public function __construct(string $host, int $port, string $localhost, string $ident, $level = Logger::DEBUG) {
        parent::__construct(LOG_USER, $level, TRUE);
        $this->localhost = $localhost;
        $this->ident = $ident;
        $this->rfc = self::RFC5424;
        $this->socket = new UdpSocket($host, $port);
    }

    protected function write(array $record): void {
        $lines = $this->splitMessageIntoLines($record['formatted']);
        $header = $this->makeCommonSyslogHeader($this->logLevels[$record['level']]);
        foreach ($lines as $line) {
            $this->socket->write($line, $header);
        }
    }

    public function close(): void {
        $this->socket->close();
    }

    private function splitMessageIntoLines($message): array {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        return preg_split('/$\R?^/m', (string)$message, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Make common syslog header (see rfc5424)
     */
    protected function makeCommonSyslogHeader(int $severity): string {
        $priority = $severity + $this->facility;
        if (!$pid = getmypid()) {
            $pid = '-';
        }
        $date = $this->getDateTime();
        return "<$priority>1 " .
            $date . " " .
            $this->localhost . " " .
            $this->ident . " " .
            $pid . " - - ";
    }

    protected function getDateTime(): string {
        return date($this->dateFormats[$this->rfc]);
    }

    /**
     * Inject your own socket, mainly used for testing
     */
    public function setSocket(UdpSocket $socket): self
    {
        $this->socket = $socket;
        return $this;
    }
}
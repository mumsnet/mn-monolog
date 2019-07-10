<?php declare(strict_types=1);

namespace MnMonolog\Test;

use Monolog\Logger;
use Monolog\DateTimeImmutable;
use Monolog\Formatter\FormatterInterface;

/**
 * Lets you easily generate log records and a dummy formatter for testing purposes
 * *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class TestCase extends \PHPUnit\Framework\TestCase {
    /**
     * @return array Record
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', array $context = []): array {
        return [
            'message' => (string)$message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => new DateTimeImmutable(TRUE),
            'extra' => [],
        ];
    }

    protected function getMultipleRecords(): array {
        return [
            $this->getRecord(Logger::DEBUG, 'debug message 1'),
            $this->getRecord(Logger::DEBUG, 'debug message 2'),
            $this->getRecord(Logger::INFO, 'information'),
            $this->getRecord(Logger::WARNING, 'warning'),
            $this->getRecord(Logger::ERROR, 'error'),
        ];
    }

    /**
     * @suppress PhanTypeMismatchReturn
     */
    protected function getIdentityFormatter(): FormatterInterface {
        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->expects($this->any())
            ->method('format')
            ->will($this->returnCallback(function ($record) {
                return $record['message'];
            }));
        return $formatter;
    }
}
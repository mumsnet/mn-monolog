<?php declare(strict_types=1);

namespace MnMonolog\Handler;

use MnMonolog\Test\TestCase;

/**
 * @requires extension sockets
 */
class PapertrailHandlerTest extends TestCase {

    public function testWeSplitIntoLines() {
        $time = '2014-01-07T12:34';
        $pid = getmypid();
        $host = "testpapertrailhandler.devmn.net";
        $handler = $this->getMockBuilder('\MnMonolog\Handler\PapertrailHandler')
            ->setConstructorArgs(array("127.0.0.1", 514, $host, "test"))
            ->setMethods(array('getDateTime'))
            ->getMock();
        $handler->method('getDateTime')
            ->willReturn($time);
        $handler->setFormatter(new \Monolog\Formatter\ChromePHPFormatter());
        $socket = $this->getMockBuilder('Monolog\Handler\SyslogUdp\UdpSocket')
            ->setMethods(['write'])
            ->setConstructorArgs(['lol'])
            ->getMock();
        $socket->expects($this->at(0))
            ->method('write')
            ->with("lol", "<" . (LOG_USER + LOG_WARNING) . ">1 $time $host test $pid - - ");
        $socket->expects($this->at(1))
            ->method('write')
            ->with("hej", "<" . (LOG_USER + LOG_WARNING) . ">1 $time $host test $pid - - ");
        $handler->setSocket($socket);
        $handler->handle($this->getRecordWithMessage("hej\nlol"));
    }

    public function testSplitWorksOnEmptyMsg() {
        $handler = new PapertrailHandler("127.0.0.1", 514, "testpapertrailhandler.devmn.net", "test");
        $handler->setFormatter($this->getIdentityFormatter());
        $socket = $this->getMockBuilder('Monolog\Handler\SyslogUdp\UdpSocket')
            ->setMethods(['write'])
            ->setConstructorArgs(['lol'])
            ->getMock();
        $socket->expects($this->never())
            ->method('write');
        $handler->setSocket($socket);
        $handler->handle($this->getRecordWithMessage(NULL));
    }

    public function testRfc() {
        $time = 'Mar 22 21:16:47';
        $pid = getmypid();
        $host = "testpapertrailhandler.devmn.net";
        $handler = $this->getMockBuilder('\MnMonolog\Handler\PapertrailHandler')
            ->setConstructorArgs(array(
                "127.0.0.1", 514, $host, "test"
            ))
            ->setMethods(array('getDateTime'))
            ->getMock();
        $handler->method('getDateTime')
            ->willReturn($time);
        $handler->setFormatter(new \Monolog\Formatter\ChromePHPFormatter());
        $socket = $this->getMockBuilder('\Monolog\Handler\SyslogUdp\UdpSocket')
            ->setConstructorArgs(array('lol', 999))
            ->setMethods(array('write'))
            ->getMock();
        $socket->expects($this->at(0))
            ->method('write')
            ->with("lol", "<" . (LOG_USER + LOG_WARNING) . ">1 $time $host test $pid - - ");
        $socket->expects($this->at(1))
            ->method('write')
            ->with("hej", "<" . (LOG_USER + LOG_WARNING) . ">1 $time $host test $pid - - ");
        $handler->setSocket($socket);
        $handler->handle($this->getRecordWithMessage("hej\nlol"));
    }

    protected function getRecordWithMessage($msg) {
        return [
            'message' => $msg, 'level' => \Monolog\Logger::WARNING, 'context' => NULL, 'extra' => [], 'channel' => 'lol'
        ];
    }
}
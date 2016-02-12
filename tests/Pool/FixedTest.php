<?php

namespace WyriHaximus\React\Tests\ChildProcess\Pool\Pool;

use Phake;
use WyriHaximus\React\ChildProcess\Pool\Pool\Fixed;

class FixedTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $process = Phake::mock('React\ChildProcess\Process');
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        $poolPromise = Fixed::create($process, $loop);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $poolPromise);
        $promiseHasResolved = false;
        $poolPromise->then(function ($pool) use (&$promiseHasResolved) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\Pool\Fixed', $pool);
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Pool\PoolInterface', $pool);
            $promiseHasResolved = true;
        });
        $this->assertTrue($promiseHasResolved);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Given class doesn't implement ChildInterface
     */
    public function testCreateFromClass()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        Fixed::createFromClass('stdClass', $loop);
    }
}

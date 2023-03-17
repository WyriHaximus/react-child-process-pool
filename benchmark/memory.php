<?php

const MB = 1048576;
const I = 25000;

function show_memory($message = '')
{
    echo '------------------------------------------', PHP_EOL;
    echo $message, PHP_EOL;
    echo date(DATE_RFC822), PHP_EOL;
    echo '------------------------------------------', PHP_EOL;
    echo 'Current memory usage: ',      memory_get_usage()          / MB, 'MB', PHP_EOL;
    echo 'Peak memory usage: ',         memory_get_peak_usage()     / MB, 'MB', PHP_EOL;
    echo 'Current real memory usage: ', memory_get_usage(true)      / MB, 'MB', PHP_EOL;
    echo 'Peak real memory usage: ',    memory_get_peak_usage(true) / MB, 'MB', PHP_EOL;
}

show_memory('Bare init');

require dirname(__DIR__) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

show_memory('Begin');

$loop = EventLoopFactory::create();

Flexible::createFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)->done(function (PoolInterface $messenger) use ($loop) {
    $messenger->on('error', function ($e) {
        echo 'Error: ', var_export($e, true), PHP_EOL;
    });

    $i = 0;
    $timer = $loop->addPeriodicTimer(0.0001, function () use (&$i, $messenger, &$timer, $loop) {
        if ($i >= I) {
            $loop->cancelTimer($timer);
            $timer = null;
            $messenger->terminate();

            show_memory('Completed messaging');
            return;
        }

        $messenger->rpc(MessagesFactory::rpc('return', [
            'i' => $i,
            'time' => time(),
        ]));

        $i++;
    });
});

$loop->run();

show_memory('Done');

unset($loop);
$loop = null;

show_memory('Removed loop');

$cycles = gc_collect_cycles();

show_memory('gc_collect_cycles: ' . $cycles);

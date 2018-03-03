<?php
/**
 * This file is part of workerman.
*
* Licensed under The MIT License
* For full copyright and license information, please see the MIT-LICENSE.txt
* Redistributions of files must retain the above copyright notice.
*
* @author walkor<walkor@workerman.net>
* @copyright walkor<walkor@workerman.net>
* @link http://www.workerman.net/
* @license http://www.opensource.org/licenses/mit-license.php MIT License
*/

use \Workerman\Worker;
use \Workerman\WebServer;
use \Workerman\Connection\TcpConnection;

// Command. For example 'tail -f /var/log/nginx/access.log'.
define('CMD', 'bash');

// Whether to allow client input.
define('ALLOW_CLIENT_INPUT', true);

// Uinix user for command. Recommend nobody www etc. 
define('USER', 'www-data');

require_once __DIR__ . '/vendor/autoload.php';

$worker = new Worker("Websocket://0.0.0.0:7778");
$worker->name = 'phptty';
$worker->user = USER;

// on connection
$worker->onConnect = function ($connection) {

    unset($_SERVER['argv']);

    // initialise process
    $connection->process = proc_open(CMD, [
        0 => ["pty", "r"],
        1 => ["pty", "w"],
        2 => ["pty", "w"]
    ], $pipes, realpath('./'), array_merge(
        ['COLUMNS' => 130, 'LINES' => 50], $_SERVER
    ));

    $connection->pipes = $pipes;
    stream_set_blocking($pipes[0], 0);

    // stdout
    $connection->stdout = new TcpConnection($pipes[1]);
    //
    $connection->stdout->onMessage = function ($process_connection, $data) use ($connection) {
        $connection->send($data);
    };
    //
    $connection->stdout->onClose = function ($process_connection) use ($connection) {
        $connection->close(); // Close WebSocket connection on process exit.
    };

    // stdin
    $connection->stdin = new TcpConnection($pipes[2]);
    //
    $connection->stdin->onMessage = function ($process_connection, $data) use ($connection) {
        $connection->send($data);
    };
};

// on websocket message
$worker->onMessage = function ($connection, $data) {
    if (ALLOW_CLIENT_INPUT) {
        fwrite($connection->pipes[0], $data);
    }
};

// on websocket close
$worker->onClose = function ($connection) {
    $connection->stdin->close();
    $connection->stdout->close();
    fclose($connection->pipes[0]);
    $connection->pipes = null;
    proc_terminate($connection->process);
    proc_close($connection->process);
    $connection->process = null;
};

// on worker stopped
$worker->onWorkerStop = function ($worker) {
    foreach ($worker->connections as $connection) {
        $connection->close();
    }
};

// create webserver
$webserver = new WebServer('http://0.0.0.0:7779');
$webserver->addRoot('localhost', __DIR__ . '/Web');

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}

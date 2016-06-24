<?php
/*
 :) http://stackoverflow.com/questions/13847462/how-do-i-stop-a-php-script-running-on-the-background
 */

//ignore_user_abort(true);    // run script in background
error_reporting(~E_NOTICE); // 
set_time_limit (0);         // run script forever
//ob_implicit_flush(true);    // flush after each echo()

require('autoloader.php');

use Ratchet\Server\IoServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

use Primitives\Logger;

use TopLevel\Sockets;
use TopLevel\World;

require 'vendor/autoload.php';

$address = "127.0.0.1";
$port = 4096;
$max_clients = 10;

$logger  = new Logger();
$world   = new World( $logger );


// Note: the idea of decompositioning IoServer::factory() in order to add timer to the main loop 
//       is something you can come up with after studying its source
//       https://github.com/ratchetphp/Ratchet/blob/master/src/Ratchet/Server/IoServer.php#L68.
//       But many stack overflow threads are for making easier having this idea.

$loop = LoopFactory::create();
$socket = new Reactor( $loop );
$socket->listen( 8080, '0.0.0.0');
$core = new Sockets( $logger );
$server = new IoServer($core, $socket, $loop);
$loop->addPeriodicTimer(2,
   function($timer) use ( $core, $world ) {
     $core->passClientActionsToWorld( $world );
     $world->tick();
     $core->talkToClientsAboutWorld( $world );
   }
);

$server->run();

?>

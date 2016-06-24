<?php
/*
 :) http://stackoverflow.com/questions/13847462/how-do-i-stop-a-php-script-running-on-the-background
 */

//ignore_user_abort(true);    // run script in background
error_reporting(~E_NOTICE); // 
set_time_limit (0);         // run script forever
//ob_implicit_flush(true);    // flush after each echo()

spl_autoload_register(
   function($className)
   {
      $className = str_replace("_", "\\", $className);
      $className = ltrim($className, '\\');
      $fileName = '';
      $namespace = '';
      if ($lastNsPos = strripos($className, '\\'))
      {
         $namespace = substr($className, 0, $lastNsPos);
         $className = substr($className, $lastNsPos + 1);
         $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
      }
      $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
      require $fileName;
   }
);

//set_include_path(__DIR__."/html");
use Ratchet\Server\IoServer;
//use MyApp\Chat;
use Primitives\Logger;
use Primitives\Timer;

use TopLevel\Sockets;
use TopLevel\World;

require 'vendor/autoload.php';

$address = "127.0.0.1";
$port = 4096;
$max_clients = 10;

$logger  = new Logger();
$timer   = new Timer(100);

$world   = new World( $logger );
$sockets = new Sockets( $logger, $address, $port, $max_clients );

if ( !$sockets->isAlive() ) {
   die( "Could not establish sockets.\n");
}

while (1) {
   $logger->log("Time is ".microtime(true), 1);
   $world->tick();
   $sockets->addToOutEventQueue( $world->getWorldEvents() );
   $sockets->readAndWriteSockets();
   $world->handleEvents( $sockets->getAndDestroyInEventQueue() );
   $timer->waitUntilNextTick();
}

microtime(true) * 1000;
?>

<?php

namespace TopLevel;

use Primitives\Event;
use TopLevel\World;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

define( 'UNPROCESSED_MESSAGE_LIMIT', 10 );

class Sockets implements MessageComponentInterface {
   protected $clients;

   public function __construct( $logger ) {
      $this->logger = $logger;      
      $this->clients = new \SplObjectStorage;

      $this->logger->log("SOCKETS Starting.", 2);
      $this->inPostboxesByResourceID = array();
   }

   public function onOpen(ConnectionInterface $conn) {
      // Store the new connection to send messages to later
      $this->clients->attach($conn);

      $this->logger->log("SOCKETS New connection! ({$conn->resourceId})", 2);
      $this->inPostboxesByResourceID[ $conn->resourceId ] = array();
      array_push( $this->inPostboxesByResourceID[ $conn->resourceId ], new Event( Event::BORN, array( "" ) ) );
      $conn->send( "Bravo.\n" );
   }

   public function onMessage(ConnectionInterface $from, $msg) {
      $numRecv = count($this->clients) - 1;
      $this->logger->log( 'SOCKETS '.sprintf('Connection %d sending message "%s" ', $from->resourceId, $msg), 2);

      array_push( $this->inPostboxesByResourceID[ $from->resourceId ], new Event( Event::SAY, array( $msg ) ) );

      // do not allow the client to flood us
      if ( count( $this->inPostboxesByResourceID[ $from->resourceId ] ) > UNPROCESSED_MESSAGE_LIMIT ) {
         unset( $this->inPostboxesByResourceID[ $from->resourceId ] );
         $this->inPostboxesByResourceID[ $from->resourceId ] = array();
         $from->close(); 
      }
   }

   public function passClientActionsToWorld( World $world ) {
      foreach ($this->clients as $client) {
         if ( count( $this->inPostboxesByResourceID[ $client->resourceId ] ) > 0 ) {
            $head = array_shift( $this->inPostboxesByResourceID[ $client->resourceId ] );
            if ( !is_null( $head ) ) {
               $world->handleEvent( $head );
            }
         }
      }

   }

   public function talkToClientsAboutWorld( World $world ) {
      foreach ($this->clients as $client) {
         foreach ( $world->getLocalEvents() as $event ) {
            $client->send( $event->convertToString() );
         }
      }
   }

   public function onClose(ConnectionInterface $conn) {
      // The connection is closed, remove it, as we can no longer send it messages
      $this->clients->detach($conn);

      $this->logger->log("SOCKETS Connection {$conn->resourceId} has disconnected", 2);

      // Dead people should not talk
      unset( $this->inPostboxesByResourceID[ $conn->resourceId ] );
      $this->inPostboxesByResourceID[ $conn->resourceId ] = array("");
      array_push( $this->inPostboxesByResourceID[ $conn->resourceId ], new Event( Event::DIED, array( "" ) ) );
   }

   public function onError(ConnectionInterface $conn, \Exception $e) {
      $this->logger->error("SOCKETS An error has occurred: {$e->getMessage()}");
      $conn->close();
   }
}

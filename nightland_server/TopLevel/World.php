<?php

namespace TopLevel;

use \Primitives\Event;
use \Primitives\EventHandler;

class World extends EventHandler
{
   public function handleBornEvent( $arg_array )
   {
      $this->logger->log( "Handle event BORN(".join(',', $arg_array ).")", 6 );
   }

   public function handleDiedEvent( $arg_array )
   {
      $this->logger->log( "Handle event DIED(".join(',', $arg_array ).")", 6 );
   }

   public function handleExplodedEvent( $arg_array )
   {
      $this->logger->log( "Handle event EXPLODED(".join(',', $arg_array ).")", 6 );
   }

   public function handleSayEvent( $arg_array )
   {
      $this->logger->log( "Handle event SAY(".join(',', $arg_array ).")", 6 );
   }

   public function tick()
   {
      $this->counter = $this->counter + 1;
      $this->logger->log( "World ticked, counter is ".$this->counter, 3 );
   }

   public function getLocalEvents()
   {
      if ( $this->counter % 5 == 0 ) {
         return array( new Event( Event::EXPLODED, array($this->counter) ) );
      } else {
         return array();
      }
   }

   public function __construct( $logger ) {
      $this->logger = $logger;
      $this->addCallback( Event::BORN,     array($this, "handleBornEvent"    ) );
      $this->addCallback( Event::DIED,     array($this, "handleDiedEvent"    ) );
      $this->addCallback( Event::EXPLODED, array($this, "handleExplodedEvent") );
      $this->addCallback( Event::SAY     , array($this, "handleSayEvent") );
      $this->counter = 0;
   }
}


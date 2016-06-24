<?php

namespace Primitives;

class Event
{
   const BORN = 1;
   const DIED = 2;
   const EXPLODED = 3;
   const SAY = 4;

   private static $toText = array( 1 => "BORN",
                                   2 => "DIED",
                                   3 => "EXPLODED",
                                   4 => "SAY" );

   public function __construct( $event_type, $arg_array ) {
      $this->event_type  = $event_type;
      $this->arg_array   = $arg_array;
   }

   public function getEventType() { return $this->event_type; }
   public function getArgArray()  { return $this->arg_array; }
   public function getTypeText( $value ) { return self::$toText[ $value ]; }

   public function convertToString() { return $this->getTypeText( $this->event_type ).",".join( ",", $this->arg_array )."\n"; }
}

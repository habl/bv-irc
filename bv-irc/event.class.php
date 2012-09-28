<?php
    /**
     * Library with IRC commands
     *
     * @author hans
     */
    class event extends command
    {
        /**
         * The IRC events
         * 
         * @param array $data the incomming data
         */
        protected function observe( $data )
        {
            if ( substr( $data[0], 0, 1 ) == ":" )
            {
                $from = substr( $data[0], 1 );
                array_shift( $data );
            }
            
            switch ( $data[0] )
            {
                // server ping
                case "PING":
                    $this->sendData( 'PONG', $data[1] );
                    break;
                case "PRIVMSG":
                    // if channel onPublic, if nick onPrivate
                    break;
                case "JOIN":
                    $this->runHandler( 'onJoin' );
                    break;
                case "NOTICE":
                    // onnotice
                    break;
                // end of motd, usefull for onconnect
                case "004":
                    $this->runHandler( 'onConnect' );
                    break;
            }
        }
        
        /**
         * running an event handler
         * 
         * @param string $handler the callback to run (if existing)
         * 
         * @TODO checking if the methods are callable
         * @TODO maybe adding dynamic handlers? $this->registerHandler( 'event', 'callback' );
         */
        private function runHandler( $handler )
        {
            if ( method_exists( $this, $handler )  )
            {
                $this->$handler();
            }
            elseif ( function_exists( $handler ) )
            {
                $handler();
            }
            else
            {
                $this->debug( "! No method found for " . $handler );
            }
        }
    }
?>
<?php
    /**
     * Library with IRC commands
     *
     * @author hans
     */
    class event extends command
    {
        /**
         * array with all events which are being observed
         * 
         * @var array 
         */
        private $events = array();
        
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

            $params = $this->getParameters( $this->getRaw() );
            
            switch ( $data[0] )
            {
                // server ping
                case "PING":
                    $this->sendData( 'PONG', $data[1] );
                    break;
                case "PRIVMSG":
                    
                    if ( $data[1] != $this->getNick() )
                    {
                        $this->runHandler( 'onPublic', $params );
                    }
                    else
                    {
                        $this->runHandler( 'onMessage', $params );
                    }
                    // if channel onPublic, if nick onPrivate
                    break;
                case "JOIN":
                    $this->runHandler( 'onJoin' );
                    break;
                case "NOTICE":
                    // onnotice
                    break;
            }
            
            $this->runDynamicHandlers( $data[0] );
        }
        
        /**
         * Run all dynamically registered event handlers
         * 
         * @param string $curEvent the current event
         */
        private function runDynamicHandlers( $curEvent )
        {
            if ( is_array( $this->events ) )
            {
                foreach ( $this->events as $event => $callbacks )
                {
                    foreach ( $callbacks as $callback )
                    {
                        if ( $curEvent == $event )
                        {
                            $this->runHandler( $callback, $params );
                        }
                    }
                }
            }
        }
        
        /**
         * Register an event handler
         * 
         * @param type $event
         * @param type $callback
         */
        public function registerEvent( $event, $callback )
        {
            $this->events[$event][] = $callback;
        }
        
        /**
         * running an event handler
         * 
         * @param string $handler the callback to run (if existing)
         * @param array $parameters
         * 
         * @TODO checking if the methods are callable
         */
        private function runHandler( $handler, $parameters = false )
        {
            if ( method_exists( $this, $handler )  )
            {
                if ( $parameters )
                    $this->$handler( $parameters );
                else
                    $this->$handler( );
            }
            elseif ( function_exists( $handler ) )
            {
                if ( $parameters )
                    $handler( $parameters );
                else
                    $handler();
            }
            else
            {
                $this->debug( "! No method found for " . $handler );
            }
        }
        
        /**
         * format the raw data so it's more usable
         * 
         * @param string $data raw server input
         * @return array|boolean array with all parameters or false on failure
         */
        private function getParameters( $data )
        {
            if ( preg_match( "/^:([^!]+)!([^\@]+)\@([^\s]+) .* ([^\s]+) \:(.*)$/U", $data, $return ) )
            {
                return array( "from" => $return[1], "fromUser" => $return[2], "fromHost" => $return[3], "to" => $return[4], "parameters" => $return[5] );
            }
            else
            {
                return array();
            }
        }
    }
?>
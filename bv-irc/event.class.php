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
        private $_events = array();
        
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

            $event = strtoupper( $data[0] );
            
            switch ( $event )
            {
                // server ping should always being observed to prevent a connection timeout
                case "PING":
                    $this->sendData( 'PONG', $data[1] );
                    break;
                // privmsg got more event types, public and private, check which handler should be runned
                case "PRIVMSG":
                    // if destination isn't our nick it's a channel message
                    if ( $data[1] != $this->getNick() )
                    {
                        if ( isset( $this->_events['PUBLIC'] ) )
                            $event = "PUBLIC";
                    }
                    // otherwise it's private
                    else
                    {
                        if ( isset( $this->_events['PRIVATE'] ) )
                            $event = "PRIVATE";
                    }
                    break;
                 // nickname already in use
                 case "433":
                     $nick = $this->getNick();
                     
                     // only run if there isn't an event handler registered
                     if ( ! isset( $this->_events['433'] ) )
                     {
                        $altNick = $this->getAltNick();
                        
                        // try alternative nick
                        if ( $altNick && $altNick != $this->getNick() )
                        {
                            $this->log( 'Nickname already in use, trying alternative nickname' );
                            $this->setNick( $altNick );

                            $this->sendData( 'NICK', $altNick );
                        }
                        // if the alternative is also in use disconnect
                        else
                        {
                            $this->log( "Alternative nick also in use" );
                            $this->disconnect();
                        }
                     }
                     break;

            }
            
            $this->runDynamicHandlers( $event );
            
            // check if an event handler changed our nick
            if ( isset( $this->_events['433'] ) && $event == "433" )
            {
                // if not the event handler failed and we should disconnect
                if ( $this->getNick() == $botnick )
                {
                    $this->disconnect();
                }
            }
        }
        
        /**
         * Run all dynamically registered event handlers
         * 
         * @param string $curEvent the current event
         */
        private function runDynamicHandlers( $curEvent )
        {
            // get all parameters
            $params = $this->getParameters( $this->getRaw(), $curEvent );
            
            if ( is_array( $this->_events ) )
            {
                // find if there is an event handler for the current event
                foreach ( $this->_events as $event => $callbacks )
                {
                    foreach ( $callbacks as $callback )
                    {
                        if ( $curEvent == $event )
                        {
                            // run it!
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
            // make it uppercase for the event checks
            $event = strtoupper( $event );
            
            $this->_events[$event][] = $callback;
        }
        
        /**
         * running an event handler
         * 
         * @param string $handler the callback to run (if existing)
         * @param array $parameters
         * 
         * @TODO checking if the methods are callable
         */
        private function runHandler( $handler, $parameters = array() )
        {
            if ( method_exists( $this, $handler )  )
            {
                $this->$handler( $parameters );
            }
            elseif ( function_exists( $handler ) )
            {
                $handler( $parameters );
            }
            else
            {
                $this->debug( "! Method not found: " . $handler );
            }
        }
        
        /**
         * format the raw data so it's more usable
         * 
         * @param string $data raw server input
         * @param string $event the current event
         * @return array|boolean array with all parameters or false on failure
         */
        private function getParameters( $data, $event )
        {
            $params = array();
            
            // fetch all useful information of the server input
            if ( preg_match( "/^:([^\s]+) .*[\s]?([^\s]*)[\s]?\:(.*)$/U", $data, $return ) )
            {
                // fetch nickname, username and host if any
                if ( preg_match( "/^([^!]+)!([^\@]+)\@([^\s]+)$/U", $return[1], $returnFrom ) )
                {
                    $params['from'] = $returnFrom[1];
                    $params['fromUser'] = $returnFrom[2];
                    $params['fromHost'] = $returnFrom[3];
                }
                // and if not just save the complete line as from (in case it's us)
                else
                {
                    $params['from'] = $return[1];
                }
                
                if ( $return[2] != $event )
                {
                    $params['destination'] = $return[2];
                    $params['parameters'] = $return[3];
                }
                else
                {
                    $params['destination'] = $return[3];
                }
                //
                return $params;
            }
            else
            {
                return array();
            }
        }
    }
?>
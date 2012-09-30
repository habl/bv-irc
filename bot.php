<?php
    require_once( "bv-irc/irc.class.php" );
    
    class bot extends IRC
    {
        public function __construct()
        {
            // register onConnect on numeric event 004 (end of motd)
            $this->registerEvent( '004', 'onConnect' );
            $this->registerEvent( 'public', 'onPublic' );
            $this->registerEvent( 'private', 'onMessage' );
            $this->registerEvent( 'JOIN', 'onJoin' );
            $this->registerEvent( 'NOTICE', 'onNotice');
            $this->registerEvent( 'MODE', 'onMode');
        }
        
        /**
         * when the bot connect join a channel
         */
        protected function onConnect()
        {
            $this->joinChannel( '#testerdetest' );
        }
        
        /**
         * when a message is received on the channel
         * 
         * @param array $parameters
         */
        protected function onPublic( $parameters )
        {
            print_r( $parameters );
            if ( $parameters['parameters'] == "hi" )
            {
                $this->privmsg( $parameters['destination'], "hi!" );
            }
            
            if ( $parameters['parameters'] == "!reconnect" )
            {
                $this->reconnect( "Reconnect requested by " . $parameters['from'] );
            }
        }
        
        /**
         * when a private message has been received
         */
        protected function onMessage( $parameters )
        {
            print_r( $parameters );
            
            if ( $parameters['parameters'] == "hi" )
            {
                $this->privmsg( $parameters['from'], "hi!" );
            }
        }
        
        protected function onNotice( $parameters )
        {
            print_r( $parameters );
        }
        
        protected function onMode( $parameters )
        {
            print_r( $parameters );
        }
        
        protected function onJoin( $parameters )
        {
            print_r( $parameters );
            
            if ( $parameters['from'] == $this->getNick() )
            {
                $this->privmsg( $parameters['destination'], 'Woohoo I joined!' );
            }
        }

    }
    
    $irc = new bot();
    
    $irc->setServer( "irc.bitvortex.net" );
    $irc->setPort( 6667 );
    $irc->setAutoReconnect( true );
    
    $irc->setNick( "hablbot" );
    $irc->setAltNick( "hablbot-" );
    $irc->setUser( "hablbot" );
    $irc->setRealName( "habl bot" );
    
    $irc->setDebug( true );
    
    $irc->connect();
?>
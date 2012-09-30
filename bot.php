<?php
    require_once( "bv-irc/irc.class.php" );
    
    class bot extends IRC
    {
        public function __construct()
        {
            // register onConnect on numeric event 004 (end of motd)
            $this->registerEvent( '004', 'onConnect' );
            $this->registerEvent( 'public', 'onPublic' );
            $this->registerEvent( 'private', 'onPrivate' );
            //$this->registerEvent( 'JOIN', 'onJoin' );
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
            if ( $parameters['parameters'] == "hi" )
            {
                $this->privmsg( $parameters['to'], "hi!" );
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
            if ( $parameters['parameters'] == "hi" )
            {
                $this->privmsg( $parameters['from'], "hi!" );
            }
        }
    }
    
    $irc = new bot();
    
    $irc->setServer( "irc.bitvortex.net" );
    $irc->setPort( 6667 );
    $irc->setNick( "hablbot" );
    $irc->setUser( "hablbot" );
    $irc->setRealName( "habl bot" );
    $irc->setDebug( true );
    $irc->setAutoReconnect( true );
    
    $irc->connect();
?>
<?php
    require_once( "bv-irc/irc.class.php" );
    
    class bot extends IRC
    {
        public function __construct()
        {
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
    
    $irc->connect();
?>
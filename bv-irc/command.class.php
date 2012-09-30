<?php
    /**
     * Library with IRC commands
     *
     * @author hans
     */
    class command
    {
        /**
         * Join an IRC channel
         * 
         * @param string $channel
         */
        public function joinChannel( $channel )
        {
            if ( substr( $channel, 0, 1 ) == "#" )
            {
                $this->sendData( 'JOIN', $channel );
            }
        }
        
        public function privmsg( $to, $message )
        {
            $this->sendData( 'PRIVMSG', $to, $message );
        }
        
        /**
         * Sents disconnect command to server
         * 
         * @param string $reason quit reason
         */
        public function quitIrc( $reason )
        {
            $this->sendData( 'QUIT', $reason );
        }
        
        /**
         * Change our nickname
         * 
         * @param string $nick
         */
        public function changeNick( $nick )
        {
            $this->setNick( $nick );
            
            $this->sendData( 'NICK', $nick );
        }
    }
?>
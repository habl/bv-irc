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
    }
?>
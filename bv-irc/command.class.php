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
        
        /**
         * Send irc messages to a destination. Supports multilines
         * 
         * @param string $to
         * @param string $message
         */
        public function privmsg( $to, $message )
        {
            $messages = explode( "\n", $message );
            
            foreach ( $messages as $message )
            {
                $this->sendData( 'PRIVMSG', $to, ':' . $message );
            }
        }
        
        /**
         * Sents disconnect command to server
         * 
         * @param string $reason quit reason
         */
        public function quitIrc( $reason )
        {
            $this->sendData( 'QUIT', ':' . $reason );
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
        
        /**
         * Sent an IRC notice to somebody or channel
         * 
         * @param string $destination
         * @param string $parameters
         */
        public function sendNotice( $destination, $parameters )
        {
            $this->sendData( 'NOTICE', $destination, ':' . $parameters );
        }
        
        /**
         * set an IRC channel mode
         * 
         * @param string $channel
         * @param string $mode
         * @param string $target optional, for opping / deopping
         */
        public function setChannelMode( $channel, $mode, $target = false )
        {
            $parameters = $mode;
            
            if ( $target )
                $parameters .= " " . $target;
            
            $this->sendData( 'MODE', $channel, $parameters );
        }
        
        /**
         * Give a user chanop
         * 
         * @param string $channel
         * @param string $nick
         */
        public function op( $channel, $nick )
        {
            $this->setChannelMode( $channel, "+o", $nick );
        }
        
        /**
         * Remove a user chanop
         * 
         * @param string $channel
         * @param string $nick
         */
        public function deop( $channel, $nick )
        {
            $this->setChannelMode( $channel, "-o", $nick );
        }
        
        /**
         * Give a user voice
         * 
         * @param string $channel
         * @param string $nick
         */
        public function voice( $channel, $nick )
        {
            $this->setChannelMode( $channel, "+v", $nick );
        }
        
        /**
         * Remove a user voice
         * 
         * @param string $channel
         * @param string $nick
         */
        public function dvoice( $channel, $nick )
        {
            $this->setChannelMode( $channel, "-v", $nick );
        }
        
        /**
         * Give a user halfop
         * 
         * @param string $channel
         * @param string $nick
         */
        public function hop( $channel, $nick )
        {
            $this->setChannelMode( $channel, "+h", $nick );
        }
        
        /**
         * Remove a user half op
         * 
         * @param string $channel
         * @param string $nick
         */
        public function dhop( $channel, $nick )
        {
            $this->setChannelMode( $channel, "-h", $nick );
        }
        
        /**
         * Set a channel topic
         * 
         * @param string $channel
         * @param string $topic
         */
        public function setTopic( $channel, $topic )
        {
            $this->sendData( "TOPIC", $channel, ':' . $topic );
        }
    }
?>
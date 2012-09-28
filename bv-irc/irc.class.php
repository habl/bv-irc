<?php
    require_once( dirname( __FILE__ ) . "/command.class.php" );
    require_once( dirname( __FILE__ ) . "/event.class.php" );
    
    /**
     * Main IRC class
     *
     * @author Hans Blaauw <info@habl.nl>
     */
    class IRC extends event
    {
        /**
         * Client nickname
         * @var string
         */
        private $nick;
        
        /**
         * Client username
         * @var string 
         */
        private $user;
        
        /**
         * Client real name
         * @var string 
         */
        private $realName;
        
        /**
         * Server address
         * @var string 
         */
        private $server;
        
        /**
         * Server port
         * @var int 
         */
        private $port = 6667;
        
        /**
         * The running socket
         * @var resource 
         */
        private $conn;
        
        /**
         * Debugging enabled
         * @var bool 
         */
        private $debug = false;
                
        /**
         * Name of the server (not the host!)
         * 
         * @var string 
         */
        private $serverName; 
        
        /**
         * Are we logged on?
         * @var bool 
         */
        private $loggedOn;
        
        /**
         * Received data from server
         * @var string 
         */
        private $raw;
        
        /**
         * event class
         * @var object 
         */
        protected $event;
        
        /**
         * command class
         * @var object 
         */
        protected $command;
        
        /**
         * Set nickname of the client
         * 
         * @param string $nick
         */
        public function setNick( $nick )
        {
            $this->nick = $nick;
        }
        
        /**
         * Set username of the client
         * 
         * @param string $user
         */
        public function setUser( $user )
        {
            $this->user = $user;
        }
        
        /**
         * Set real name of the client
         * 
         * @param string $realName
         */
        public function setRealName( $realName )
        {
            $this->realName = $realName;
        }
        
        /**
         * Set the IRC server to connect to
         * 
         * @param string $server
         */
        public function setServer( $server )
        {
            $this->server = $server;
        }
        
        /**
         * Set the IRC server port
         * 
         * @param int $port
         */
        public function setPort( $port )
        {
            $this->port = $port;
        }
        
        /**
         * Turn on debugging
         * 
         * @param bool $val
         */
        public function setDebug( $val )
        {
            if ( $val )
                $this->debug = true;
            else
                $this->debug = false;
        }
        
        /**
         * Connect to the IRC server and start listening to events
         */
        public function connect()
        {
            $this->log( "Connecting to {$this->server}:{$this->port}" );
            
            // open the connection
            $this->conn = fsockopen( $this->server, $this->port );
            
            if ( $this->conn )
            {
                $this->log( "Connected." );
                
                // start processing the data
                $this->main();
            }
        }
        
        /**
         * This is the main loop which handles the events
         */
        protected function main()
        {
            // save incomming data and chop the leading white space
            $this->raw = chop( fgets( $this->conn ) );
            
            $this->debug( '<- ' . $this->raw );
            
            $data = explode( ' ', $this->raw );
            
            // first make sure we are connected and registered on the server
            if ( ! $this->loggedOn )
            {
                // if not logged on, wait with processing events till we can login
                if ( strstr( $this->raw, "Found your hostname" ) )
                {
                    // save the servername so we can use it to identify server notices
                    $this->serverName = substr( $data[0], 1 );
                    
                    // start login
                    $this->login();
                }
            }
            else
            {
                $this->observe( $data );
            }
            
            // if we are still connecting continue monitoring the data
            if ( $this->conn )
            {
                $this->main();
            }
        }

        
        /**
         * Sent data to the IRC server
         * 
         * @param string $cmd the command to perform
         * @param string $destination where to sent the command to
         * @param string $parameters additional parameters (optiona;)
         * @return boolean
         */
        protected function sendData( $cmd, $destination, $parameters = "" )
        {
            if ( $this->conn && isset( $destination ) )
            {
                // build an irc command
                $serverString = $cmd . " " . $destination;
                
                if ( ! empty( $parameters ) )
                    $serverString .= " " . $parameters;
                    
                $this->debug( '-> ' . $serverString );
                    
                fwrite( $this->conn, $serverString . "\n" );
                
                return true;
            }
            
            return false;
        }
        
        /**
         * Register user on the IRC server
         */
        protected function login( )
        {
            $this->sendData( 'USER', $this->nick, $this->nick . ' ' . $this->user . ' : '.$this->realName );
         
            $this->sendData( 'NICK', $this->nick );
            
            $this->loggedOn = true;
        }
        
        /**
         * Log a message to the console
         * 
         * @param string $message
         */
        protected function log( $message )
        {
            printf( "%s\n", $message );
        }
        
        /**
         * Debugging messages
         * 
         * @param string $message
         */
        protected function debug( $message )
        {
            if ( $this->debug )
            {
                printf( "%s\n", $message );
            }
        }
        
        /**
         * return the client nick
         * 
         * @return string|bool nickname on succes or false on fail
         */
        public function getNick()
        {
            if ( isset( $this->nick ) )
            {
                return $this->nick;
            }
            
            return false;
        }
        
        /**
         * get raw received data
         * @return string|bool data on succes or false on fail
         */
        public function getRaw()
        {
            if ( isset( $this->raw ) )
            {
                return $this->raw;
            }
            
            return false;
        }
       
    }
    
?>
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
        private $_nick;
        
        /**
         * Client username
         * @var string 
         */
        private $_user;
        
        /**
         * Client real name
         * @var string 
         */
        private $_realName;
        
        /**
         * Server address
         * @var string 
         */
        private $_server;
        
        /**
         * Server port
         * @var int 
         */
        private $_port = 6667;
        
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
        private $_serverName; 
        
        /**
         * Are we logged on?
         * @var bool 
         */
        private $_loggedOn;
        
        /**
         * Received data from server
         * @var string 
         */
        private $_raw;
        
        /**
         * Auto reconnect on disconnect
         * 
         * @var bool 
         */
        private $_autoReconnect = false;
        
        /**
         * Reconnecting delay
         * @var int seconds
         */
        private $_delay;
        
       
        /**
         * Connect to the IRC server and start listening to events
         */
        public function connect()
        {
            $this->log( "Connecting to {$this->_server}:{$this->_port}" );
            
            // open the connection
            $this->conn = fsockopen( $this->_server, $this->_port, $errno, $errstr, 10 );
            
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
            $this->_raw = chop( fgets( $this->conn ) );
            
            $this->debug( '<- ' . $this->_raw );
            
            $data = explode( ' ', $this->_raw );
            
            // first make sure we are connected and registered on the server
            if ( ! $this->_loggedOn )
            {
                // if not logged on, wait with processing events till we can login
                if ( strstr( $this->_raw, "Found your hostname" ) )
                {
                    // save the servername so we can use it to identify server notices
                    $this->_serverName = substr( $data[0], 1 );
                    
                    // start login
                    $this->login();
                }
            }
            else
            {
                $this->observe( $data );
            }
            
            // if we are still connecting continue monitoring the data
            if ( ! feof( $this->conn ) )
            {
                $this->main();
            }
            else
            {
                // we are disconnected so remove the socket
                unset( $this->conn );
                
                // reconnect if required
                if ( $this->_autoReconnect )
                {
                    $this->reconnect();
                }
                else
                {
                    $this->log( "Disconnected from server." );
                    
                    exit;
                }
            }
        }

        /**
         * Register user on the IRC server
         */
        protected function login( )
        {
            $this->sendData( 'USER', $this->_nick, $this->_nick . ' ' . $this->_user . ' : ' . $this->_realName );
         
            $this->sendData( 'NICK', $this->_nick );
            
            $this->_loggedOn = true;
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
         *  Reconnect to server
         *  
         *  @param string $quitReason
         */
        public function reconnect( $quitReason = "Reconnecting" )
        {
            // disconnect first if still connected
            if ( isset( $this->conn ) )
            {
                if ( ! feof( $this->conn ) )
                    $this->disconnect( $quitReason );
                
                unset( $conn );
            }
            
            // reset some runtime variables
            $this->_loggedOn = false;
            $this->_serverName = "";
            
            sleep( $this->_delay );
            
            // connect again
            $this->connect();
        }
        
       
        /**
         * Log a message to the console
         * 
         * @param string $message
         */
        public function log( $message )
        {
            printf( "%s\n", $message );
        }
        
        /**
         * Debugging messages
         * 
         * @param string $message
         */
        public function debug( $message )
        {
            if ( $this->debug && ! empty( $message ) )
            {
                printf( "%s\n", $message );
            }
        }
        
        /**
         * Set nickname of the client
         * 
         * @param string $nick
         */
        public function setNick( $nick )
        {
            $this->_nick = $nick;
        }
        
        /**
         * Set username of the client
         * 
         * @param string $user
         */
        public function setUser( $user )
        {
            $this->_user = $user;
        }
        
        /**
         * Set real name of the client
         * 
         * @param string $realName
         */
        public function setRealName( $realName )
        {
            $this->_realName = $realName;
        }
        
        /**
         * Set the IRC server to connect to
         * 
         * @param string $server
         */
        public function setServer( $server )
        {
            $this->_server = $server;
        }
        
        /**
         * Set the IRC server port
         * 
         * @param int $port
         */
        public function setPort( $port )
        {
            $this->_port = $port;
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
         * Turn on/off autoreconnect on disconnect
         * 
         * @param bool $val
         * @param int $delay the time to wait with reconnecting in seconds
         */
        public function setAutoReconnect( $val, $delay = 10 )
        {
            if ( $val )
                $this->_autoReconnect = true;
            else
                $this->_autoReconnect = false;
            
            $this->_delay = $delay;
        }
        /**
         * return the client nick
         * 
         * @return string|bool nickname on succes or false on fail
         */
        public function getNick()
        {
            if ( isset( $this->_nick ) )
            {
                return $this->_nick;
            }
            
            return false;
        }
        
        /**
         * get raw received data
         * @return string|bool data on succes or false on fail
         */
        public function getRaw()
        {
            if ( isset( $this->_raw ) )
            {
                return $this->_raw;
            }
            
            return false;
        }
       
    }
    
?>
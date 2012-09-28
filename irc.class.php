<?php
	class IRC
	{
		private $nick;
		private $user;
		private $realName;
		private $server;
		private $port = 6667;
		private $conn;
		private $debug = false;
		private $serverName;
		private $loggedOn;
		private $raw;
		
		public function __construct( )
		{
			
		}
		
		public function setNick( $nick )
		{
			$this->nick = $nick;
		}
		
		public function setUser( $user )
		{
			$this->user = $user;
		}
		
		public function setRealName( $realName )
		{
			$this->realName = $realName;
		}
		
		public function setServer( $server )
		{
			$this->server = $server;
		}
		
		public function setPort( $port )
		{
			$this->port = $port;
		}
		
		public function setDebug( $val )
		{
			if ( $val )
				$this->debug = true;
			else
				$this->debug = false;
		}
		
		public function connect()
		{
			$this->log( "Connecting to {$this->server}:{$this->port}" );
			
			$this->conn = fsockopen( $this->server, $this->port );
			if ( $this->conn )
			{
				$this->log( "Connected." );
				
				//$this->login();
				$this->main();
			}
		}
		
		protected function main()
		{
			// save incomming data and chop the leading white space
			$this->raw = chop( fgets( $this->conn ) );
			
			$this->debug( '<- ' . $this->raw );
			
			$data = explode( ' ', $this->raw );
			
			// first make sure we are connected and registered on the server
			if ( ! $this->loggedOn )
			{
				if ( strstr( $this->raw, "Found your hostname" ) )
				{
					$this->serverName = substr( $data[0], 1 );
					
					$this->login();
				}
			}
			else
			{
				$this->events( $data );
			}
			$this->main();
		}
		
		protected function events( $data )
		{
			if ( substr( $data[0], 0, 1 ) == ":" )
			{
				$from = substr( $data[0], 1 );
				array_shift( $data );
			}
			
			switch ( $data[0] )
			{
				case "PING":
					$this->sendData( 'PONG', $data[1] );
					break;
				case "PRIVMSG":
					// if channel onPublic, if nick onPrivate
					break;
				case "JOIN":
					$this->runHandler( 'onJoin' );
					break;
				case "NOTICE":
					// onnotice
					break;
				// end of motd, usefull for onconnect
				case "376":
					$this->runHandler( 'onConnect' );
					break;
			}
		}
		
		protected function sendData( $cmd, $destination, $parameters = "" )
		{
			if ( $this->conn && isset( $destination ) )
			{
				$serverString = $cmd . " " . $destination;
				
				if ( isset( $parameters ) )
					$serverString .= " " . $parameters;
					
				$this->debug( '-> ' . $serverString );
					
				fwrite( $this->conn, $serverString . "\n" );
				
				return true;
			}
			
			return false;
		}
		
		/**
		 * running an event handler
		 * @TODO checking if the methods are callable
		 * @TODO maybe adding dynamic handlers? $this->registerHandler( 'event', 'callback' );
		 * 
		 */
		private function runHandler( $handler )
		{
			if ( method_exists( $this, $handler )  )
			{
				$this->$handler();
			}
			elseif ( function_exists( $handler ) )
			{
				$handler();
			}
			else
			{
				$this->debug( "! No method found for " . $handler );
			}
		}
		
		protected function login( )
		{
			$this->sendData( 'USER', $this->nick, $this->nick . ' ' . $this->user . ' : '.$this->realName );
		 
			$this->sendData( 'NICK', $this->nick );
			
			$this->loggedOn = true;
		}
		
		protected function log( $message )
		{
			printf( "%s\n", $message );
		}
		
		protected function debug( $message )
		{
			if ( $this->debug )
			{
				printf( "%s\n", $message );
			}
		}
		
		protected function joinChannel( $channel )
		{
			if ( substr( $channel, 0, 1 ) == "#" )
			{
				$this->sendData( 'JOIN', $channel );
			}
		}
	}
?>
<?php
	require_once( "irc.class.php" );
	
	class bot extends IRC
	{
		public function __construct()
		{
		}
		
		protected function onConnect()
		{
			$this->joinChannel( '#testerdetest' );
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
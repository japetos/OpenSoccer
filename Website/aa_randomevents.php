<?php if (!isset($_GET['mode'])) { include 'zzserver.php'; } ?>
<?php

//Zufallszahl zwischen 1:1000 ermitteln
function getRandomValue() {
	
	$random = NULL;
	
	mt_srand((double)microtime()*1000000);
	$random = mt_rand(1,1000);
	
	return = $random;
}

//Zufallsverein mit 'user' ermitteln / keine Computer Teams
function getRandomUserTeam() {
	
	$result = mysql_query("SELECT team FROM ".$prefix."users WHERE team>='1' ORDER BY RAND() LIMIT 1");
	$team = mysql_fetch_array($result);
	mysql_free_result($result);
	
	$team_id = $team['team'];
	
	return $team_id;	
}

//Zufallsevent aus DB laden
function getRandomEvent() {
	
	$result = mysql_query("SELECT * FROM ".$prefix."randomevent ORDER BY RAND() LIMIT 1");
	$event = mysql_fetch_array($result);
	mysql_free_result($result);
	
	$event_data = $event;
	
	return $team_data;	
}

//Zufälligen Spiele ermitteln
function getRandomPlayerByTeamId($team_id) {
	
	$result = mysql_query("SELECT * FROM ".$prefix."spieler WHERE team='$team_id' ORDER BY RAND() LIMIT 1");
	$player = mysql_fetch_array($result);
	mysql_free_result($result);
	
	$player_data = $player;
	
	return $player_data;
	
}

//Effekt auf Spieler
function effectOnPlayer($team_id, $event_data) {
	
	$effect_key = $event_data['effect'];

	$player = getRandomPlayerByTeamId($team_id);
	
	$player_name = $player['vorname'] ." ". $player['nachname'];
	$player_id = $player['id'];
	$player_string = "<a href=\"spieler.php?id=$player_id\">$player_name</a>";
	
	//effekt Wert /10 teilen
	$effect_value = $event_data['effect_skillchange']/10;
	
	//message für protokoll
	$tmp_message = str_replace("{playername}", $player_string, $event_data['message']);
	$event_message = str_replace("{value}", $effect_value, $tmp_message);
	
	//evtl noch typ nachtragen, mir unbekannt, momentan = "Sanktion"
	$sql7 = "INSERT INTO ".$prefix."protokoll (id,team,text,typ,zeit) VALUES ('', ".$team_id.", ".$event_message.", 'Spieler', ".$time.")";
	if (mysql_errno()) { 
		die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query7 </b><BR>". $sql7);
	} else {
		$mysql_query($sql7);
	}
	
	//Verletzung updaten
	$sql6 = "UPDATE ".$prefix."spieler SET ".$effect_key."=".$effect_key."+(".$effect_value.") WHERE id='".$player_id."'";
	if (mysql_errno()) { 
		die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query8 </b><BR>". $sql8);
	} else {
		$mysql_query($sql8);
	}
	
}


$random = NULL;
$random = getRandomValue();

$team_id = NULL;
$team_id = getRandomUserTeam();

$event_data = NULL;
$team_data = getRandomEvent();


//prüfen ob zufallszahl==event weight und ob verein mit user in DB vorhanden
if($random==$event_data['weight'] && $team_id>='1') {
	
	$time = time();
	
	//Handelt es sich um das Event: money?
	if($event_data['effect']==="money") {
		
		//evtl noch typ nachtragen, mir unbekannt, momentan = "Sanktion"
		$sql0 = "INSERT INTO ".$prefix."protokoll (id,team,text,typ,zeit) VALUES ('', ".$team_id.", ".$event_data['message'].", 'Sanktion', ".$time.")";
		if (mysql_errno()) { 
			die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query0 </b><BR>". $sql0);
		} else {
			$mysql_query($sql0);
		}
		
		//Update auf vereinskonto
		$sql1 = "UPDATE ".$prefix."teams SET konto=konto+(".$event_data['effect_money_amount'].") WHERE id='".$team_id."'";
		if (mysql_errno()) { 
			die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query1 </b><BR>". $sql1);
		} else {
			$mysql_query($sql1);
		}
		
		//Buchung eintragen in man_buchungen
		$sql2 = "INSERT INTO ".$prefix."buchungen (id,team,verwendungszweck,betrag,zeit) VALUES ('', ".$team_id.", ".$event_data['message'].", '".$event_data['effect_money_amount']."', ".$time.")";
		if (mysql_errno()) { 
			die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query2 </b><BR>". $sql2);
		} else {
			$mysql_query($sql2);
		}

	
	//Handelt es sich um das Event: fans?
	// ich gehe davon aus dass das fanaufkommen eine numerisch zahl ist 
	// und somit hier prozentual aufgebessert/verschlechtert wird
	} else if($event_data['effect']="fans") {
		
		//evtl noch typ nachtragen, mir unbekannt, momentan = "Sanktion"
		$sql3 = "INSERT INTO ".$prefix."protokoll (id,team,text,typ,zeit) VALUES ('', ".$team_id.", ".$event_data['message'].", 'Sanktion', ".$time.")";
		if (mysql_errno()) { 
			die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query3 </b><BR>". $sql3);
		} else {
			$mysql_query($sql3);
		}
		
		//Update auf teams fanaufkommen
		$sql4 = "UPDATE ".$prefix."teams SET fanaufkommen=fanaufkommen*(".1+($event_data['effect_skillchange']/100).") WHERE id='".$team_id."'";
		if (mysql_errno()) { 
			die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query4 </b><BR>". $sql4);
		} else {
			$mysql_query($sql4);
		}
		
		
	//Handelt es sich um das event: player?
	} else if($event_data['effect']=="verletzung") {
		
		$player = getRandomPlayerByTeamId($team_id);
		
		$player_name = $player['vorname'] ." ". $player['nachname'];
		$player_id = $player['id'];
		$player_string = "<a href=\"spieler.php?id=$player_id\">$player_name</a>";
		
		$event_message = str_replace("{value}", $player_string, $event_data['message']);
		
		//evtl noch typ nachtragen, mir unbekannt, momentan = "Sanktion"
		$sql5 = "INSERT INTO ".$prefix."protokoll (id,team,text,typ,zeit) VALUES ('', ".$team_id.", ".$event_message.", 'Spieler', ".$time.")";
		if (mysql_errno()) { 
			die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query5 </b><BR>". $sql5);
		} else {
			$mysql_query($sql5);
		}
		
		//Verletzung updaten
		$sql6 = "UPDATE ".$prefix."spieler SET verletzung=verletzung+".$event_data['effect_skillchange']." WHERE id='".$player_id."'";
		if (mysql_errno()) { 
			die("MySQL sagt:<BR>". mysql_error() ."<BR><b>Query6 </b><BR>". $sql6);
		} else {
			$mysql_query($sql6);
		}
		
	//Event wirkt sicvh auf Staerke, Frische oder Moral aus	
	} else if($event_data['effect']=="staerke" || $event_data['effect']=="frische" || $event_data['effect']=="moral") {
		
		$player = getRandomPlayerByTeamId($team_id);
		effectOnPlayer($team_id, $event_data);
		
	}
	
} //-- ENDE

?>

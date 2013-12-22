<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript' src="lib/js/GCT.js"></script>
<script type='text/javascript' src="lib/js/wz_tooltip.js"></script>

<div id='idGTC' ></div>

<?php
 echo "<script type='text/javascript'>

	var gct = new GCT( 'idGTC' );

	gct.addColumn('number', '".tr('ranking')."', 'width:30px; text-align: left; font-weight: bold; ');
	gct.addColumn('number', '".tr('caches')."', 'width:50px; text-align: left; font-weight: bold; ');  
	gct.addColumn('string', '".tr('user')."', 'width:50px; text-align: left; font-weight: bold; ' ); 
 	gct.addColumn('string', '".tr('descriptions')."', 'font-family: curier new; font-style: italic;  ');		
 	gct.addColumn('string', 'UserName' );
	gct.hideColumns( [4] );
			
 </script>";

 

require_once('./lib/db.php');

$sRok = "";
$sMc = "";
$sCondition = "";
$nIsCondition = 0;
$nMyRanking = 0;


if ( isset( $_REQUEST[ 'Rok' ]) )
	$sRok =  $_REQUEST[ 'Rok' ];

if ( isset( $_REQUEST[ 'Mc' ]) )
	$sMc =  $_REQUEST[ 'Mc' ];



if ( $sRok <> "" and $sMc <> "" )
{
	$sData_od = $sRok.'-'.$sMc.'-'.'01';
	
	$dDate = new DateTime( $sData_od );
	$dDate->add( new DateInterval('P1M') );
	$nIsCondition = 1;
}

if ( $sRok <> "" and $sMc == "" )
{
	$sData_od = $sRok.'-01-01';

	$dDate = new DateTime( $sData_od );
	$dDate->add( new DateInterval('P1Y') );
	$nIsCondition = 1;
}


if ( $nIsCondition )
{
	$sData_do = $dDate->format( 'Y-m-d');	
	$sCondition = "and date >='" .$sData_od ."' and date < '".$sData_do."'";	
}

//SantaClause
$dbc = new dataBase();
$query = "SELECT COUNT(*) count FROM `caches` WHERE `status`=1";
$dbc->multiVariableQuery($query);
$record = $dbc->dbResultFetch();
$nNrActiveCaches = $record[ "count"];
$sUserStyle = ' style="color:green" ';
$sProfil = 'Jest od z nami od zawsze ...';
$sUsername = '<a Św.Mikołaj style="color:red" href="viewprofile.php?userid=59241" onmouseover="Tip(\\\''.$sProfil.'\\\')" onmouseout="UnTip()"  >Św. Mikołaj Santa Claus</a>';
$sWellBehaved = tr(well_behaved);

unset( $dbc );



$dbc = new dataBase();

$query = 
		"SELECT COUNT(*) count, u.username username, UPPER(u.username) UUN, u.user_id user_id, 
		DATE(u.date_created) date_created, u.description description
		
		FROM 
		cache_logs cl
		join caches c on c.cache_id = cl.cache_id
		join user u on cl.user_id = u.user_id
		
		WHERE cl.deleted=0 AND cl.type=1 "
		
		. $sCondition .		
		
		"GROUP BY u.user_id   		
		ORDER BY count DESC, u.username ASC";

		
$dbc->multiVariableQuery($query);

echo "<script type='text/javascript'>";

//SantaClause
$nRanking = 0;
$nRanking++;
echo "
gct.addEmptyRow();
gct.addToLastRow( 0, 1 );
gct.addToLastRow( 1, $nNrActiveCaches );
gct.addToLastRow( 2, '$sUsername' );
gct.addToLastRow( 3, '$sWellBehaved' );
gct.addToLastRow( 4, 'Św. Mikołaj, Santa Clause' );
";


$nRanking = 0;
$sOpis = "";
$nOldCount = -1;
$nPos = 0;
$nMyRanking = 0;
$nMyRealPos = 0;





while ( $record = $dbc->dbResultFetch() )
{	
	if ( $record[ "description" ] <> "" )
	{
		$sOpis = $record[ "description" ];
		
		$sOpis = str_replace("\r\n", " ",$sOpis);
		$sOpis = str_replace("\n", " ",$sOpis);
		$sOpis = str_replace("'", "-",$sOpis);
		$sOpis = str_replace("\"", " ",$sOpis);		
	}
	else
		$sOpis = "";
	
	$sOpis = "<br>".$sOpis;
	
	
	$sProfil = "<b>Zarejestrowany od:</b> ".$record[ "date_created" ];
		

	$nCount = $record[ "count" ];
	$sUUN = $record[ "UUN" ];
	$sDateCr = $record[ "date_created" ];

	if ( $nRanking <3 )
		$sUserStyle = ' style="color:green" ';
	else
		$sUserStyle = '';
	 
	$sUsername = '<a '.$record[ "username" ].$sUserStyle.' href="viewprofile.php?userid='.$record["user_id"].'" onmouseover="Tip(\\\''.$sProfil.'\\\')" onmouseout="UnTip()"  >'.$record[ "username" ].'</a>';
	//$sUsername = ''.$record[ "username" ].'';

	
	if ( $nCount != $nOldCount )
	{				
		$nRanking++;
		$nOldCount = $nCount; 
	}
	
	$nPos++;
	
	echo "
			gct.addEmptyRow();
			gct.addToLastRow( 0, $nRanking );
			gct.addToLastRow( 1, $nCount );
			gct.addToLastRow( 2, '$sUsername' );
			gct.addToLastRow( 3, '$sOpis' );
			gct.addToLastRow( 4, '$sUUN' );
		";
	
	if ( $usr['userid'] == $record[ 'user_id'] )
	{
		$nMyRanking = $nRanking;
		$nMyRealPos = $nPos-1;
		//echo " gct.addToLastRow( 3, '<span style=\"color:red\">$sUUN</span>' );";
	}


	
	
}


echo "gct.drawTable();";

echo "document.Position.Ranking.value = '".$nMyRanking." / ".$nRanking."';";
echo "document.Position.RealPosOfTable.value = '".$nMyRealPos."';";
echo "</script>";

?>

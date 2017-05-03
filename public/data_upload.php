<?php

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    include_once 'config.php';
    include_once 'include/db_functions.php'; 
    
    $dbh = db_connect();

$orgID = 10;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Data upload page</title>
<style type="text/css">
body {
	background: #E3F4FC;
	font: normal 14px/30px Helvetica, Arial, sans-serif;
	color: #2b2b2b;
}
a {
	color:#898989;
	font-size:14px;
	font-weight:bold;
	text-decoration:none;
}
a:hover {
	color:#CC0033;
}

h1 {
	font: bold 14px Helvetica, Arial, sans-serif;
	color: #CC0033;
}
h2 {
	font: bold 14px Helvetica, Arial, sans-serif;
	color: #898989;
}
#container {
	background: #CCC;
	margin: 100px auto;
	width: 945px;
}
#form 			{padding: 20px 150px;}
#form input     {margin-bottom: 20px;}
</style>
</head>
<body>
<div id="container">
<div id="form">

<?php

//Upload File
if (isset($_POST['submit'])) {
    
    $what_was_uploaded = $_POST['data_type'];
    $eventID = $_POST['eventID'];
    $ticketID = check_item_in_table_for_value($dbh, "eventID", "event-tickets", $eventID, 0, "ticketID");
    
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        echo("<h1>" . "File ". $_FILES['filename']['name'] ." uploaded successfully." . "</h1>");
        echo("<h2>Displaying contents for event: $eventID and ticket: $ticketID</h2>");
        echo("<a href='/data_upload.php'>Upload Another File</a><br />\n");
        //readfile($_FILES['filename']['tmp_name']);
    }
	// when really uploading, have OrgID context of relevant organization for Org-Person table entry
	
	$row = 0;
	//Import uploaded file to Database
	$handle = fopen($_FILES['filename']['tmp_name'], "r");

	ini_set('max_execution_time', 0);
	
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$row++;
		$data = array_map("utf8_encode", $data);  // maybe?
		$num = count($data);
		switch ($what_was_uploaded) {
                    case "mbrdata":
                        // 25 fields in Member Detail csv file
                        // zip fix formula: =IF(LEN(M2)=4,CONCATENATE("0",M2),IF(LEN(M2)=8,CONCATENATE("0",LEFT(M2,4),"-",RIGHT(M2,4)),M2))

                        $pmiid 		= trim(ucwords($data[0]));
                        $prefix 	= trim(ucwords($data[1]));
                        $first 		= trim(ucwords($data[2]));
                        $middle 	= trim(ucwords($data[3]));
                        $last 		= trim(ucwords($data[4]));
                        $suffix 	= trim(ucwords($data[5]));
                        $title 		= trim(ucwords($data[6]));
                        $comp 		= trim(ucwords($data[7]));
                        $prefADType     = trim(ucwords($data[8]));
                        $prefAD 	= trim(ucwords($data[9]));
                        $prefCity 	= trim(ucwords($data[10]));
                        $prefST 	= trim(ucwords($data[11]));
                        $prefZip 	= trim(ucwords($data[12]));
                        if(strlen($prefZip)==4) {
                                $prefZip = "0" . $prefZip;
                        } elseif (strlen($prefZip)==8) {
                                $r2 = substr($prefZip, -4);
                                $l2 = substr($prefZip, 4);
                                $prefZip = "0" . $l2 . "-" . $r2;
                        }
                        $prefCountry = trim(ucwords($data[13]));
                        $homePhone 	= trim(ucwords($data[14]));
                        $workPhone 	= trim(ucwords($data[15])); if($data[16]) {$workPhone .= " x".trim(ucwords($data[16]));}
                        $cellPhone 	= trim(ucwords($data[17]));
                        $email1 	= trim(strtolower($data[18]));
                        $email2 	= trim(strtolower($data[19]));
                        $OrgStat2 	= trim(ucwords($data[20]));  // PMI Classification Type
                        $relDate1 	= $data[21]; 
                        if (!empty($relDate1)) {
                                list($d, $m, $y) = array_pad(explode("/", $relDate1, 3), 3, null);  
                                $relDate1 = "$y-$m-$d"; 
                        }
                        $relDate2 	= $data[22]; 
                        if (!empty($relDate2)) {
                                list($d, $m, $y) = array_pad(explode("/", $relDate2, 3), 3, null); 
                                $relDate2 = "$y-$m-$d"; 
                        }
                        $relDate3 	= $data[23]; 
                        if (!empty($relDate3)) {
                                list($d, $m, $y) = array_pad(explode("/", $relDate3, 3), 3, null); 
                                $relDate3 = "$y-$m-$d"; 
                        }
                        $relDate4 	= $data[24]; 
                        if (!empty($relDate4)) {
                                list($d, $m, $y) = array_pad(explode("/", $relDate4, 3), 3, null); 
                                $relDate4 = "$y-$m-$d"; 
                        }

                        // before inserting, consider various checks on email and/or phone

                        if(empty($email1) && !empty($email2)) {
                                $email1 = $email2;
                                $email2 = '';
                        }

                        ($email1 == $email2) ? $email2 = '': 1;

                        $toomany = check_table_for_value($dbh, "person-email", "emailADDR", $email1, 1);

                        if ($pmiid <> "PMI ID" and $toomany < 1) {
                            $sql = "INSERT INTO `person` (prefix, firstName, midName, lastName, suffix, login, title, compName) VALUES (:prefix, :firstName, :midName, :lastName, :suffix, :login, :title, :compName)";
                            try {
                                $query = $dbh->prepare($sql);
                                empty($prefix) ? $query->bindValue(':prefix',  null, PDO::PARAM_INT) : $query->bindParam(':prefix', $prefix, PDO::PARAM_STR);
                                empty($first) ? $query->bindValue(':firstName',  null, PDO::PARAM_INT) :$query->bindParam(':firstName', $first, PDO::PARAM_STR);
                                empty($middle) ? $query->bindValue(':midName',  null, PDO::PARAM_INT) :$query->bindParam(':midName', $middle, PDO::PARAM_STR);
                                empty($last) ? $query->bindValue(':lastName',  null, PDO::PARAM_INT) :$query->bindParam(':lastName', $last, PDO::PARAM_STR);
                                empty($prefix) ? $query->bindValue(':suffix',  null, PDO::PARAM_INT) :$query->bindParam(':suffix', $suffix, PDO::PARAM_STR);
                                empty($email1) ? $query->bindValue(':login',  null, PDO::PARAM_INT) :$query->bindParam(':login', $email1, PDO::PARAM_STR);
                                empty($title) ? $query->bindValue(':title',  null, PDO::PARAM_INT) :$query->bindParam(':title', $title, PDO::PARAM_STR);
                                empty($comp) ? $query->bindValue(':compName',  null, PDO::PARAM_INT) :$query->bindParam(':compName', $comp, PDO::PARAM_STR);
                                $query->execute();
                            } catch (PDOException $e){
                                    echo $e->getMessage();
                            }

                            // then insert org-person table

                            $personID = $dbh->lastInsertId();
                            //for ($c=0; $c < $num; $c++) { echo $data[$c] . ", "; } echo ("<br />\n");
                            //echo($sql . " & " . $personID . "<p>\n");

                            $sql = "INSERT INTO `org-person` (orgID, personID, OrgStat1, OrgStat2, RelDate1, RelDate2, RelDate3, RelDate4) VALUES (:orgID, :personID, :OrgStat1, :OrgStat2, :RelDate1, :RelDate2, :RelDate3, :RelDate4)";
                            try {
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':orgID', $orgID, PDO::PARAM_INT);
                                $query->bindParam(':personID', $personID, PDO::PARAM_INT);
                                empty($pmiid) ? $query->bindValue(':OrgStat1',  null, PDO::PARAM_INT) :$query->bindParam(':OrgStat1', $pmiid, PDO::PARAM_INT);
                                empty($OrgStat2) ? $query->bindValue(':OrgStat2',  null, PDO::PARAM_INT) :$query->bindParam(':OrgStat2', $OrgStat2, PDO::PARAM_STR);
                                empty($relDate1) ? $query->bindValue(':RelDate1',  null, PDO::PARAM_INT) :$query->bindParam(':RelDate1', $relDate1, PDO::PARAM_STR);
                                empty($relDate2) ? $query->bindValue(':RelDate2',  null, PDO::PARAM_INT) :$query->bindParam(':RelDate2', $relDate2, PDO::PARAM_STR);
                                empty($relDate3) ? $query->bindValue(':RelDate3',  null, PDO::PARAM_INT) :$query->bindParam(':RelDate3', $relDate3, PDO::PARAM_STR);
                                empty($relDate4) ? $query->bindValue(':RelDate4',  null, PDO::PARAM_INT) :$query->bindParam(':RelDate4', $relDate4, PDO::PARAM_STR);
                                $query->execute();
                            } catch (PDOException $e){
                                echo $e->getMessage();
                            }

                            //echo("org-person: " . $sql . " & " . $personID . "<p>\n");
                            // then insert person-address table

                            $sql = "INSERT INTO `person-address` (personID, addrTYPE, addr1, city, state, zip, country) 
                                    VALUES (:personID, :addrTYPE, :addr1, :city, :state, :zip, :country)";
                            try {
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':personID', $personID, PDO::PARAM_INT);
                                empty($prefADType) ? $query->bindValue(':addrTYPE',  null, PDO::PARAM_INT) :$query->bindParam(':addrTYPE', $prefADType, PDO::PARAM_STR);
                                empty($prefAD) ? $query->bindValue(':addr1',  null, PDO::PARAM_INT) :$query->bindParam(':addr1', $prefAD, PDO::PARAM_STR);
                                empty($prefCity) ? $query->bindValue(':city',  null, PDO::PARAM_INT) :$query->bindParam(':city', $prefCity, PDO::PARAM_STR);
                                empty($prefST) ? $query->bindValue(':state',  null, PDO::PARAM_INT) :$query->bindParam(':state', $prefST, PDO::PARAM_STR);
                                empty($prefZip) ? $query->bindValue(':zip',  null, PDO::PARAM_INT) :$query->bindParam(':zip', $prefZip, PDO::PARAM_STR);
                                empty($prefCountry) ? $query->bindValue(':country',  null, PDO::PARAM_INT) :$query->bindParam(':country', $prefCountry, PDO::PARAM_STR);
                                $query->execute();
                            } catch (PDOException $e){
                                echo $e->getMessage();
                            }

                            //echo("person-address: " . $sql . " & " . $personID . "<p>\n");
                            // then insert person-email table (Primary Email)

                            if ($email1 <> "") {
                                //$sql = "INSERT INTO `person-email` (personID, isPrimary, emailADDR, createDate, updateDate) VALUES (:personID, 1, :emailADDR, NOW(), NOW())";
                                $sql = "INSERT INTO `person-email` (personID, isPrimary, emailADDR) VALUES (?, 1, ?)";
                                try {
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(1, $personID, PDO::PARAM_INT);
                                    $query->bindParam(2, $email1, PDO::PARAM_STR);
                                    //$query->execute(array(':personID' => $personID, ':isPrimary' => '1', ':emailADDR' => $email1));
                                    $query->execute();
                                } catch (PDOException $e){
                                    echo $e->getMessage();
                                }
                            }
                            // then insert person-email table (Alternate Email) if it exists

                            if ($email2 <> "" and strtolower($email2) <> strtolower($email1)) {
                                $sql = "INSERT INTO `person-email` (personID, emailADDR) VALUES (?, ?)";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(1, $personID, PDO::PARAM_INT);
                                $query->bindParam(2, $email2, PDO::PARAM_STR);
                                $query->execute();
                            }
                        }
                        break;
	// EVENT STUFF
			case "evtdata":;
                            if($row==1){
                                //debug(1, "Dealing with an event file with $num variables, yay!<br>");

                                for($i=1;$i<$num;$i++){  # skipping 0 because it's 'Attended'
                                    switch(true){
                                        case (preg_match("/salutation/i", $data[$i])):		$map[2]=$i; debug(8,"map2: $i; sal<br>"); break;
                                        case (preg_match("/^first name$/i", $data[$i])):	$map[3]=$i; debug(8,"map3: $i; fn<br>"); break;
                                        case (preg_match("/last name/i", $data[$i])):		$map[4]=$i; debug(8,"map4: $i; ln<br>"); break;
                                        case (preg_match("/suffix/i", $data[$i])):		$map[5]=$i; debug(8,"map5: $i; sfx<br>"); break;
                                        case (preg_match("/email/i", $data[$i])):		$map[6]=$i; debug(8,"map6: $i; em1<br>"); break;
                                        case (preg_match("/^ticket$/i", $data[$i])):		$map[7]=$i; debug(8,"map7: $i; tkt<br>"); break;
                                        case (preg_match("/seat/i", $data[$i])):		$map[8]=$i; debug(8,"map8: $i; st<br>"); break;
                                        case (preg_match("/registered on/i", $data[$i])):	$map[9]=$i; debug(8,"map9: $i; reg<br>"); break;
                                        case (preg_match("/registered by/i", $data[$i])):	$map[10]=$i; debug(8,"map10: $i; rby<br>"); break;
                                        case (preg_match("/^payment$/i", $data[$i])):		$map[11]=$i; debug(8,"map11: $i; pay<br>"); break;
                                        case (preg_match("/confirmation co/i", $data[$i])):	$map[12]=$i; debug(8,"map12: $i; cfm<br>"); break;
                                        case (preg_match("/payment rec/i", $data[$i])):		$map[13]=$i; debug(8,"map13: $i; pyr<br>"); break;
                                        case (preg_match("/^status$/i", $data[$i])):		$map[14]=$i; debug(8,"map14: $i; stat<br>"); break;
                                        case (preg_match("/cancelled on/i", $data[$i])):	$map[15]=$i; debug(8,"map15: $i; canc<br>"); break;
                                        case (preg_match("/^cost$/i", $data[$i])):		$map[16]=$i; debug(8,"map16: $i; cost<br>"); break;
                                        case (preg_match("/cc charge/i", $data[$i])):		$map[17]=$i; debug(8,"map17: $i; ccc<br>"); break;
                                        case (preg_match("/handling/i", $data[$i])):		$map[18]=$i; debug(8,"map18: $i; hnd<br>"); break;
                                        case (preg_match("/your amount/i", $data[$i])):		$map[19]=$i; debug(8,"map19: $i; amt<br>"); break;
                                        case (preg_match("/disc code/i", $data[$i])):		$map[20]=$i; debug(8,"map20: $i; dcc<br>"); break;
                                        case (preg_match("/disc desc/i", $data[$i])):		$map[21]=$i; debug(8,"map21: $i; dd<br>"); break;
                                        case (preg_match("/disc amount/i", $data[$i])):		$map[22]=$i; debug(8,"map22: $i; damt<br>"); break;
                                        case (preg_match("/pmi (number|membership)/i", $data[$i])):		$map[23]=$i; debug(8,"map23 $i; pmi<br>"); break;
                                        case (preg_match("/preferred first/i", $data[$i])): 	$map[24]=$i; debug(8,"map24: $i; pfn<br>"); break;
                                        case (preg_match("/topic/i", $data[$i])):		$map[25]=$i; debug(8,"map25: $i; top<br>"); break;
                                        case (preg_match("/pmp\?/i", $data[$i])):		$map[26]=$i; debug(8,"map26: $i; pmp<br>"); break;
                                        case (preg_match("/first time/i", $data[$i])):		$map[27]=$i; debug(8,"map27: $i; ft<br>"); break;
                                        case (preg_match("/city and state/i", $data[$i])):	$map[28]=$i; debug(8,"map28: $i; cst<br>"); break;
                                        case (preg_match("/company name\?/i", $data[$i])):	$map[29]=$i; debug(8,"map29: $i; comp<br>"); break;
                                        case (preg_match("/authorize/i", $data[$i])):		$map[30]=$i; debug(8,"map30: $i; auth<br>"); break;
                                        case (preg_match("/question(s)*/i", $data[$i])):	$map[31]=$i; debug(8,"map31: $i; que<br>"); break;
                                        case (preg_match("/(allergy)|(dietary)/i", $data[$i])):	$map[32]=$i; debug(8,"map32: $i; food<br>"); break;
                                        case (preg_match("/industry/i", $data[$i])):		$map[33]=$i; debug(8,"map33: $i; ind<br>"); break;
                                        case (preg_match("/(networking list)|(networking handout)/i", $data[$i])):		$map[34]=$i; debug(8,"map34: $i; net<br>"); break;
                                        default: echo("Encountered an unknown column: '" . $data[$i] . "'<br>"); break;
                                    } // switch
				} // for
                                    print_r($map); echo("<p>");
				} else {

                                    $prefix	= trim(ucwords($data[$map[2]])); 	isset($prefix) ?: $prefix = null;
                                    $first 	= trim(ucwords($data[$map[3]])); 	isset($first) ?: $first = null;
                                    $last 	= trim(ucwords($data[$map[4]])); 	isset($last) ?: $last = null;
                                    $suffix 	= trim(ucwords($data[$map[5]])); 	isset($suffix) ?: $suffix = null;
                                    $email1 	= trim(strtolower($data[$map[6]]));     isset($email1) ?: $email1 = null;
                                    $seats 	= $data[$map[8]];	isset($seats)   ?: $seats = 1;
                                    $regDate	= $data[$map[9]];	isset($regDate) ?: $regDate = null;
                                    $regBy	= $data[$map[10]];	isset($regBy)   ?: $regBy = null;
                                    $pmtType	= $data[$map[11]];	isset($pmtType) ?: $pmtType = null;
                                    $confirm	= $data[$map[12]];	isset($confirm) ?: $confirm = null;
                                    $received	= $data[$map[13]];	isset($received)?: $received = 0;

                                    if (preg_match("/[no|0]/i", $received)) {
                                        $received = 0; 
                                        //debug(2, "Set pmtRecd to 0.<br>");
                                    } else { 
                                        $received = 1; 
                                        //debug(2, "Set pmtRecd to 1.<br>");
                                    }
                                    $status         = $data[$map[14]];	isset($status)  ?: $status = null;
                                    $cancel         = $data[$map[15]];	($cancel=="")   ?: $cancel = null;
                                    $cost           = $data[$map[16]];	isset($cost)    ?: $cost = '0.00';
                                    $ccfee          = $data[$map[17]];	isset($ccfee)   ?: $ccfee = '0.00';
                                    $handlefee      = $data[$map[18]];	isset($handlefee) ?: $handlefee = '0.00';
                                    $orgAmount      = $data[$map[19]];	isset($orgAmount) ?: $orgAmount = '0.00';
                                    $discCode       = $data[$map[20]];	isset($discCode) ?: $discCode = null;
                                    $discDesc       = $data[$map[21]];	isset($discDesc) ?: $discDesc = null;
                                    $discAmt        = $data[$map[22]];	isset($discAmt)  ?: $discAmt = '0.00';

                                    if(isset($map[23])) $pmiid	= $data[$map[23]];		
                                    isset($pmiid) ?: $pmiid = null;
                                    is_numeric($pmiid) ?: $pmiid = null;

                                    if(isset($map[24])) $prefName = $data[$map[24]];		
                                    isset($prefName) ?: $prefName = null;

                                    if(isset($map[25])) $topics	= $data[$map[25]];		
                                    isset($topics) ?: $topics = null;

                                    if(isset($map[26])) $OrgStat3	= $data[$map[26]];		
                                    isset($OrgStat3) ?: $OrgStat3 = null;
                                    
                                    if(isset($map[27])){
                                        $firstEvent	= $data[$map[27]];					
                                        isset($firstEvent) ?: $firstEvent = 0;
                                        if (preg_match("/[no|0]/i", $firstEvent)) { $firstEvent = 0; } else { $firstEvent = 1; }
                                    }
                                    if(isset($map[28]))$commute= $data[$map[28]];		
                                    isset($commute) ?: $commute = null;

                                    if(isset($map[29])) $comp	= trim(ucwords($data[$map[29]])); 	
                                    isset($comp) ?: $comp = null;

                                    if(isset($map[30])){
                                        $authorized= $data[$map[30]];					
                                        isset($authorized) ?: $authorized = 0;
                                        if (preg_match("/[no|0]/i", $authorized)) { $authorized = 0; } else { $authorized = 1; }
                                    }
                                    if(isset($map[31])){$questions	= $data[$map[31]];  isset($questions) ?: $questions = "";}
                                    if(isset($map[32])){$allergy	= $data[$map[32]];  isset($allergy) ?: $allergy = "";}
                                    if(isset($map[33])){$industry	= $data[$map[33]];  isset($industry) ?: $industry = "";}
                                    if(isset($map[34])){
                                        $network	= $data[$map[34]];					
                                        isset($network) ?: $network = 0;
                                        if (preg_match("/[no|0]/i", $network)) { $network = 0; } else { $network = 1; }
                                    }
                                    debug(2, "Processed $num column variables.<br>");

                                    
                    debug(4, "<hr>Prefix: $prefix, first: $first, last: $last, suffix: $suffix, email: $email1, seats: $seats, "
                            . "regDate: $regDate, regBy: $regBy, pmtType: $pmtType, confirm: $confirm, received: $received, "
                            . "status: $status, cancel: $cancel, cost: $cost, ccfee: $ccfee, handle: $handlefee, "
                            . "orgAmt: $orgAmount, discount: $discCode, desc: $discDesc, dAmt: $discAmt, "
                            . "pmi: $pmiid, pref: $prefName, topics: $topics, O3: $OrgStat3, FE: $firstEvent, commute: $commute"
                            . ", company: $comp, PDUs: $authorized, Q: $questions, food: $allergy, ind: $industry, network: $network <hr>");
                                    
                                    if (!empty($regDate) && $regDate !== null) {
                                        list($m, $d, $y) = array_pad(explode("/", $regDate, 3), 3, null);
                                        $regDate = date('Y-m-d H:i:s', strtotime("$y-$m-$d 00:00:00"));
                                        debug(2, "Changed regDate to $regDate. and cancel: $cancel<br>");
                                    }

                                    if (!empty($cancel) && $cancel !== null) {
                                        //list($m, $d, $y) = array_pad(explode("/", $cancel, 3), 3, null);
                                        $cancel = date('Y-m-d H:i:s', strtotime($cancel));
                                        debug(2, "Changed cancelDate to $cancel.<br>");
                                    } 
				
				if($personID = get_personID_from_email($dbh, $email1)) {
                                    debug(2, "Found '$email1' in database... with personID: $personID, so may update company: '$comp'<br>");

                                    //Need to check to see if existing email, personID and org-person entry
                                    $cnt_sql = "SELECT count(*) FROM `org-person` WHERE personID=$personID AND orgID=$orgID";
                                    $cnt = get_count($dbh, $cnt_sql);
                                    
                                    if($cnt ===0) {
                                        $sql = "INSERT INTO `org-person` (orgID, personID, OrgStat1, OrgStat3) 
                                                VALUES (:orgID, :personID, :OrgStat1, :OrgStat3)";
                                        try {
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':orgID', $orgID, PDO::PARAM_INT);
                                            $query->bindParam(':personID', $personID, PDO::PARAM_INT);
                                            !isset($pmiid) ? $query->bindValue(':OrgStat1',  null, PDO::PARAM_INT) :$query->bindParam(':OrgStat1', $pmiid, PDO::PARAM_INT);
                                            !isset($OrgStat3) ? $query->bindValue(':OrgStat3',  null, PDO::PARAM_INT) :$query->bindParam(':OrgStat3', $OrgStat3, PDO::PARAM_STR);
                                            $query->execute();

                                            // insert an industry into person
                                        } catch (PDOException $e){
                                            echo $e->getMessage();
                                        }
                                    }
                                    
                                    if($comp !== null) {
                                        $cur_comp = check_item_in_table_for_value($dbh, "personID", "person", $personID, 0, "compName");
                                        if($cur_comp == $comp) {
                                                debug(2, "The company is already $cur_comp so no change. <br>");
                                        } else {
                                            $sql = "UPDATE `person` SET
                                                        compName=?
                                                    WHERE personID=? and compName is null";
                                            try {
                                                $q = $dbh->prepare($sql);
                                                empty($comp) ? $q->bindValue(1, null, PDO::PARAM_INT) : $q->bindParam(1, $comp, PDO::PARAM_STR);
                                                //empty($prefName) ? $q->bindValue(2, null, PDO::PARAM_INT) : $q->bindParam(2, $prefName, PDO::PARAM_STR);
                                                $q->bindParam(2, $personID, PDO::PARAM_INT);
                                                $q->execute();
                                            } catch (PDOException $e){
                                                echo $e->getMessage();
                                            }
                                        }
                                    }
				// perform checks to see if `org-person` needs to have OrgStat3 (isCertified) or OrgStat1 (PMI ID) updated
                                } elseif (!empty($pmiid) && ($personID = get_personID_from_orgstat1($dbh, $pmiid))) {
                                    // The personID with this non-null PMI ID exists so associate the email address with this personID
                                    debug(2, "If pmiid: $pmiid is null, we should NOT be here seeing personID: $personID");
                                    $sql = "INSERT INTO `person-email` (personID, emailADDR) VALUES (?, ?)";
                                    try {
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(1, $personID, PDO::PARAM_INT);
                                        $query->bindParam(2, $email1, PDO::PARAM_STR);
                                        $query->execute();
                                    } catch (PDOException $e){
                                        echo $e->getMessage();
                                    }
				} else {
                                    // The only way to detect a duplicate (email address or PMI ID) so, 
                                    // person must be inserted; then org-person; then email; then address
                                    debug(2, "Didn't find '$email1' in database... Inserting person...<br>");
                                    if(isset($prefName)) { 
                                            $prefPrint = ", :prefName"; 
                                            $prefVar = ", prefName"; 
                                    } else { 
                                            $prefPrint = ""; 
                                            $prefVar = ""; 
                                    }
                                    $sql = "INSERT INTO `person` (prefix, firstName, lastName, suffix, login, compName, defaultOrgID" . $prefVar .") 
                                            VALUES (:prefix, :firstName, :lastName, :suffix, :login, :compName, :defaultOrgID" . $prefPrint . ")";
                                    try {	
                                        $query = $dbh->prepare($sql);

                                        empty($prefix) ? $query->bindValue(':prefix',  null, PDO::PARAM_INT) : $query->bindParam(':prefix', $prefix, PDO::PARAM_STR);
                                        empty($first) ? $query->bindValue(':firstName',  null, PDO::PARAM_INT) :$query->bindParam(':firstName', $first, PDO::PARAM_STR);
                                        empty($last) ? $query->bindValue(':lastName',  null, PDO::PARAM_INT) :$query->bindParam(':lastName', $last, PDO::PARAM_STR);
                                        empty($prefix) ? $query->bindValue(':suffix',  null, PDO::PARAM_INT) :$query->bindParam(':suffix', $suffix, PDO::PARAM_STR);
                                        empty($email1) ? $query->bindValue(':login',  null, PDO::PARAM_INT) :$query->bindParam(':login', $email1, PDO::PARAM_STR);
                                        empty($comp) ? $query->bindValue(':compName',  null, PDO::PARAM_INT) :$query->bindParam(':compName', $comp, PDO::PARAM_STR);
                                        $query->bindParam(':defaultOrgID', $orgID, PDO::PARAM_INT);
                                        if(isset($prefName)) {
                                            empty($prefName) ? $query->bindValue(':prefName',  null, PDO::PARAM_INT) :$query->bindParam(':prefName', $prefName, PDO::PARAM_STR);
                                        }
                                        $query->execute();
                                    } catch (PDOException $e){
                                        echo $e->getMessage();
                                    }

                                    $personID = $dbh->lastInsertId();

                                    // then insert org-person table
                                    $sql = "INSERT INTO `org-person` (orgID, personID, OrgStat1, OrgStat3) 
                                            VALUES (:orgID, :personID, :OrgStat1, :OrgStat3)";
                                    try {
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':orgID', $orgID, PDO::PARAM_INT);
                                        $query->bindParam(':personID', $personID, PDO::PARAM_INT);
                                        !isset($pmiid) ? $query->bindValue(':OrgStat1',  null, PDO::PARAM_INT) :$query->bindParam(':OrgStat1', $pmiid, PDO::PARAM_INT);
                                        !isset($OrgStat3) ? $query->bindValue(':OrgStat3',  null, PDO::PARAM_INT) :$query->bindParam(':OrgStat3', $OrgStat3, PDO::PARAM_STR);
                                        $query->execute();

                                        // insert an industry into person
                                    } catch (PDOException $e){
                                        echo $e->getMessage();
                                    }
                                    //debug(2, "Inserted `org-person` with personID: $personID and current orgID: $orgID...<br>");

                                    $sql = "INSERT INTO `person-email` (personID, isPrimary, emailADDR) VALUES (?, 1, ?)";
                                    try {
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(1, $personID, PDO::PARAM_INT);
                                        $query->bindParam(2, $email1, PDO::PARAM_STR);
                                        $query->execute();
                                    } catch (PDOException $e){
                                        echo $e->getMessage();
                                    }
                                    debug(2, "Inserted `person-email` with '$email1'...<br>");
				} // else
					
				// These statements must be executed whether the personID is new or not.  The event 'registration' is new.
				$sql = "INSERT INTO `event-registration` (personID, eventID, ticketID, reportedIndustry, cityState, canNetwork, 
                                            eventQuestion, eventTopics, foodStuff, isFirstEvent, isAuthPDU, createDate, registeredBy, regStatus)
                                        VALUES (:personID, :eventID, :ticketID, :industry, :cityState, :canNetwork, :questions, :topics, :allergy, 
                                            :firstEvent, :authPDU, :createDate, :registeredBy, :regStatus)";
				debug(2, "Adding registration for $personID.<br>");
				try {
                                    $query = $dbh->prepare($sql);
                                    $query->bindValue(':personID',  $personID, PDO::PARAM_INT);
                                    $query->bindValue(':eventID',  $eventID, PDO::PARAM_INT);
                                    $query->bindValue(':ticketID',  $ticketID, PDO::PARAM_INT);
                                    !isset($industry) 	? $query->bindValue(':industry',  null, PDO::PARAM_INT) :$query->bindParam(':industry', $industry, PDO::PARAM_STR);
                                    !isset($commute) 	? $query->bindValue(':cityState',  null, PDO::PARAM_INT) :$query->bindParam(':cityState', $commute, PDO::PARAM_STR);
                                    !isset($network) 	? $query->bindValue(':canNetwork',  0, PDO::PARAM_INT) :$query->bindParam(':canNetwork', $network, PDO::PARAM_INT);
                                    !isset($questions) 	? $query->bindValue(':questions',  null, PDO::PARAM_INT) :$query->bindParam(':questions', $questions, PDO::PARAM_STR);
                                    !isset($topics) 	? $query->bindValue(':topics',  null, PDO::PARAM_INT) :$query->bindParam(':topics', $topics, PDO::PARAM_STR);
                                    !isset($allergy) 	? $query->bindValue(':allergy',  null, PDO::PARAM_INT) :$query->bindParam(':allergy', $allergy, PDO::PARAM_STR);
                                    !isset($firstEvent) ? $query->bindValue(':firstEvent',  0, PDO::PARAM_INT) :$query->bindParam(':firstEvent', $firstEvent, PDO::PARAM_STR);
                                    !isset($authorized) ? $query->bindValue(':authPDU',  0, PDO::PARAM_INT) :$query->bindParam(':authPDU', $authorized, PDO::PARAM_STR);
                                    !isset($regDate) 	? $query->bindValue(':createDate',  null, PDO::PARAM_INT) :$query->bindParam(':createDate', $regDate, PDO::PARAM_STR);
                                    !isset($regBy) 	? $query->bindValue(':registeredBy',  $personID, PDO::PARAM_INT) :$query->bindParam(':registeredBy', $regBy, PDO::PARAM_STR);
                                    !isset($status) 	? $query->bindValue(':regStatus',  "Active", PDO::PARAM_STR) :$query->bindParam(':regStatus', $status, PDO::PARAM_STR);
                                    $query->execute();
					
				} catch (PDOException $e){
                                    echo $e->getMessage();
				}
				$regID = $dbh->lastInsertId();
				debug(2, "Added registrationID: $regID <br>");
				
				$sql = "INSERT INTO `reg-finance` (regID, personID, eventID, ticketID, ccFee, cost, confirmation, discountCode, 
                                            discountAmt, handleFee, orgAmt, pmtRecd, pmtType, seats, status, createDate, cancelDate)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				debug(2, "Adding registration finance record... with '$regDate' and '$cancel' <br>");
				try {
                                    $query = $dbh->prepare($sql);
                                    $query->bindValue(1,  $regID, PDO::PARAM_INT);
                                    $query->bindValue(2,  $personID, PDO::PARAM_INT);
                                    $query->bindValue(3,  $eventID, PDO::PARAM_INT);
                                    $query->bindValue(4,  $ticketID, PDO::PARAM_INT);
                                    !isset($ccfee)          ? $query->bindValue(5,  null, PDO::PARAM_INT) :$query->bindParam(5, $ccfee, PDO::PARAM_STR);
                                    !isset($cost)           ? $query->bindValue(6,  null, PDO::PARAM_INT) :$query->bindParam(6, $cost, PDO::PARAM_STR);
                                    !isset($confirm)        ? $query->bindValue(7,  null, PDO::PARAM_INT) :$query->bindParam(7, $confirm, PDO::PARAM_STR);
                                    !isset($discCode)       ? $query->bindValue(8,  null, PDO::PARAM_INT) :$query->bindParam(8, $discCode, PDO::PARAM_STR);
                                    !isset($discAmt)        ? $query->bindValue(9,  0, PDO::PARAM_INT) :$query->bindParam(9, $discAmt, PDO::PARAM_STR);
                                    !isset($handlefee)      ? $query->bindValue(10,  0, PDO::PARAM_INT) :$query->bindParam(10, $handlefee, PDO::PARAM_STR);
                                    !isset($orgAmount)      ? $query->bindValue(11,  0, PDO::PARAM_INT) :$query->bindParam(11, $orgAmount, PDO::PARAM_STR);
                                    !isset($received)       ? $query->bindValue(12,  0, PDO::PARAM_INT) :$query->bindParam(12, $received, PDO::PARAM_INT);
                                    !isset($pmtType)        ? $query->bindValue(13,  null, PDO::PARAM_INT) :$query->bindParam(13, $pmtType, PDO::PARAM_STR);
                                    !isset($seats)          ? $query->bindValue(14,  null, PDO::PARAM_INT) :$query->bindParam(14, $seats, PDO::PARAM_INT);
                                    !isset($status)         ? $query->bindValue(15,  null, PDO::PARAM_INT) :$query->bindParam(15, $status, PDO::PARAM_STR);
                                    !isset($regDate)        ? $query->bindValue(16,  null, PDO::PARAM_INT) :$query->bindParam(16, $regDate, PDO::PARAM_STR);
                                    ($cancel=='')           ? $query->bindValue(17,  null, PDO::PARAM_INT) :$query->bindParam(17, $cancel, PDO::PARAM_STR);
                                    $query->execute();
                                    //var_dump($query);
					
				} catch (PDOException $e){
                                    echo $e->getMessage();
				}
			
				debug(2, "Added registration finance record: $regID <hr>");
			}
		}
		
		// echo "<p>=========================================== $num fields in line $row: ===========================================<br /></p>\n";
		// for ($c=0; $c < $num; $c++) { echo $data[$c] . "<br />\n"; }
		echo("Worked on record $row<br />\n");
	}

	fclose($handle);

	print "Import of $row items complete";

	//view upload form
} else {
?>    

Upload data by browsing to file and clicking on Upload<br />
<form enctype='multipart/form-data' action='data_upload.php' method='post'>
    File name to import:<br />
    <input size='50' type='file' name='filename'><br />
    <input type='submit' name='submit' value='Upload'><br />
    <select id="dt" name='data_type'>
        <option value='mbrdata'>Member Data</option>
        <option value='evtdata'>Event Data</option>
    </select><br />
    <select id="evt" name='eventID' style='display:none;'>
        <option>Select an event...</option>
<?php
            $ev_sql = "SELECT eventID, eventName, date_format(eventStartDate, '%b %Y') "
                    . "FROM `org-event`"
                    . "WHERE orgID=$orgID";
            try {
                $ev_q = $dbh->prepare($ev_sql);
                $ev_q->execute();
                while ($ev_row = $ev_q->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $ev_id = $ev_row[0];  $ev_name = $ev_row[1]; $ev_date = $ev_row[2];
                    echo("<OPTION value='$ev_id'>$ev_id: $ev_date -> $ev_name</OPTION>\n");
                }
            } catch (Exception $ex) {

            }
?>
    </select>

</form>

<?php
}  // end of else
?>
</div>
</div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>	
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>    
    <script>
        $(location).ready(function() {
            $('#dt').on('change', function() {
                var dt = $('#dt').val();
                if (dt === 'evtdata') {
                    $('#evt').show();
                }
            });
        });
    </script>
</body>
</html>

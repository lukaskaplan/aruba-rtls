<?php
include("./config.php");

// Reduce errors
error_reporting(~E_WARNING);

// Connect to database
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
if($conn->connect_error){
    die ("Connection failed: " . $conn->connect_error);
}
else echo "Connected to DB\n";

// Create a UDP socket
if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))){
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}
else echo "Socket created \n";
 
// Bind the source address
if( !socket_bind($sock, "0.0.0.0" , AR_PORT) ){
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Could not bind socket : [$errorcode] $errormsg \n");
}
else echo "Socket bind OK \n";
 
// Start RTLS communication
while(1){
    //Receive some data
    $rcv_payload = ""; 
    $r = socket_recvfrom($sock, $buf, 1024, 0, $remote_ip, $remote_port);
    $rcv_message = rcv_explode($buf);
    //echo "$remote_ip:$remote_port -- ".get_msg_type($rcv_message[0])."\n";

    /*
     * If we receive AR_AP_NOTIFICATION,
     * we have to acknowledge it.
     * */
    if(get_msg_type($rcv_message[0]) == "AR_AP_NOTIFICATION"){
        $header = parse_header($rcv_message[0]);
	$sql = "INSERT INTO AR_AP_NOTIFICATION (msg_id, ap_mac) 
		VALUES ('".bin2hex($header[1])."', '".bin2hex($header[5])."') 
		ON DUPLICATE KEY UPDATE msg_id='".bin2hex($header[1])."'";

	if($conn->query($sql) != TRUE) die("DB Error: ".$sql." - " . $conn->error);

	// Prepare ACK message
	$ack_header_fields = $header;
	// Set message type to ACK (0x0010)
	$ack_header_fields[0] = hex2bin("0010");
	// Create header from array of fields
	$ack_header = implode("",$ack_header_fields);
	// Create sha1 hash of header
	$ack_checksum = hex2bin(hash_hmac('sha1', $ack_header, AR_KEY));
	// Make whole ack message
	$ack_message = $ack_header.$ack_checksum;
	// echo "---> ACK: ".bin2hex($ack_header)." - ".bin2hex($ack_checksum)."\n";	
	// Send ack message
	socket_sendto($sock, $ack_message , strlen($ack_message) , 0 , $remote_ip , $remote_port);
    } // end of AR_AP_NOTIFICATION section

    /* 
     * If we receive AR_COMPOUD_REPORT.
     * we have to parse it to submessages,
     * and then extract data and save them to database.
     */
    if(get_msg_type($rcv_message[0]) == "AR_COMPOUND_MESSAGE_REPORT"){
        $msg_count = hexdec(bin2hex(substr($rcv_message[1],0,2))); //little complicated because simple bindec() does not work here
	//echo "\tHDR:".bin2hex(substr($rcv_message[1],0,4))." - dec: ".$msg_count."\n";

	for($i=0; $i<$msg_count; $i++){
	    $offset = 4 + ($i * 44);
	    $size = 44;
	    // cut one sub-message from payload
	    $sub_buf = substr($rcv_message[1],$offset,$size);
	    // explode it to header, payload
	    $sub_message[0] = substr($sub_buf, 0, 16);	//16 byte - header
	    $sub_message[1] = substr($sub_buf, 16);		//rest is payload

	    if(get_msg_type($sub_message[0]) == "AR_STATION_REPORT"){
	        //echo "\t DATA: ".bin2hex(substr($rcv_message[1],$offset,$size))."\n";
		$sub_header = parse_header($sub_message[0]);
		$data = parse_stationreport($sub_message[1]);
		$sql = "INSERT INTO AR_STATION_REPORT (ap_mac,station_mac,noise_floor,data_rate,channel,rssi,type,associated,radio_bssid,mon_bssid,age) 
			VALUES ('".bin2hex($sub_header[5])."',
			'".bin2hex($data[0])."', 
			'".bin2hex($data[1])."', 
			'".bin2hex($data[2])."', 
			'".bin2hex($data[3])."', 
			'".bin2hex($data[4])."', 
			'".bin2hex($data[5])."', 
			'".bin2hex($data[6])."', 
			'".bin2hex($data[7])."', 
			'".bin2hex($data[8])."', 
			'".bin2hex($data[9])."' 
			)";

                if($conn->query($sql) != TRUE) die("DB Error: ".$sql." - " . $conn->error);
	    }
            else echo get_msg_type($sub_message[0])." - not written in DB\n";
	}

    } // end of AR_COMPOUND_MESSAGE_REPORT section

} // end of while

// Close UDP socket
socket_close($sock);

// Close DB connection
$conn->close();

/****************************************************************************************************/

function get_msg_type($header){
	$type = substr($header,0,2);
	$type = bin2hex($type);
	if($type == "0000") return "AR_AS_CONFIG_SET";
	if($type == "0001") return "AR_STATION_REQUEST";
	if($type == "0010") return "AR_ACK";
	if($type == "0011") return "AR_NACK";
	if($type == "0012") return "AR_TAG_REPORT";
	if($type == "0013") return "AR_STATION_REPORT";
	if($type == "0014") return "AR_COMPOUND_MESSAGE_REPORT";
	if($type == "0015") return "AR_AP_NOTIFICATION";
	if($type == "0016") return "AR_MMS_CONFIG_SET";
	if($type == "0017") return "AR_STATION_EX_REPORT";
	if($type == "0018") return "AR_AP_EX_REPORT";
	else return "UNKNOWN";
}

function rcv_explode($message){
	/* 
	 * $message = whole RTLS binary message
	 * return array:
	 * [0] = 16 byte - RTLS header
	 * [1] = RTLS payload
	 * [2] = 20 byte - RTLS hmac-sha1 hash of header and payload
	 */
    $rtls = array();
    $rtls[0] = substr($message, 0, 16);
    $rtls[1] = substr($message, 16, -20);
    $rtls[2] = substr($message,-20);
    return $rtls;
}

function parse_header($header){
	/*
	 * $header = 16 byte header in binary
	 * return array
	 * [0] = 2 byte - Message type
	 * [1] = 2 byte - Message Id
	 * [2] = 1 byte - Major version (1 or 2)
	 * [3] = 1 byte - Minor version (always 0)
	 * [4] = 2 byte - Data Length (length of rtls payload)
	 * [5] = 6 byte - AP MAC
	 * [6] = 2 byte - Padding
	 */
    $field = array();
    $field[0] = substr($header,0,2);
    $field[1] = substr($header,2,2);
    $field[2] = substr($header,4,1);
    $field[3] = substr($header,5,1);
    $field[4] = substr($header,6,2);
    $field[5] = substr($header,8,6);
    $field[6] = substr($header,14,2);
    return $field;
}

function parse_stationreport($payload){
	/*
	 * $payload = 28 byte binary ar_station_report message
	 * Return array:
	 * [0] = 6 byte - station MAC (ap or client)
	 * [1] = 1 byte - noise floor
	 * [2] = 1 byte - Data rate
	 * [3] = 1 byte - Channel
	 * [4] = 1 byte - RSSI
	 * [5] = 1 byte - Type (AR_WLAN_CLIENT, AR_WLAN_AP)
	 * [6] = 1 byte - Associated (1= all aps and associated stations, 2= unassociated stations)
	 * [7] = 6 byte - Radio_BSSID (radio which detected the device)
	 * [8] = 6 byte - Mon_BSSID (AP that the station is associated to)
	 * [9] = 4 byte - age, # of seconds since the last packet was heard from this station
	 * */
	$field = array();
	$field[0] = substr($payload,0,6);
	$field[1] = substr($payload,6,1);
	$field[2] = substr($payload,7,1);
	$field[3] = substr($payload,8,1);
	$field[4] = substr($payload,9,1);
	$field[5] = substr($payload,10,1);
	$field[6] = substr($payload,11,1);
	$field[7] = substr($payload,12,6);
	$field[8] = substr($payload,18,6);
	$field[9] = substr($payload,24,4);
	return $field;
}

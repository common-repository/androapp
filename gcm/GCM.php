<?php
if ( ! defined( 'ABSPATH' ) ) exit; 

use phpFCMv1\Client;
use phpFCMv1\Recipient;
use phpFCMv1\Data;
        
class GCM {
 
    //put your code here
    // constructor
    function __construct() {
         
    }
    
    private function loadGCMClientFiles(){
        require_once __DIR__ . '/vendor/autoload.php';
    }
    
    private function get_v1_data($message){
        $message['post_id'] = strval($message['post_id']);
        $payload = array('data' => $message);
        $data = new Data();
        $data->setPayload($payload);
        return $data;
    }
    
    public function send_notification_android_topic_v1($topic_name, $message, $firebaseServiceAccountFilePath){
        $this->loadGCMClientFiles();
        // Client instance should be created with path to service account key file
        $filePath = $this->getFirebaseServiceAccountFilePath($firebaseServiceAccountFilePath);
        echo "<br/><b>Firebase Service Account File Path = $filePath</b><br/>";
        $client = new Client($filePath);
        $data = $this->get_v1_data($message);
        $recipient = new Recipient();
        $recipient->setTopicRecipient($topic_name);
       
        // Build FCM request payload
        $client -> build($recipient, null, $data);

        $result = $client -> fire();
        echo "<br/><br/>result = ";
        echo $result;
        return $result;
    }
    
    private function getFirebaseServiceAccountFilePath($firebaseServiceAccountFilePath){
        if(!empty($firebaseServiceAccountFilePath)){
            return $firebaseServiceAccountFilePath;
        }
        return ABSPATH . '/firebase-service-accounts-key.json';
    }
    /**
     * Sending Push Notification using Firebase HTTP V1 APIs
     */
    public function send_notification_v1($registatoin_ids, $message, $firebaseServiceAccountFilePath) {
        $this->loadGCMClientFiles();
        // Client instance should be created with path to service account key file
        $filePath = $this->getFirebaseServiceAccountFilePath($firebaseServiceAccountFilePath);
        echo "<br/><b>Firebase Service Account File Path = $filePath</b><br/>";
        $client = new Client($filePath);
        $data = $this->get_v1_data($message);
        $count = 0;
        
        foreach($registatoin_ids as $registatoin_id){
            $recipient = new Recipient();
            $recipient->setSingleRecipient($registatoin_id);
            // Build FCM request payload
            $client->build($recipient, null, $data);

            $result = $client -> fire();  
            echo "<br/>result = ";
            echo $result;
            if($result){
                $count++;
            }
        }
        return $count;
    }
    
     /**
     * Sending Push Notification
     */
    public function send_notification_ios_topic($topic_name, $message, $google_api_key, $registatoin_ids) {
        //Creating the notification array.
        $google_api_key = trim($google_api_key);
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        
        $body = $message['excerpt'];
        $body = strip_tags($body);
	$body = html_entity_decode($body);
       
	$title = $message['title'];
	$title = strip_tags($title);
	$title = html_entity_decode($title);
 
        $notification = array( 
            'title' => $title , 'body' => $body,
        	'mutable_content' => true,
	'attachment-url' => $message['postImage'] 
	);
	$indata = array('post_id' => $message['post_id'], 'postType' => $message['postType'], 'link' => $message['link'],
            'notification_type' => $message['notification_type'],
            'cache' => $message['cache'],
		'attachment-url' => $message['postImage']);

	echo "Indata = ".json_encode($indata, JSON_UNESCAPED_SLASHES);
	$data = array('data' => $indata);

        $fcmOptions = array("analytics_label" => "PostId ".$message['post_id']);
        if(empty($topic_name)){
            //This array contains, the token and the notification. The 'to' attribute stores the token.
            $arrayToSend = array('registration_ids' => $registatoin_ids, 'data' => $data,
                'notification' => $notification,'priority'=>'high', 'ttl' => '259200s',
                'fcm_options' => $fcmOptions);
        }else{
            //This array contains, the token and the notification. The 'to' attribute stores the token.
            $arrayToSend = array('to' => "/topics/".$topic_name, 'data' => $data, 
                'notification' => $notification, 'priority'=>'high', 'ttl' => '259200s',
                'fcm_options' => $fcmOptions);    
        }
        
        
        //Generating JSON encoded string form the above array.
        $json = json_encode($arrayToSend, JSON_UNESCAPED_SLASHES);

	echo "JSon = ".$json;
        //Setup headers:
        $headers = array();
        $headers[] = 'Content-Type: application/json';

        $headers[] = "Authorization: key=".$google_api_key;

        //Setup curl, add headers and post parameters.
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);       

        //To return the output as string instead of printing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        //Send the request
        $response = curl_exec($ch);

        //Close request
        curl_close($ch);
        return $response;
    }
     /**
     * Sending Push Notification
     */
    public function send_notification_topic($topic_name, $message, $google_api_key, 
            $firebaseApiVersion, $firebaseServiceAccountFilePath) {
        if($firebaseApiVersion == "v1"){
            return $this->send_notification_android_topic_v1($topic_name, $message, $firebaseServiceAccountFilePath);
        }
        $google_api_key = trim($google_api_key);
        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';
 
        $fields = array(
            'to' => "/topics/".$topic_name,
            'data' => $message,
        );
 
        $headers = array(
            'Authorization: key=' . $google_api_key,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
 
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
 
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
 
        // Close connection
        curl_close($ch);
        return $result;
    }

    /*
     * Sending Push Notification for IOS
     */
    public function send_notification_ios($registatoin_ids, $message, $google_api_key) {
        return $this->send_notification_ios_topic("", $message, $google_api_key, $registatoin_ids);
    }
    
    /**
     * Sending Push Notification
     */
    public function send_notification($registatoin_ids, $message, $google_api_key, 
            $firebaseApiVersion, $firebaseServiceAccountFilePath) {
        if($firebaseApiVersion == "v1"){
            $this->send_notification_v1($registatoin_ids, $message, $firebaseServiceAccountFilePath);
        }
        $google_api_key = trim($google_api_key);
        // Set POST variables
        //$url = 'https://android.googleapis.com/gcm/send';
        $url = 'https://fcm.googleapis.com/fcm/send';
 
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
 
        $headers = array(
            'Authorization: key=' . $google_api_key,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
 
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
 
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
 
        // Close connection
        curl_close($ch);
        return $result;
    }
 
}
 
?>

<?php
//require_once ('GCM.php');
require_once __DIR__ . '/vendor/autoload.php';


use phpFCMv1\Client;
use phpFCMv1\Notification;
use phpFCMv1\Recipient;
use phpFCMv1\Data;

// Client instance should be created with path to service account key file
$client = new Client('hstal-service-accounts.json');
$recipient = new Recipient();
// Either Notification or Data (or both) instance should be created
$notification = new Notification();

$d = array('post_id' => "387", 'postType' => "posts", 'link' => "https://hindi.storytal.com/387/patni-ne-liya-pati-ka-test/",
            'notification_type' => "stack",
            'cache' => "yes",
            'attachment-url' => "https://hindi.storytal.com/wp-content/uploads/2017/10/patni_ne_liya_pati_ka_test-150x150.png");

$payload = array('data' => $d);
$data = new Data();
$data->setPayload($payload);

// Recipient could accept individual device token,
// the name of topic, and conditional statement
//$recipient -> setSingleREcipient('dVTrI-COmW0:APA91bHvtY-TWbglfU61mna6mkY9yT1aFokwDqGG0Pg2rWjiib4pVPZE5S8PyaD9JhzHJt_iFwZY_UkHDdfn0gq69dlhl6Ox44x6FOFypSPpXb5n3_3wc-pKtR-MnNyPt2cbceTu3OAX');
$recipient -> setSingleREcipient('dijG_HcaIvw:APA91bE9o-4EBOTlJo8vVBjtGm_NG_9C3ID_h-7U_ZN8CMPD0MoXBtCmCW4kDoL-x7mH1kurzqbPbJNz_AiyFAsMrVywILHWCi2qFj4CysBKSauaOKXlv03UpSxWAS559VlNGzXW0zEz');
// Setup Notificaition title and body
$notification->setNotification('Test Title', 'Test Body');

// Build FCM request payload
$client -> build($recipient, null, $data);

$result = $client -> fire();
// You can check the result
// If successful, true will be returned
// If not, error message will be returned
echo $result;
echo "done";
function old_way() {
    $gcm = new GCM();

    $message = array("post_id" => $_GET['postid'], "notification_type" => "stack");
    $google_api_key = "AIzaSyAPCbTgCQlD1v8nvNms5Dc7YO__21QEDgY";
    $gcmResult = $gcm->send_notification_topic("all", $message, $google_api_key);

    echo  ($gcmResult);
}
?>

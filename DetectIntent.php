<?php

require_once 'vendor/autoload.php';
require_once 'lib.php';


use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\EventInput;

// Load env vars from .env file in dev environment
if (class_exists('\Symfony\Component\Dotenv\Dotenv')) {
    $dotenv = new \Symfony\Component\Dotenv\Dotenv();
    if (is_readable(__DIR__.'/.env')) $dotenv->load(__DIR__.'/.env');
}

/**
 * If evaluated to true, we will mock the service instead of really requesting dialogflow
 *
 * @var string
 */
$mock = getenv('MOCK_SERVICE');

/**
 * Read Google project ID from environment
 *
 * @var string
 */
$projectId = getenv('GOOGLE_PROJECT_ID');

/**
 * Accept session identifier as POST (preferred) or GET parameter `session`
 * Default/Fallback: 'test123'
 *
 * @var string
 */
$sessionId = filter_input(INPUT_POST, 'session', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK)
    ?: (filter_input(INPUT_GET, 'session', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK)
        ?: 'test123');

/**
 * Accept message as POST param `message` (preferred) or GET param `q`
 * Default/Fallback: 'Hi'
 *
 * @var string
 */
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)
    ?: (filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING)
        ?: 'Hi');

/**
 * Accept event name as POST param `event` or GET param `e`
 * Omit the event param, if none of the above is supplied
 *
 * @var string
 */
$event = filter_input(INPUT_POST, 'event', FILTER_SANITIZE_STRING)
    ?: filter_input(INPUT_GET, 'e', FILTER_SANITIZE_STRING);

/**
 * Accept language code as POST param `language` or GET param `lang`
 * Default: 'en-US'
 *
 * @var string
 */
$languageCode = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING)
    ?: (filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING)
        ?: 'en-US');

if ($mock) {
    $responseBody = '{"queryText":"welcome","languageCode":"en","parameters":null,"allRequiredParamsPresent":true,"fulfillmentText":"Hello, my name is unknown \ud83d\udc11","fulfillmentMessages":[{"text":{"text":["Hello, my name is unknown \ud83d\udc11"]}},{"payload":{"suggested_replies":["Hello","Unknown?","Tell a joke","\uf09f\u92a9","\ud83d\udca9"]}}],"intent":{"name":"projects\/mock\/agent\/intents\/fake-mock","displayName":"First Contact"},"intentDetectionConfidence":1}';
} else {
    // Initialize new session
    $sessionsClient = new SessionsClient();
    $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());
    // Create query input
    $queryInput = new QueryInput();
    if ($message) {
        // Create text input
        $textInput = new TextInput();
        $textInput->setText($message);
        $textInput->setLanguageCode($languageCode);
        $queryInput->setText($textInput);
    }
    if ($event) {
        $eventInput = new EventInput();
        $eventInput->setName($event);
        $eventInput->setLanguageCode($languageCode);
        $queryInput->setEvent($eventInput);
    }
    // Get response and close connection
    $detectedIntentResponse = $sessionsClient->detectIntent($session, $queryInput);
    $sessionsClient->close();
    // Serialize Query Result to JSON
    $responseBody = $detectedIntentResponse->getQueryResult()->serializeToJsonString();
}

// Normalize the JSON string by adding null values
$responseBody = normalizeJSON($responseBody);
// Optionally prettify JSON
if (array_key_exists('pretty', $_GET) || getenv('PRETTY_OUTPUT')) {
    $responseBody = prettifyJSON($responseBody);
}
// Optionally set CORS header
if ($cors = getenv('DIALOGFLOW_CORS')) {
    header("Access-Control-Allow-Origin: {$cors}");
}

// Set content type and length response headers
header("Content-Type: application/json; charset=utf-8");
header("Content-Length: " . strlen($responseBody));

// Finally output the response body and we are done =)
echo $responseBody;
die;

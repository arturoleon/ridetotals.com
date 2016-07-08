<?php 
use \fkooman\OAuth\Client\Api;
use \fkooman\OAuth\Client\Context;
use \fkooman\OAuth\Client\PdoStorage;
use \Guzzle\Service\Client;
use \fkooman\OAuth\Client\ClientConfig;
use \App\Service\UberService;
use \fkooman\OAuth\Client\Callback;
use \Slim\Container;
use \Slim\App;
use \Slim\Views\PhpRenderer;

session_start();
// Eloquent initialization
$mysqlDetails = array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'totales',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci'
);
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($mysqlDetails);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$schema = $capsule->schema();

// Slim initializaiton
$configuration = array(
    'settings' => array(
        'displayErrorDetails' => true,
    )
);

// Bootstrap Oauth and UberService
$clientConfig = new ClientConfig(
    array(
        "authorize_endpoint" => "https://login.uber.com/oauth/v2/authorize",
        "client_id" => "",
        "client_secret" => "",
        "token_endpoint" => "https://login.uber.com/oauth/v2/token",
        "redirect_uri"  => "http://localhost/callback",
        "use_array_scope" => array("profile","history"),
        "enable_debug"   => true
    )
);
if(!isset($_SESSION["current_user"]))
    $_SESSION["current_user"] = uniqid("",TRUE);

$httpClient = new Client();
$pdo = new PDO("{$mysqlDetails['driver']}:host={$mysqlDetails['host']};dbname={$mysqlDetails['database']}",$mysqlDetails['username'],$mysqlDetails['password']);
$pdoStorage = new PdoStorage($pdo);
$api = new Api("uber", $clientConfig, $pdoStorage , $httpClient);
$callback = new Callback("uber", $clientConfig, $pdoStorage, $httpClient);
$context = new Context($_SESSION["current_user"], array("profile","history"));

// Create container and add classes
$container = new Container($configuration);
$container['renderer'] = new PhpRenderer("../app/views/");
$container['uberService'] = new UberService($httpClient, $context, $api, $callback);

$app = new App($container);
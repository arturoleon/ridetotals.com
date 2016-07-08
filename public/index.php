<?php
require '../vendor/autoload.php';
require '../app/bootstrap.php';

use \App\Model\User;
use \App\Model\Stats;
use \Slim\Http\Request;
use \Slim\Http\Response;

//move to utils
function seconds2human($ss) {
    if($ss == 0)
        return "0m";

    $s = $ss%60;
    $m = floor(($ss%3600)/60);
    $h = floor(($ss%86400)/3600);
    $d = floor(($ss%2592000)/86400);
    $M = floor($ss/2592000);
    $output = "";
    if($d > 0)
        $output .= "{$d}d ";

    if($h > 0)
        $output .= "{$h}h ";

    if($m > 0)
        $output .= "{$m}m";

    return $output;
}

// Routes
$app->get('/login', function (){
    $this->uberService->login("/details");
});

$app->get('/details', function(){
    $this->uberService->requireLogin();
    $output = $this->uberService->getStats();
    header("Location: /stats/{$output['user']['uuid']}");
    exit;
});

$app->get('/stats/{user}', function(Request $request, Response $response){
    $stats = Stats::getByUniqueId($request->getAttribute('user'));
    if(!$stats)
        return $this->renderer->render($response->withStatus(404), "404.php",array(
            'title' => "Página no encontrada"
        ));

    $stats = $stats->load("user")->toArray();
    $totalKms = number_format($stats["totalKm"],2);
    $totalTime = seconds2human($stats["totalTime"]);
    $metas = array(
        "og:title" => "RideTotals - Mis estadísticas de Uber",
        "og:description" => "He usado Uber {$stats["totalTrips"]} veces ({$totalKms} km) en un tiempo total de {$totalTime}"
    );

    return $this->renderer->render($response, "stats.php",array(
        'title' => "RideTotals - Estadísticas de {$stats['user']['first_name']}",
        'meta' => $metas,
        'output' => $stats,
        'isLoggedIn' => $this->uberService->isLoggedIn(),
        'isCurrentUser' => (isset($_SESSION["logged_user"]) && $_SESSION["logged_user"] == $stats["user"]["uuid"]),
    ));
});

$app->get('/', function (Request $request, Response $response) {
    return $this->renderer->render($response, "home.php",array("loginLink" => $this->uberService->getLoginLink()));
});

$app->get('/privacy', function (Request $request, Response $response) {
    return $this->renderer->render($response, "privacidad.php", array("title" => "RideTotals - Privacidad",));
});

$app->get('/top100', function (Request $request, Response $response) {
    $top100 = Stats::all()->sortByDesc("totalTrips")->take(100)->load("user")->toArray();
    return $this->renderer->render($response, "top100.php", array("title" => "RideTotals - Top 100","output" => $top100));
});

$app->get('/callback', function () {
    $this->uberService->callback($_GET);
    header("Location: /details");
    exit;
});

$app->run();
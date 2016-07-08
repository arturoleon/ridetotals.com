<?php
/*
 * This class is used to interact with the Uber API
 */

namespace App\Service;
use Exception;
use \fkooman\OAuth\Client\Exception\AuthorizeException;
use \App\Model\User;
use \App\Model\Stats;


class UberService{
    private $httpClient;
    private $context;
    private $api;
    private $callback;
    private $accessToken;
    private $baseUrl = "https://api.uber.com/";
    private $currentUser;

    /**
     * UberService constructor.
     * @param \Guzzle\Service\ClientInterface $httpClient
     * @param \fkooman\OAuth\Client\Context $context
     * @param \fkooman\OAuth\Client\Api $api
     * @param \fkooman\OAuth\Client\Callback $callback
     * @return mixed
     */
    public function __construct($httpClient, $context, $api, $callback){
        $this->httpClient = $httpClient;
        $this->context = $context;
        $this->api = $api;
        $this->callback = $callback;
        $this->accessToken = $api->getAccessToken($this->context);
        if ('' === session_id()) {
            // no session currently exists, start a new one
            session_start();
        }
    }

    /**
     * @param string $path
     * @return array
     */
    public function get($path){
        $request = $this->httpClient->get($this->baseUrl.$path)
            ->addHeader('Authorization', sprintf('Bearer %s', $this->accessToken->getAccessToken()));
        $body = $request->send()->getBody();

        return json_decode($body,true);
    }

    public function getUser(){
        if(isset($this->currentUser))
            return $this->currentUser;

        $response = $this->get("v1/me");
        $user = User::getByUniqueId($response["uuid"]);
        $_SESSION["logged_user"] = $response["uuid"];
        if($user) {
            $user->first_name = $response["first_name"];
            $user->last_name = $response["last_name"];
            $user->email = $response["email"];
            $user->picture = $response["picture"];
            $user->session_id = $_SESSION["current_user"];
            $user->save();
        }else{
            $user = new User(array(
                'first_name' => $response["first_name"],
                'last_name' => $response["last_name"],
                'email' => $response["email"],
                'picture' => $response["picture"],
                'uuid' => $response["uuid"],
                'session_id' => $_SESSION["current_user"]
            ));
            $user->save();
        }

        $this->currentUser = $user->toArray();
        return $this->currentUser;
    }

    public function getStats(){
        $user = $this->getUser();
        $stats = Stats::getByUniqueId($user["uuid"]);

        if($stats && strtotime($stats->toArray()["updated_at"])+43200 > time())
            return $stats->load("user")->toArray();

        $totalMiles = 0;
        $totalWaitTime = 0;
        $totalTripTime = 0;
        $cities = array();
        $products = array();
        $initialRequest = $this->get("v1.2/history?limit=50");
        $totalTrips = $initialRequest["count"];

        $requiredRounds = $totalTrips <= 50 ? 1 : ceil($totalTrips / 50);

        for($i = 0; $i < $requiredRounds; $i++){
            $offset = $i*50;
            $response = $i == 0 ? $initialRequest : $this->get("v1.2/history?limit=50&offset={$offset}");
            foreach($response["history"] as $trip){
                if($trip["status"] != "completed")
                    continue;

                $totalMiles += $trip["distance"];
                $totalWaitTime += $trip["start_time"] - $trip["request_time"];
                $totalTripTime += $trip["end_time"] - $trip["start_time"];

                if(!in_array($trip["start_city"]["display_name"],$cities))
                    array_push($cities,$trip["start_city"]["display_name"]);

                if(!isset($products[$trip["product_id"]]))
                    $products[$trip["product_id"]] = 0;

                $products[$trip["product_id"]] += 1;
            }
        }

        $output = array(
            "user" => $user,
            "count" => $initialRequest["count"],
            "totalKm" => $totalMiles * 1.60934,
            "totalWaitTime" => $totalWaitTime,
            "totalTime" => $totalTripTime,
            "cities" => $cities,
            "products" => $this->mapProducts($products)
        );

        if($stats) {
            $stats->totalKm = $output["totalKm"];
            $stats->totalWaitTime = $output["totalWaitTime"];
            $stats->totalTime = $output["totalTime"];
            $stats->cities = $output["cities"];
            $stats->products = $output["products"];
            $stats->totalTrips = $output["count"];
            $stats->save();
        }else{
            $stats = new Stats(array(
                'user_uuid' => $user["uuid"],
                'totalKm' => $output["totalKm"],
                'totalWaitTime' => $output["totalWaitTime"],
                'totalTime' => $output["totalTime"],
                'cities' => $output["cities"],
                'products' => $output["products"],
                'totalTrips' => $output["count"]
            ));
            $stats->save();
        }

        return $stats->load("user")->toArray();
    }


    /**
     * @param array $products
     * @returns array
     */
    private function mapProducts($products){
        $output = array();

        foreach($products as $product=>$times){
            try{
                $response = $this->get("v1/products/{$product}");
                if(!isset($output[$response["display_name"]]))
                    $output[$response["display_name"]] = array("count" => 0,"image" => $response["image"]);

                $output[$response["display_name"]]["count"] += $times;
            }catch(\Guzzle\Http\Exception\BadResponseException $e){
                if(!isset($output["Other"]))
                    $output["Other"] = array("count" => 0,"image" => null);

                $output["Other"]["count"] += $times;
            }

        }
        return $output;
    }

    public function getLoginLink(){
        return $this->api->getAuthorizeUri($this->context);
    }

    /**
     * @param string $returnUrl
     */
    public function login($returnUrl = "/"){
        if (false === $this->accessToken) {
            /* no valid access token available, go to authorization server */
            header("HTTP/1.1 302 Found");
            header("Location: " . $this->api->getAuthorizeUri($this->context));
            exit;
        }
        header("Location: {$returnUrl}");
        exit;
    }

    public function isLoggedIn(){
        return false !== $this->accessToken;
    }

    public function requireLogin(){
        if($this->isLoggedIn())
            return;

        header("Location: login");
        exit;
    }

    /**
     * @param array $request
     */
    public function callback($request){
        try {
            $this->callback->handleCallback($request);

            header("HTTP/1.1 302 Found");
            header("Location: /");
        } catch (AuthorizeException $e) {
            // this exception is thrown by Callback when the OAuth server returns a
            // specific error message for the client, e.g.: the user did not authorize
            // the request
            echo sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription());
        } catch (Exception $e) {
            // other error, these should never occur in the normal flow
            echo sprintf("ERROR: %s", $e->getMessage());
        }
    }
}
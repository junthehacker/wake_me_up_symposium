<?php
    include "include/haumea.php";
    include "include/classes.php";
    include "include/config.php";

    $app_data = new AppData();

    $app = new Haumea\App();
    $app->app_data = $app_data;
    $app->setJsonPrettyPrint(true);
    if(defined("VIRTUALMODE")){
        $app->setVirtualMode(true);
    };

    // Authenticate users
    $app->register("auth", function($args, $app){
        $headers = array_change_key_case($app->router->getRequestHeaders(), CASE_LOWER);
        if(!isset($headers["authtk"])){
            return array("401", "not authorized", null);
        } else {
            if($headers["authtk"] != USERPASSWORD){
                return array("401", "not authorized", null);
            }
        }
    });

    // Get all sessions
    $app->get("sessions", function($args, $app){
        $result = array();
        foreach($app->app_data->sessions as $session){
            $result[] = array(
                "id" => $session->id,
                "speaker" => $session->speaker,
                "title" => $session->title,
                "description" => $session->description
            );
        }
        return array(200, "ok", $result);
    });

    $app->get("statistics", array("auth", function($args, $app){

    }));

    $app->get("auth_tokens/%tokens", function($args, $app){
        if($args["tokens"] == USERPASSWORD){
            return array(200, "ok", null);
        } else {
            return array(404, "not found", null);
        }
    });



    $app->start();
?>
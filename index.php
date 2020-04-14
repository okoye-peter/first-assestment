<?php

require "router.php";

include "src/estimator.php";

route($_SERVER['REQUEST_URI'], function () {
    $start = time();

    $file = fopen('src/response.log', 'a+');

    $offset = strripos($_SERVER['REQUEST_URI'], '/') + 1;

    $dataType = strtolower(substr($_SERVER['REQUEST_URI'], $offset));

    if ($dataType == 'json' || $dataType == 'on-covid-19') {
        $data = json_decode(file_get_contents("php://input"));
        
    } elseif ($dataType == 'xml') {
        $data = simplexml_load_string(file_get_contents("php://input"));

    }

    $impact = [];

    $severeImpact = [];

    $end = time();

    covid19ImpactEstimator($data, $impact, $severeImpact);

    fwrite($file, strtoupper($_SERVER['REQUEST_METHOD']) . "   " . $_SERVER['REQUEST_URI'] . "    " . http_response_code() . "    " . ($end - $start) . " ms\n");

    fclose($file);
});


$action = $_SERVER['REQUEST_URI'];
dispatch($action);

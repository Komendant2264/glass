<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
include_once($_SERVER['DOCUMENT_ROOT']."/classes/dbConfig.php");
include_once($_SERVER['DOCUMENT_ROOT']."/classes/classUser.php" );
include_once($_SERVER['DOCUMENT_ROOT']."/classes/classOrder.php");
// custom Classes extend Base Classes
include_once __DIR__."/lib/classes/COrderApi.php";
global $dbConf;
include_once __DIR__ . "/lib/glassapi.php";
$api = new \GlassApi\GlassApi();
// Инициализация модулей
$error = false;

if (!empty($_GET['action'])){
    $action = $_GET['action'];
    $file = __DIR__."/lib/actions/$action.php";
    if(file_exists($file)) {
        include_once $file;
    } else {
        echo 'Error. No action file.';
        $error = true;
    }
} else {
    $error = true;
    echo 'Error. No action.';
}

if ($api->checkAuthorization() && !$error) {
    $actionClass = "\\GlassApi\\".$action;
    $actionDo = new $actionClass();
    $nextOffset = "";
    $nextLimit = "";

    //Обработка пагинации
    if(isset($_GET['limit']) && (int)$_GET['limit'] > 0)
    {
        $actionDo->limit = (int)$_GET['limit'];
    }
    if(isset($_GET['offset']) && (int)$_GET['offset'] > 0)
    {
        $actionDo->offset = (int)$_GET['offset'];
    }
    if(isset($actionDo->offset) && isset($actionDo->limit)){
        $nextLimit = "&limit=$actionDo->limit";
        $nextOffset="&offset=".($actionDo->offset+$actionDo->limit);
    }

    // Обработка фильтров

    if(isset($actionDo->filters) && is_array($actionDo->filters)) {
        $arrFilters = [
            'date_from',
            'date_to',
            'create_date_to',
            'create_date_from',
            'orderId'
        ];
        $filter = [];
        foreach ($_GET as $key=>$getParam)
        {
            if(in_array($key,$arrFilters))
            {
                $filter[$key] = $getParam;
            }
        }
        // Fix если не задан какой либо параметр по дате
        if(isset($filter['create_date_to']) && !isset($filter['create_date_from'])) $filter['create_date_from'] = $filter['create_date_to'];
        if(isset($filter['create_date_from']) && !isset($filter['create_date_to'])) $filter['create_date_to'] = $filter['create_date_from'];
        $actionDo->filters = $filter;
    }
    $result = $actionDo->executeAction();
    $result['next_page'] = "?action=".$action.$nextOffset.$nextLimit;
    $api->sendJsonAnswer($result);
} else {
    echo 'Error authorization';
}
session_abort();
die();

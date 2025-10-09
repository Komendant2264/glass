<?php
namespace GlassApi;

class GlassApi
{
    public $token;  // Токен авторизации
    public $defaultToken = 'KbgvcKvd2WZmI9obqk62LdBl9IFXArFgVvq2E1ph5exSUKTq';
    public $context;
    public $requestHeaders;
    public $requestBody;
    public $debug_mode = true;

    public function __construct()
    {
        $token = $this->defaultToken;
        $this->token = $token;
        $this->requestHeaders = getallheaders();
        $requestBody = file_get_contents('php://input');
        $this->requestBody = json_decode($requestBody, TRUE);
        if(isset($this->requestBody['debug_mode'])) $this->debugMode = $this->requestBody['debug_mode'];
    }

    public function checkAuthorization()
    {
        $tokenIsset = (key_exists('token',$this->requestHeaders) || key_exists('Token',$this->requestHeaders));
        if ($tokenIsset) $token = isset($this->requestHeaders['token'])?$this->requestHeaders['token']:$this->requestHeaders['Token'];
        if (empty($token)) return false;
        return ($token == $this->token);
    }


    public function sendJsonAnswer(array $arResult, string $status = "200")
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($arResult);
        die();
    }

    public function sendJsonError(array $arError, string $status = "401")
    {
        if ($this->debugMode) {
            \Bitrix\Main\Diag\Debug::dumpToFile(
                [
                    'time' => ConvertTimeStamp(time(), "FULL", "ru"),
                    'errors' => $arError,
                    'status' => $status,
                    'requestBody' => json_encode($this->requestBody),
                    'debug' => debug_backtrace(2)
                ],
                '------------------Error--' . ConvertTimeStamp(time(), "FULL", "ru") . '------------------',
                '/.debug/api/api_errors.log'
            );
            $arError['debug'] = debug_backtrace();
        }
        $this->sendJsonAnswer($arError, $status);
    }
}
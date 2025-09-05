<?php

declare(strict_types=1);

namespace GlassApi\Rest\Classes;

/**
 *
 */
class Request
{
    private $context;
    private $request;
    private $arRequest;

    public function __construct($context, $request)
    {
        $this->context = $context;
        $this->request = $request;

        if (empty($request->toArray())) {
            if (!empty($params = json_decode(file_get_contents("php://input"), true))) {
                $arRequest = $this->validation($params);
            } else if (!empty($_REQUEST)){
                $arRequest = $this->validation($_REQUEST);
            } else {
                $arRequest = [];
            }
        } else {
            $arRequest = $this->validation($request->toArray());
        }

        $this->arRequest = $arRequest;
    }

    /**
     * @return HttpContext|Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \Bitrix\Main\Request|HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getArRequest(): array
    {
        return $this->arRequest;
    }

    /**
     * @param array $arRequest
     * @return array
     */
    private function validation(array $arRequest): array
    {
        $result = [];

        if (empty($arRequest))
            return $result;

        foreach ($arRequest as $name => $value) {
            if (is_string($value)) {
                if ($this->isJson($value)) {
                    $result[htmlspecialchars("{$name}")] = json_decode($value, true);
                } else {
                    $result[htmlspecialchars("{$name}")] = htmlspecialchars($value);
                }
            } elseif (is_int($value)) {
                $result[htmlspecialchars("{$name}")] = intval($value);
            } elseif (is_float($value)) {
                $result[htmlspecialchars("{$name}")] = floatval($value);
            } elseif (is_array($value)) {
                $result[htmlspecialchars("{$name}")] = $this->validation($value);
            }
        }
        return $result;
    }

    /**
     * @param $string
     * @return bool
     */
    private function isJson($string)
    {
        // Пробуем декодировать строку
        $decoded = json_decode($string);

        // Проверяем, успешно ли прошла декодировка
        return json_last_error() === JSON_ERROR_NONE && !is_null($decoded);
    }
}
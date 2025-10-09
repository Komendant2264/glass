<?php
namespace GlassApi;
use \App\Services\GoodsTypesService;
use \App\Services\GoodsService;
use \App\Services\BrandService;
class getProducts extends GlassApi
{
    public $limit = 100;
    public $offset = 0;
    public function executeAction(){
        $goodsTypesService = new GoodsTypesService();
        $goodsService = new GoodsService();
        $brandService = new BrandService();
        $items = $goodsService->getByParent([
                'parent' => null,
//                'sort' => [
//                    [
//                        "column"=>"id",
//                        "direction" => "asc"
//                    ]
//                ],
                'search' => "",
                'limit' => $this->limit,
                'offset' => $this->offset
            ]
        )->get();
        // Типы товаров
        $arrProductTypes = [];
        foreach ($goodsTypesService->getTree([],true) as $type){
            $arrProductTypes[$type['id']] = $type;
        }

        // Бренды
        $arrBrands = [];
        foreach ($brandService->getAll() as $brand)
        {
            $arrBrands[$brand->id] = $brand->key;
        }
        // Массив товаров
        $arrProducts = [];
        foreach ($items as $item)
        {
            // Получаем общий массив товара
            $arrProduct = [
                "id" => $item->id,
                "type" => [
                    "id" => $arrProductTypes[$item->fk_type]['id'],
                    "name" => $arrProductTypes[$item->fk_type]['name']
                ],
                'manufacturer' => $item-> fk_manufacturer,
                'brand' => [
                    "id" => !empty($item->fk_brand)?$item->fk_brand:"",
                    "name" => !empty($item->fk_brand)?$arrBrands[$item->fk_brand]:""
                ],
                'full_name' => $item-> full_name,
                'short_name' => $item-> short_name,
                'article' => $item-> article,
                'eurocode' => $item-> eurocode,
                'short_eurocode' => $item-> short_eurocode,
                'scancode' => $item-> scancode,
            ];
            $arrProducts[] = $arrProduct;
        }
        return array(
            'products' => $arrProducts
        );
    }
}
<?php
/**
 * 트리거 필드데이터를 레시피에 전달 합니다.
 * 앱스토어 > 레시피관리 > 채널관리 에서 트리거 데이터 전달 URL 을 설정할 수 있습니다.

 */
require_once __DIR__ . '/../vendor/autoload.php';

use Recipe\Recipe;

$recipe = new Recipe();

// 트리거 필드 정보 리턴
$url = __DIR__ . '/json/trigger.json';

/*
 * 트리거 필드 데이터 양식 샘플
{
    "fields": [
        {
            "data_type": "number",
            "type": "select",
            "required": true,
            "dynamic": true,
            "label": "쇼핑몰",
            "name": "shop_no"
        },
        {
            "data_type": "string",
            "label": "상품명",
            "name": "product_name"
        },
        {
            "data_type": "string",
            "type": "select",
            "dynamic": true,
            "label": "상품분류",
            "name": "collection"
        },
        {
            "data_type": "string",
            "type": "select",
            "label": "진열상태",
            "name": "display",
            "options": [
                {"label": "진열함", "value": "T"},
                {"label": "진열안함", "value": "F"}
            ]
        },
        ...
    ],
    "ingredients": [
        {
            "label": "상품명",
            "name": "product_name",
            "description": ""
        }
        {
            "label": "소비자가",
            "name": "retail_price",
            "description": "1000"
        }
    ],
    "meta": {
        "data_selector_field": "shop_no"
    }
}
 */
echo $recipe->getTriggerData($url);

// 결과 리턴
echo json_encode($result);

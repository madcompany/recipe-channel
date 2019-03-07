<?php
/**
 * 엑션 필드데이터를 레시피에 전달 합니다.
 * 앱스토어 > 레시피관리 > 채널관리 에서 엑션 필드 전달 URL 을 설정할 수 있습니다.

 */
require_once __DIR__ . '/../vendor/autoload.php';

use Recipe\Recipe;

$recipe = new Recipe();

// 엑션 필드 정보 리턴
$url = __DIR__ . '/json/action.json';
/*
 * 엑션 필드 데이터 양식 샘플
{
    "fields": [
        {
            "type": "string",
            "required": true,
            "label": "상품명",
            "name": "product_name",
            "placeholder": "상품명을 입력하세요."
        },
        {
            "type": "string",
            "type": "select",
            "label": "상품분류",
            "name": "collection",
            "dynamic": true
        },
        ...
    ],
    "ingredients": [
        {
            "label": "신규 상품 번호",
            "name": "product_no",
            "description": "새로 등록된 상품의 번호입니다."
        }
    ]
}
*/

echo $recipe->getActionData($url);

// 결과 리턴
echo json_encode($result);

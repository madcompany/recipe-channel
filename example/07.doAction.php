<?php
/**
 * 엑션 실행
 * 앱스토어 > 레시피관리 > 채널관리 에서 액션에 사용하는 URL 을 설정할 수 있습니다.
 *
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Recipe\Recipe;

$recipe = new Recipe();

$data = $_POST;
/*
    {
        "product_name": "상품명",
        "category_no": "1"
    }
 */
//Access Token 로드 - 엑션 처리시 사용
$access_token = $recipe->getBearerToken();

// 엑션처리
$result = doAction($access_token, $data);


// 재료 반환 데이터 생성
/* 재료 반환 데이터 샘플
{
    "data": [
    {
        "product_code": "P00000N",
        "product_no": 100
    }
  ]
}
*/

// 기본 재료 (엑션 데이터 재료 ID 를 key 로 사용한다. - 날짜, 시간 등
$ingredients = $recipe->makeIngredients();

echo json_encode($ingredients);


function doAction()
{
    //some Action
    return ;
}
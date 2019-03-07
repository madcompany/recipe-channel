<?php
/**
 * 트리거 동적 필드데이터를 레시피에 전달 합니다.
 * 앱스토어 > 레시피관리 > 채널관리 에서 트리거 동적 필드 데이터 전달 URL 을 설정할 수 있습니다.
 *
 * 해당 필드는 dynamic 을 true 로 설정하여야 합니다.
 * 요청은 POST 로 전달되면 동적 필드의 데이터를 로드하여 전달하셔야 합니다.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Cafe24corp\Recipe;

$recipe = new Recipe();

// 동적 필드 데이터 요청
/*
{
    "data": [
        {
            "name": "collection"
        }
        ...
    ]
}
*/
$data = $_POST['data'];

$dynamicFieldData = [];

for ($i = 0 ; $i < count($data) ; $i++){
    $name = $data[$i]['name'];

    //동적 데이터 생성
    $dynamicFieldData = array_push ( $dynamicFieldData, doGetDynamicFieldData($name) );

}

// 레시피로 전달
/* 전달 데이터 샘플
{
    "data": [
        {
            "name": "collection",
            "options": [
                {
                    "label": "Recommend Products",
                    "value": "123"
                },
                {
                    "label": "Hit Products",
                    "value": "456"
                },
                ...
            ]
        },
        ...
    ]
}
*/

// 결과 리턴
echo json_encode(['data' => $dynamicFieldData ]);

//동적 데이터 생성
function doGetDynamicFieldData($name){
}
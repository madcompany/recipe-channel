<?php
/**
 * 트리거 이벤트 전달
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Recipe\Recipe;
use Recipe\Exception;

$recipe = new Recipe();

try {
    // 트리거 아이디
    $trigger_id = 100010;

    // 채널측 유저 아이디
    $user_id = 'jylee08'; //필요하지 않을 경우 전달하지 않아도 된다.

    // 트리거 데이터 생성 (전달할 갯수만큼 루프
    $triggerData = [];

    for ($i = 0 ; $i < 1 ; $i++)
    {
        //데이터 예시 - 트리거 필드에 설정된 ID 값으로 전달 한다.
        $value = [
            'id'        => 'id',
            'message'   => 'message'
        ];

        $triggerData[] = $recipe->makeTriggerData($trigger_id, $value, $user_id);
    }

    // 트리거 이벤트 레시피로 전달
    $recipe->sendTriggerEvent($triggerData);

}catch(Exception\RecipeException $e){

    echo $e->getMessage();
    exit;
}

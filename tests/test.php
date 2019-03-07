<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Recipe\Recipe;
use Recipe\Exception;

$recipe = new Recipe();


try {
    //채널 사용자 ID별 활성 트리거 변경 알림
    $data = $_POST['data'];

    $result = [];

    for ($i = 0 ; $i < count($data) ; $i++) {
        $user_id = $data[$i]['user_id'];

        $triggerData = $recipe->getActiveTriggerList($user_id);

        // 채널 측에 결과 저장
        $result[] = ['result' => true];
    }

    // 결과 리턴
    echo json_encode($result);


    // 트리거 필드 정보 리턴
    $url = __DIR__ . '/json/trigger.json';
    echo $recipe->getTriggerData($url);

    // 트리거 이벤트 전달

    // 트리거 아이디
    $trigger_id = 100010;

    // 채널측 유저 아이디
    $user_id = 'jylee08'; //필요하지 않을 경우 전달하지 않아도 된다.

    // 트리거 데이터 생성 (전달할 갯수만큼 루프

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

    // 엑션 필드 로드
    $url = __DIR__ . '/json/action.json';
    echo $recipe->getActionData($url);

    //Access Token 로드 - 엑션 처리시 사용
    $access_token = $recipe->getBearerToken();

    // 엑션처리
    // runAction();

    // 재료 반환 데이터 생성
    // 기본 재료 (엑션 데이터 재료 ID 를 key 로 사용한다.
    $ingredients = $recipe->makeIngredients();

    echo json_encode($ingredients);



}catch(Exception\RecipeException $e){

    echo $e->getMessage();
    exit;
}


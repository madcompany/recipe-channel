<?php
/**
 * 각 채널에서 채널 사용자별로 "카페24 Recipe"에 전달해야 하는
 * 트리거 이벤트 리스트가 변경되었다는 알림을 받기 위한 프로토콜입니다.
 *
 * 변경알림을 전달 받으면 전달받은 유저아이디로
 * 유저별 트리거 리스트를 검색하여 결과 값을 채널쪽에 저장합니다.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Cafe24corp\Recipe;

$recipe = new Recipe();

//채널 사용자 ID별 활성 트리거 변경 알림
$data = $_POST['data'];

/*
 * POST 데이터 샘플
{
    "data": [
        {
            "user_id": "userid1"
        },
        {
            "user_id": "userid2"
        },
        ...
    ]
}
 */

$result = [];

for ($i = 0 ; $i < count($data) ; $i++) {
    $user_id = $data[$i]['user_id'];

    $triggerData = $recipe->getActiveTriggerList($user_id);

    /*
     * 응답 데이터 샘플
     {
        "triggers": [
            100001,
            100002,
            100004
        ]
    }
     */

    // 채널 측에 결과 저장
    // doStoreTrigger()

    $result[] = ['result' => true];
}

// 결과 리턴
echo json_encode($result);


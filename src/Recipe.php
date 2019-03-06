<?php

namespace Recipe;

use Recipe\Exception\RecipeException;
/**
 * Class Recipe
 *
 * @package Recipe
 */
class Recipe {

    const VERSION = '1.0';

    /**
     * @const string The name of the environment variable that contains the channel ID.
     */
    const CHANNEL_ID_ENV_NAME = 'RECIPE_CHANNEL_ID';

    /**
     * @const string The name of the environment variable that contains the channel secret.
     */
    const CHANNEL_SECRET_ENV_NAME = 'RECIPE_CHANNEL_SECRET';

    const BASE_URL_NAME = 'RECIPE_BASE_URL';

    const CONNECT_TIME_OUT = 'RECIPE_CONNECT_TIME_OUT';

    private $CHANNEL_NO = NULL;

    protected $client;

    public function __construct($config)
    {

        $this->CHANNEL_NO = getenv('CHANNEL_NO');

        $config = array_merge([
            'channel_id' => getenv(static::CHANNEL_ID_ENV_NAME),
            'channel_secret' => getenv(static::CHANNEL_SECRET_ENV_NAME),
            'base_url' => getenv(static::BASE_URL_NAME),
            'connect_timeout' => getenv(static::CONNECT_TIME_OUT)
        ], $config);

        $this->client = new ApiRequest($config);
    }

    /**
     *
     * @param $uri
     * @return array
     * @throws RecipeException
     */
    public function get($uri)
    {
        return $this->client->get($uri);
    }

    /**
     * @param $uri
     * @param $params
     * @return array
     * @throws RecipeException
     */
    public function post($uri, $params )
    {
        return $this->client->post($uri, $params);
    }

    /**
     * 채널 사용자 ID별 활성 트리거 변경 알림
     * @return false|string
     * @throws RecipeException
     */
    public function notifyChangedActiveTrigger()
    {

        $data = $_POST['data'];

        if ( empty($data) ) {
            throw new RecipeException('파라미터 정보가 올바르지 않습니다. ', 404);
        }

        //유저 ID 별 활성 트리거 목록 로드
        $result = [];

        for($i = 0 ; $i < count($data); $i++){

            $user_id = $data[$i]['user_id'];

            $triggerData = $this->client->getActiveTriggerList($this->CHANNEL_NO , $user_id);

            $result[] = $triggerData;
        }

        return json_encode($result);
    }

    /**
     * 트리거 이벤트 전달
     * @param $trigger_id
     * @param $channel_user_id
     * @param $value
     * @return array
     * @throws RecipeException
     */
    public function sendTriggerEvent($trigger_id, $channel_user_id, $value)
    {
        //트리거 전달 데이터 생성

        $params = $this->makeTriggerEvent($trigger_id, $channel_user_id, $value);

        //레시피로 전달
        return $this->client->post('events', $params);
    }

    /**
     * 트리거 전달 데이터 생성
     * @param $trigger_id
     * @param $channel_user_id
     * @param $value
     * @return array
     */
    private function makeTriggerEvent($trigger_id, $channel_user_id, $value)
    {
        $events = []; //이벤트

        $events[0]['meta'] = [
            'user_id'       => $channel_user_id, //계정맵핑이 필요한경우
            'channel_id'    => $this->CHANNEL_NO, //채널 고유 코드
            'trigger_id'    =>  $trigger_id,//트리거 고유번호
            'timestamp'     => time(),
            'trace_id'      => $this->getTraceId()
        ];

        $events[0]['data'] = $value;

        return ['events' => $events];
    }

    /**
     * 액션 결과 생성
     * @param $response
     * @return array
     */
    public function makeActionResult($response){

        return ['data' => $response];
    }

    /**
     * 로그 추적용 ID
     * @return string
     */
    private function getTraceId()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * 트리거 데이터 가져오기
     * @param $url
     * @return mixed
     */
    public function getTriggerData($url)
    {
        return json_decode(file_get_contents($url));
    }

}
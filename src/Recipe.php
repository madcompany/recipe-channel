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

    /**
     * @const string The name of the environment variable that contains the base url.
     */
    const BASE_URL_NAME = 'RECIPE_BASE_URL';

    /**
     * @const string The name of the environment variable that contains the http timeout time.
     */
    const CONNECT_TIME_OUT = 'RECIPE_CONNECT_TIME_OUT';

    /**
     * @const string The name of the environment variable that contains the channel number.
     */
    private $CHANNEL_NO = NULL;

    /**
     * @const string The name of the environment variable that contains default time zone.
     */
    private $TIME_ZONE;

    /**
     * @const string The name of the environment variable that contains the guzzle http client.
     */
    protected $client;

    public function __construct($config = [])
    {

        $this->TIME_ZONE = getenv('APP_TIMEZONE') ?? "Asia/Seoul";
        $this->CHANNEL_NO = getenv('CHANNEL_NO');

        $config = array_merge([
            'channel_id' => getenv(static::CHANNEL_ID_ENV_NAME),
            'channel_secret' => getenv(static::CHANNEL_SECRET_ENV_NAME),
            'base_url' => getenv(static::BASE_URL_NAME),
            'connect_timeout' => getenv(static::CONNECT_TIME_OUT) ?? 30
        ], $config);

        $this->client = new ApiRequest($config);
    }

    /**
     * 채널 사용자 ID별 활성 트리거 변경 알림
     * @return array
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

        return $result;
    }

    /**
     * 유저별 트리거 리스트 검색
     * @param $user_id
     * @return array
     * @throws RecipeException
     */
    public function getActiveTriggerList($user_id)
    {
        return $this->client->getActiveTriggerList($this->CHANNEL_NO , $user_id);
    }

    /**
     * 트리거 이벤트 전달
     * @param $params
     * @return array
     * @throws RecipeException
     */
    public function sendTriggerEvent($params)
    {
        //레시피로 전달
        return $this->client->post('events', $params);
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

    /**
     * 액션 데이터 가져오기
     * @param $url
     * @return mixed
     */
    public function getActionData($url)
    {
        return json_decode(file_get_contents($url));
    }

    /**
     * 트리거 전달 데이터 생성
     * @param $trigger_id
     * @param array $data 전달 데이터
     * @param string $user_id 채널 유저 아이디
     * @param string $data_url  데이터 URL
     * @param array $selectable_data 선택 가능 데이터
     * @param array $selectable_data_url 선택 가능 데이터 URL
     * @return array
     */
    public function makeTriggerData($trigger_id, $data = [], $user_id = '', $data_url = '', $selectable_data = [], $selectable_data_url = [])
    {
        $events = []; //이벤트

        $events['meta'] = [
            'user_id'       => $user_id, //계정맵핑이 필요한경우
            'channel_id'    => $this->CHANNEL_NO, //채널 고유 코드
            'trigger_id'    =>  $trigger_id,//트리거 고유번호
            'timestamp'     => time(),
            'trace_id'      => $this->getTraceId()
        ];

        $events['data'] = $data;
        if (isset($data_url)) $events['data_url'] = $data_url;
        if (isset($selectable_data)) $events['selectable_data'] = $selectable_data;
        if (isset($selectable_data_url)) $events['selectable_data_url'] = $selectable_data_url;

        return ['events' => $events];
    }

    /**
     * 반환 재료데이터 생성
     * @return array
     */
    public function makeIngredients(){
        //시간재료
        $timeInfo = $this->makeTimeInfo(time());

        $result = [ //반환 재료
            'date_str' => $timeInfo['date_str'],
            'date_mark' => $timeInfo['date_mark'],
            'year' => $timeInfo['year'],
            'month' => $timeInfo['month'],
            'day' => $timeInfo['day'],
            'week' => $timeInfo['week'],
            'time_str' => $timeInfo['time_str'],
            'time_mark' => $timeInfo['time_mark'],
            'hour' => $timeInfo['hour'],
            'minute' => $timeInfo['minute'],
            'second' => $timeInfo['second'],
        ];

        return $result;
    }

    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    function makeTimeInfo(int $microsecond)
    {
        $timezone = new \DateTimeZone($this->TIME_ZONE);

        try {
            $date = new \DateTime(date('Y-m-d H:i:s', $microsecond), $timezone);

            $week = ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'];
            $hourFormat = ['AM' => '오전', 'PM' => '오후'];

            $timeInfo = [
                'date_str' => $date->format("Y년 m월 d일"),
                'date_mark' => $date->format("Y-m-d"),
                'year' => $date->format('Y'),
                'month' => $date->format('m'),
                'day' => $date->format('d'),
                'week' => $week[$date->format('w')],
                'time_str' => $hourFormat[$date->format('A')] . ' ' . $date->format("H시 i분"),
                'time_mark' => $date->format('H:i:s A'),
                'hour' => $date->format('H'),
                'minute' => $date->format('i'),
                'second' => $date->format('s')
            ];

            return $timeInfo;
        }catch(\Exception $e){
            echo $e->getMessage();
            exit;
        }
    }

}
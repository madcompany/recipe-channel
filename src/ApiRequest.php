<?php

namespace Cafe24corp;

use Cafe24corp\Exception\RecipeException;
use \GuzzleHttp\Client;

use \GuzzleHttp\Exception\ClientException;
use \GuzzleHttp\Exception\GuzzleException;
/**
 * Class ApiRequest
 * 외부 api 호출용
 * API Request Library
 * @package App\Http\Libraries
 */
class ApiRequest
{
    private $client;

    private $headers = [];
    private $options = [];

    public function __construct($config)
    {
        $this->client = new Client([$config['base_url'], 'connect_timeout' => $config['connect_timeout'] ?? '3.0']);

        $this->options['debug'] = false;

        //기본 헤더
        $this->headers['Accept'] = 'application/json';
        $this->headers['Content-Type'] = 'application/json';

        $this->options['auth'] = [$config['channel_id'], $config['channel_secret']]; //Basic Auth
    }

    /**
     * GET 방식 호출
     * @param string $uri
     * @param array $params
     * @return array
     * @throws RecipeException
     */
    public function get(string $uri, $params = [])
    {
        try{
            $this->options['query'] = $params;

            $result = $this->client->request('GET', $uri, $this->options);

            return ['result' => true, 'data' => $result->getBody()->getContents(), 'message' => '정상적으로 처리되었습니다. '];

        }catch (ClientException $e){
            throw new RecipeException($e->getMessage(), 500);
        }catch (GuzzleException $e) {
            throw new RecipeException($e->getMessage(), 500);
        }

    }


    /**
     * POST 방식 호출
     * @param string $uri
     * @param array $params
     * @return array
     * @throws RecipeException
     */
    public function post(string $uri, $params = [])
    {

        try{

            $this->options['headers'] = $this->headers;

            $this->options['json'] = $params;

            $result = $this->client->request('POST', $uri, $this->options);

            $statusCode = $result->getStatusCode();
            if($statusCode != 200){
                throw new RecipeException('전송 중 오류가 발생하였습니다. ', 500);
            }

            return ['result' => true, 'data' => $result->getBody()->getContents(), 'message' => '정상적으로 처리되었습니다. '];

        }
        catch (RecipeException $e){
            throw new RecipeException( '전송중 오류가 발생하였습니다. ('.$e->getMessage().')', 500);
        }catch (GuzzleException $e) {
            throw new RecipeException( '전송중 오류가 발생하였습니다. ('.$e->getMessage().')', 500);
        }

    }

    /**
     * PUT 방식 호출
     * @param string $uri
     * @param array $params
     * @param string $auth
     * @param null $token
     * @return array
     */
    public function put(string $uri, $params = [], $auth = 'basic', $token = null)
    {

        try{
            if($auth == 'bearer'){ //Bearer Auth
                $this->headers['Authorization'] = 'Bearer '  . $token;
            }else{ //Basic Auth
                $this->options['auth'] = [config('recipe.CLIENT_ID'), config('recipe.CLIENT_SECRET')];
            }

            $this->options['headers'] = $this->headers;

            $this->options['json'] = $params;

            $result = $this->client->request('PUT', $uri, $this->options);

            $statusCode = $result->getStatusCode();
            if($statusCode != 200){
                throw new \Exception('전송 중 오류가 발생하였습니다. ');
            }

            return ['result' => true, 'data' => $result->getBody()->getContents(), 'message' => '정상적으로 처리되었습니다. '];

        }
        catch (\Exception $e){
            return ['result' => false, 'message' => '전송중 오류가 발생하였습니다. ('.$e->getMessage().')'];
        }catch (GuzzleException $e) {

            return ['result' => false , 'message' => '전송중 오류가 발생하였습니다. ('.$e->getMessage().')'];
        }

    }

    /**
     * DELETE 방식 호출
     * Basic Auth
     * @param string $uri
     * @param array $params
     * @return array
     */
    public function delete(string $uri, $params = [])
    {
        try{
            $this->options['query'] = $params;
            $this->options['auth'] = [config('recipe.CLIENT_ID'), config('recipe.CLIENT_SECRET')]; //Basic Auth

            $result = $this->client->request('DELETE', $uri, $this->options);

            return ['result' => true, 'data' => $result->getBody()->getContents(), 'message' => '정상적으로 처리되었습니다. '];

        }catch (ClientException $e){

            return ['result' => false , 'message' => '전송중 오류가 발생하였습니다. ('.$e->getMessage().')'];
        }catch (GuzzleException $e) {

            return ['result' => false , 'message' => '전송중 오류가 발생하였습니다. ('.$e->getMessage().')'];
        }

    }

    /**
     * 최신 활성 트리거 리스트 가져와서 갱신
     * @param $channel_no
     * @param $user_id
     * @return array
     * @throws RecipeException
     */
    public function getActiveTriggerList($channel_no, $user_id){

        $uri = 'channels/'.$channel_no.'/channel-users/'.$user_id.'/triggers';
        $result = $this->get($uri);

        return $result;
    }

    /**
     * 액션 결과 생성
     * @param $response
     * @return array
     */
    public function makeActionResult($response){

        return ['data' => $response];
    }

}


# Using Recipe Channel API

- [Installation](#installation)
- [Usage](#usage)
- [Documentation](#documentation)

레시피 서비스의 채널 제작을 위하여 필요한 API 기능을 제공합니다. 

## Installation

Recipe Channel API is available on Packagist ([recipe/channel](http://packagist.org/packages/recipe/channel))
and as such installable via [Composer](http://getcomposer.org/).

```bash
composer require recipe/channel
```

If you do not use Composer, you can grab the code from GitHub, and use any
PSR-0 compatible autoloader (e.g. the [Symfony2 ClassLoader component](https://github.com/symfony/ClassLoader))
to load Recipe Channel API classes.

## Usage

### 0. Enviroment variables

레시피 채널 API 를 이용하기 위해서는 환경설정 변수를 설정하셔야 합니다. 
Apache / Nginx 의 환경변수를 이용하거나 .env (https://github.com/symfony/dotenv) 등을 사용하세요.
CHANNEL_NO, CHANNEL_ID, CHANNEL_SECRET 는 개발자센터 (https://developer.cafe24.com) 에서 발급 받으실 수 있습니다.
레시피 API 요청 시 필요합니다.  

#### 0.1. Valiables

| Fields | 설명 | 비고 |
|:-------|:-----|:-----|
|RECIPE_BASE_URL | 레시피 API 주소 | |
|RECIPE_CHANNEL_NO | 발급받은 Channel Number | |
|RECIPE_CHANNEL_ID | 발급받은 Channel ID | |
|RECIPE_CHANNEL_SECRET | 발급받은 Channel Secret | |
|RECIPE_CONNECT_TIME_OUT | 기본 API timeout time | 기본값 30초 |
|APP_TIMEZONE | 앱의 기본 Timezone | 기본값 Asia/Seoul |

### 1. Trigger Fields

```php
<?php

use Recipe\Recipe;

$recipe = new Recipe();

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

$result = $recipe->getTriggerData($url);

// 결과 리턴
echo json_encode($result);

```

### 2. Dynamic Trigger Fields

```php
<?php
/**
 * 트리거 동적 필드데이터를 레시피에 전달 합니다.
 * 앱스토어 > 레시피관리 > 채널관리 에서 트리거 동적 필드 데이터 전달 URL 을 설정할 수 있습니다.
 *
 * 해당 필드는 dynamic 을 true 로 설정하여야 합니다.
 * 요청은 POST 로 전달되면 동적 필드의 데이터를 로드하여 전달하셔야 합니다.
 */

use Recipe\Recipe;

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


```

### 3. Notify Changed Actived Trigger

```php
<?php
/**
 * 각 채널에서 채널 사용자별로 "카페24 Recipe"에 전달해야 하는
 * 트리거 이벤트 리스트가 변경되었다는 알림을 받기 위한 프로토콜입니다.
 *
 * 변경알림을 전달 받으면 전달받은 유저아이디로
 * 유저별 트리거 리스트를 검색하여 결과 값을 채널쪽에 저장합니다.
 */

use Recipe\Recipe;
use Recipe\Exception;

$recipe = new Recipe();

try {
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


}catch(Exception\RecipeException $e){

    echo $e->getMessage();
    exit;
}


```

### 4. Send Trigger Event

```php
<?php
/**
 * 트리거 이벤트 전달
 */

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

```

### 5. Action Fields

```php
<?php

use Recipe\Recipe;

$recipe = new Recipe();

// 엑션 필드 정보 리턴
$url = __DIR__ . '/json/action.json';


echo $recipe->getActionData($url);

// 결과 리턴
echo json_encode($result);

```

### 6. Dynamic Action Fields

```php
<?php

use Recipe\Recipe;

$recipe = new Recipe();

// 동적 필드 데이터 요청
$data = $_POST['data'];

$dynamicFieldData = [];

for ($i = 0 ; $i < count($data) ; $i++){
    $name = $data[$i]['name'];

    //동적 데이터 생성
    $dynamicFieldData = array_push ( $dynamicFieldData, doGetDynamicFieldData($name) );

}

// 결과 리턴
echo json_encode(['data' => $dynamicFieldData ]);

```

### 7. Do Action

```php
<?php
/**
 * 엑션 실행
 * 앱스토어 > 레시피관리 > 채널관리 에서 액션에 사용하는 URL 을 설정할 수 있습니다.
 *
 */

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
// $result = doAction($access_token, $data);


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

```

## Documentation

### 채널 API 프로토콜

* [Recipe 채널 API 프로토콜 - 공통 규약](https://wiki.simplexi.com/pages/viewpage.action?pageId=1086099666)
* [Recipe 채널 API 프로토콜 - 채널 계정 연결](https://wiki.simplexi.com/pages/viewpage.action?pageId=1084232802)
* [Recipe 채널 API 프로토콜 - 레시피 생성 - 트리거](https://wiki.simplexi.com/pages/viewpage.action?pageId=1084232803)
* [Recipe 채널 API 프로토콜 - 트리거 이벤트 전달](https://wiki.simplexi.com/pages/viewpage.action?pageId=1084232805)
* [Recipe 채널 API 프로토콜 - 레시피 생성 - 액션](https://wiki.simplexi.com/pages/viewpage.action?pageId=1084232804)
* [Recipe 채널 API 프로토콜 - 액션 API](https://wiki.simplexi.com/pages/viewpage.action?pageId=1084232806)
* [Recipe 채널 API 프로토콜 - 서비스 상태](https://wiki.simplexi.com/pages/viewpage.action?pageId=1084232807)

# Using Recipe Channel API

- [Installation](#installation)
- [Trigger Field API](#trigger-field-api)
- [Documentation](#documentation)


## Installation

Recipe Channel API is available on Packagist ([recipe/channel](http://packagist.org/packages/recipe/channel))
and as such installable via [Composer](http://getcomposer.org/).

```bash
composer require recipe/channel
```

If you do not use Composer, you can grab the code from GitHub, and use any
PSR-0 compatible autoloader (e.g. the [Symfony2 ClassLoader component](https://github.com/symfony/ClassLoader))
to load Recipe Channel API classes.

## Trigger Field API

###1. 개요

Recipe 서비스 각 채널에서 준수해야 할 레시피 생성시 트리거 조건 입력과 관련된 프로토콜입니다.
이 프로토콜은 Recipe 서비스에서 레시피를 등록시 트리거 조건 입력폼을 만들고 입력한 데이터를 검증 할 때 사용됩니다.

###2. 프로토콜 상세

| Method | GET  |
---------|:------|
| URL | 카페24 개발자 센터 레시피 관리에서 등록 할 수 있습니다. |
| Headers | Accept: application/json|           
|         |Accept-Charset: utf-8|
|         |Accept-Encoding: gzip, deflate|
|         |accept-language: {{language_code}}|
|         |Content-Type: application/json|
|         |X-Request-ID: {{random_uuid}} |

####2.1. Example
```bash
4.1.1.2. Example
GET {{trigger_field_api_url}} HTTP/1.1
Host: api.app.com
 
Accept: application/json
Accept-Charset: utf-8
Accept-Encoding: gzip, deflate
accept-language: {{language_code}}
X-Request-ID: {{random_uuid}}
```

###3. Response

####3.1. HTTP 

| Status | 200 |
|-------|-------|
| Header | Content-Type application/json; charset=utf-8 |

####3.2. body

| 항목 | 설명 | 필수여부 |
|:-----|:----|:---------|
|fields|필드 리스트|Required|
|fields[].label|항목 라벨명|Required|
|fields[].name|항목 필드명|Required|
|fields[].data_type|데이터 타입 ('string','number','date') |  |
|                   |(기본값 : 'string')                   ||
|fields[].type| HTML input 타입 ('text','select') ||
|               |    (기본값 : 'text')       |         |
|fields[].default_value|기본값||
|fields[].default_operator|기본 필터링 연산 방법||
|       | eq : 일치 (Equal)      |       |
|       | neq : 비일치 (Not Equal)      |       |
|       | like : 포함 (Like)      |       |
|       | nlike : 미포함 (Not Like)      |       |
|       | ge : 이상 (Greater then or Equal)      |       |
|       | le : 이하 (Less then or Equal)      |       |
|       | gt : 초과 (Greater then)      |       |
|       |  lt : 미만 (Less then)     |       |
|fields[].dynamic|동적 옵션 여부 (true,false)||
|   |   (기본값 : false) | |
|fields[].display| 기본 노출 할 입력항목 여부 (true, false) ||
|   |  (기본값 : false) |   |
|fields[].required|필수입력 여부 (true, false)||
|   |  (기본값 : false) |   |
|fields[].placeholder|힌트 (값의 '예' 또는 '짤막한 설명')||
|fields[].options|정적 옵션||
|fields[].options[].label|옵션 라벨	||
|fields[].options[].value| 옵션 값||
|ingredients|재료 리스트 배열||
|ingredients[].label|항목 라벨||
|ingredients[].name|항목 필드명|required|
|ingredients[].description|항목 상세 설명 (또는 값 예)|required|
|meta.data_selector_field|이벤트 데이터 선택용 필드명||

####3.3. Example

```bash
HTTP/1.1 200 OKContent-Type: application/json; charset=utf-8
  
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
```

###4. Trigger Field API 사용방법

```php

<?php
/**
 * 트리거 필드데이터를 레시피에 전달 합니다.
 * 앱스토어 > 레시피관리 > 채널관리 에서 트리거 데이터 전달 URL 을 설정할 수 있습니다.

 */
require_once __DIR__ . '/../vendor/autoload.php';

use Recipe\Recipe;

$recipe = new Recipe();

// 트리거 필드 정보 리턴
$url = __DIR__ . '/json/trigger.json';

echo $recipe->getTriggerData($url);

// 결과 리턴
echo json_encode($result);

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

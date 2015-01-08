<?php

require_once('../lib/request/TingClientRequest.php');
#module_load_include('php', 'ting-client', '/lib/request/TingClientRequest');

abstract class TingClientRequestTester extends TingClientRequest {
  public static function getBadgerFishValueExposed($badgerFishObject, $valueName) {
    return self::getBadgerFishValue($badgerFishObject, $valueName);
  }

  public static function getAttributeValueExposed($object, $attributeName) {
    return self::getAttributeValue($object, $attributeName);
  }

  public static function getAttributeExposed($object, $attributeName) {
    return self::getAttribute($object, $attributeName);
  }
}



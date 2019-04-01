these two classes can be used to convert xml to badgerfish json. They can be used
with the new java soap services to avoid rewriting parsing when code expect an answer
from OLS webservices.

example input from https://openorder.addi.dk/3.0  checkOrderPolicyRequest:

$XML =
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
   <S:Body>
      <ns1:checkOrderPolicyResponse xmlns:ns1="http://oss.dbc.dk/ns/openorder">
         <ns1:lookUpUrl>https://guldbib.dk/search/ting/54051662</ns1:lookUpUrl>
         <ns1:orderPossible>true</ns1:orderPossible>
         <ns1:orderPossibleReason>owned_accepted</ns1:orderPossibleReason>
      </ns1:checkOrderPolicyResponse>
   </S:Body>
</S:Envelope>

<?php
require_once "class_lib/objconvert_class.php";
require_once "class_lib/xmlconvert_class.php";
$dom = new DOMDocument();
    $dom->preserveWhiteSpace = FALSE;
    $xmlconvert = new xmlconvert();
    $objconvert = new objconvert();
    if($dom->loadXML($XML)){
      $OLS_obj = $xmlconvert->xml2obj($dom);
      $badgerfish = $objconvert->obj2json($OLS_obj);
      $badgerfish_obj = json_decode($badgerfish);
      return $badgerfish_obj->Envelope->Body;
    }

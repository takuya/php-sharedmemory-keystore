<?php

namespace Takuya\ValueFilter\Traits;


trait FilterJson {
  public function enableJson($flag=JSON_OBJECT_AS_ARRAY):void {
    $this->append(
      fn($v)=> is_object($v) ? json_encode((array)$v): json_encode($v),
      fn($v)=>json_decode($v,null,512,$flag)
    );
  }
}
<?php

namespace Takuya\ValueFilter\Traits;

trait FilterBase64 {
  
  public function enableBase64() {
    $this->append(base64_encode(...),base64_decode(...));
  }
}

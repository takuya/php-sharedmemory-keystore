<?php

namespace Takuya\ValueFilter\Traits;

trait FilterZlib {
  
  public function enableZlib( $level = 9 ):void {
    $this->append(
      gzdeflate(...), gzinflate(...));
  }
}
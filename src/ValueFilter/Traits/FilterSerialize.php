<?php

namespace Takuya\ValueFilter\Traits;


trait FilterSerialize {
  public function enableSerialize():void {
    $this->append(serialize(...),unserialize(...));
  }
}
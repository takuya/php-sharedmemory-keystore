<?php

namespace Takuya\SysV;

interface  SharedMemory {
  
  public function __construct( string $name, int $size = 1024*2, int $perm = 0770 );
  
  public function attach():bool;
  
  public function erase():bool;
  
  public function isEmpty():bool;
  
  public function get():mixed;
  
  public function put( $var ):bool;
  
  public function destroy():bool;
  
  public function detach():bool;
}
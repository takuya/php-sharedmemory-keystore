<?php

namespace Tests\Units\KeyValueStore;

use Tests\TestCase;
use Takuya\SysV\ShmArrayStore;
use Takuya\SysV\IPCShmKeyStore;
use function Takuya\Helpers\str_rand;

class ShareMultiProcessTest extends TestCase {
  
  /**
   * @throws \Exception
   */
  public function test_shm_kvs_by_fork() {
    
    $name = str_rand(10);
    $msg = str_rand(10);
    $size = 150;
    // fork
    if( ( $pid = pcntl_fork() ) === false ) {
      throw new \Exception('fork failed');
    }
    if( $pid === 0 ) {
      $store = new ShmArrayStore($name, $size);
      $store->add($msg);
      exit(0);
    }
    pcntl_waitpid($pid, $st);
    $store = new ShmArrayStore($name, $size);
    $ret = $store->get(0);
    $store->destroy();
    $this->assertEquals($msg, $ret);
  }
}
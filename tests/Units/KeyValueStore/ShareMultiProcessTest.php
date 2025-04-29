<?php

namespace Tests\Units\KeyValueStore;

use Tests\TestCase;
use Takuya\SysV\ShmArrayStore;
use Takuya\SysV\IPCShmKeyStore;
use function Takuya\Helpers\str_rand;
use function Takuya\Helpers\child_fork;

class ShareMultiProcessTest extends TestCase {
  
  /**
   * @throws \Exception
   */
  public function test_shm_kvs_by_fork() {
    
    $name = str_rand(10);
    $msg = str_rand(10);
    $size = 150;
    // fork
    $pid = child_fork(
      function () use ( $name, $size, $msg ) {//child
        $store = new ShmArrayStore($name, $size);
        $store->add($msg);
        exit(0);
      },
      function ($cpid) {//parent
        pcntl_waitpid($cpid, $st);
      });
    //
    $store = new ShmArrayStore($name, $size);
    $ret = $store->get(0);
    $store->destroy();
    $this->assertEquals($msg, $ret);
  }
}
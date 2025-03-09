<?php

namespace Tests\Units\Shmop;

use Tests\TestCase;
use Takuya\SysV\ShmOperator;
use Takuya\ValueFilter\ValueFilterSupport;
use function Takuya\Helpers\str_rand;

class SharedMemoryTest extends TestCase {
  
  public function test_shmop_crud() {
    $msg = str_rand(100);
    $shm = new ShmOperator(str_rand(), 100);
    $ret[] = $shm->put($msg);
    $ret[] = $shm->get();
    $ret[] = $shm->erase();
    $ret[] = $shm->isEmpty();
    $ret[] = $shm->get();
    $ret[] = $shm->destroy();
    //
    $this->assertEquals([true, $msg, true, true, null, true], $ret);
  }
}
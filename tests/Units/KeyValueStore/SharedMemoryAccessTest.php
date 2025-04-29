<?php

namespace Tests\Units\KeyValueStore;

use Tests\TestCase;
use Takuya\SysV\ShmArrayStore;
use function Takuya\Helpers\str_rand;

class SharedMemoryAccessTest extends TestCase {
  
  public function test_shm_key_store_methods() {
    $arr = array_map(fn() => str_rand(10), array_fill(0, 5, null));
    $store = new ShmArrayStore(str_rand(), 500);
    $ret[] = $store->store($arr);
    $ret[] = $store->all();
    $ret[] = $store->has(0);
    $ret[] = $store->get(0);
    $ret[] = $store->set(0, $msg = '::'.str_rand());
    $ret[] = $store->get(0);
    $ret[] = $store->size();
    $ret[] = $store->del(0);
    $ret[] = $store->size();
    $ret[] = $store->clear();
    $ret[] = $store->all();
    //
    $store->destroy();
    $this->assertEquals([true, $arr, true, $arr[0], true, $msg, sizeof($arr), true, true, sizeof($arr) - 1, []], $ret);
  }
  public function test_shm_key_transaction() {
    $arr = array_map(fn() => str_rand(10), array_fill(0, 5, null));
    $store = new ShmArrayStore(str_rand(), 500);
    $ret = $store->runWithLock(function( $kvs )use($arr){
      $ret[]=$kvs->store($arr);
      $ret[]=$kvs->all();
      $ret[]=$kvs->clear();
      $ret[]=$kvs->store($arr);
      return $ret;
    });
    //
    $store->destroy();
    $this->assertEquals([true,$arr,true,true], $ret);
  }
  
}
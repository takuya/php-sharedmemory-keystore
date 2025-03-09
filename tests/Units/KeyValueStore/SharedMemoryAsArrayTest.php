<?php

namespace Tests\Units\KeyValueStore;

use Tests\TestCase;
use Takuya\SysV\ShmArrayStore;
use function Takuya\Helpers\str_rand;

class SharedMemoryAsArrayTest extends TestCase {
  
  public function test_array_loop_access_to_shm_key_store() {
    $arr = array_map(fn() => str_rand(10), array_fill(0, 5, null));
    $store = new ShmArrayStore(str_rand(), 500);
    $store->store($arr);
    foreach ($store as $k => $v) {
      $this->assertEquals($arr[$k], $v);
    }
    $store->destroy();
  }
  
  public function test_array_key_access_to_shm_key_store() {
    $store = new ShmArrayStore(str_rand(), 500);
    $store['name'] = ( $msg = str_rand() );
    $this->assertEquals($msg, $store['name']);
    $this->assertEquals(true, ! empty($store['name']));
    $this->assertEquals(true, isset($store['name']));
    unset($store['name']);
    $this->assertEquals(false, ! empty($store['name']));
    $this->assertEquals(false, isset($store['name']));
    $store->destroy();
  }
  
  public function test_array_key_access_which_no_exists_key() {
    $store = new ShmArrayStore(str_rand(), 500);
    $ret[] = $store->get(rand());
    $ret[] = $store[rand()];
    $store->destroy();
    $this->assertEquals([null, null], $ret);
  }
  
  public function test_array_append_to_shm_key_store() {
    $arr = array_map(fn() => str_rand(10), array_fill(0, 5, null));
    $store = new ShmArrayStore(str_rand(), 500);
    $store[] = ( $msg = str_rand() );
    $ret = $store->all();
    $store->destroy();
    $this->assertEquals([$msg], $ret);
  }
}
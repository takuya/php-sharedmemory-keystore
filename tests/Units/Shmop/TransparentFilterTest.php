<?php

namespace Tests\Units\Shmop;

use Tests\TestCase;
use Takuya\SysV\ShmOperator;
use Takuya\ValueFilter\ValueFilterSupport;
use function Takuya\Helpers\str_rand;

class TransparentFilterTest extends TestCase {
  
  public function test_shmop_using_null_filter() {
    $str = str_rand(10);
    $filter = new ValueFilterSupport('');
    $filter->enableSerialize();
    $shm = new ShmOperator(str_rand(10));
    $ret = $shm->get();
    //
    $shm->destroy();
    $this->assertEquals('', $ret);
  }
  
  public function test_shmop_using_filter() {
    $str = str_rand(10);
    $data = ['sample' => $str];
    $filter = new ValueFilterSupport('');
    $filter->enableSerialize();
    $filter->enableBase64();
    $filter->enableZlib();
    $filter->enableEncryption('my_strong_password');
    $shm = new ShmOperator(str_rand(10));
    $shm->setFilter($filter);
    $shm->put($data);
    $ret = $shm->get();
    //
    $shm->destroy();
    $this->assertEquals($data, $ret);
  }
  
  public function test_shmop_filter_shortcut() {
    $str = str_rand(10);
    $data = ['sample' => $str];
    $filter = new ValueFilterSupport('serialize|encryption,my_strong_password|zlib,5|base64');
    $shm = new ShmOperator(str_rand(10));
    $shm->setFilter($filter);
    $shm->put($data);
    $ret = $shm->get();
    //
    $shm->destroy();
    $this->assertEquals($data, $ret);
  }
  
  public function test_shmop_filter_shortcut_json() {
    $str = str_rand(10);
    $data = ['sample' => $str];
    $filter = new ValueFilterSupport('json');
    $shm = new ShmOperator(str_rand(10));
    $shm->setFilter($filter);
    //
    $shm->put($data);
    $ret[] = $shm->get();
    $shm->put($str);
    $ret[] = $shm->get();
    $shm->put($i = mt_rand());
    $ret[] = $shm->get();
    //
    $shm->destroy();
    $this->assertEquals(['sample' => $str], $ret[0]);
    $this->assertEquals($str, $ret[1]);
    $this->assertEquals($i, $ret[2]);
  }
}
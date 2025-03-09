<?php

namespace Takuya\SysV;

use RuntimeException;
use Takuya\ValueFilter\ValueFilterSupport;

class ShmOperator implements SharedMemory {
  
  protected int                $ipc_key;
  protected \Shmop             $shm;
  protected ValueFilterSupport $filter;
  
  /**
   * @param string $name unique name
   * @param int    $size shared memory size to allocate.
   * @param int    $perm IPC permission default is 0770
   */
  public function __construct( public string $name,
                               public int    $size = 1024*2,
                               public int    $perm = 0770,
                               public string $mode = 'c' ) {
    $this->attach();
  }
  ////////////////////////////////////////////////////////
  /// filters
  ////////////////////////////////////////////////////////
  public function setFilter( ValueFilterSupport $filter ):void {
    $this->filter = $filter;
  }
  ////////////////////////////////////////////////////////
  /// Wrapping function.
  ////////////////////////////////////////////////////////
  protected function close():bool {
    unset($this->shm);
    
    return true;
  }
  
  protected function open():bool {
    if( ! $this->shm ??= shmop_open($this->key(), $this->mode, $this->perm, $this->size) ) {
      throw new RuntimeException('shmop_open() failed.');
    }
    
    return !empty($this->shm);
  }
  
  protected function read( int $offset = 0, ?int $size = null ):string {
    return rtrim(shmop_read($this->shm, 0, $this->size), "\0");
  }
  
  protected function write( string $val, int $offset = 0 ):int {
    return shmop_write($this->shm, $val, $offset);
  }
  
  protected function key():int {
    return $this->ipc_key ??= static::str_to_key($this->name);
  }
  public static function str_to_key(string $str):int{
    return crc32($str)&0x7FFFFFFF;
  }
  ////////////////////////////////////////////////////////
  // -- implementation.
  ////////////////////////////////////////////////////////
  /**
   * This fill \0 NULL to memory.
   * @return bool
   */
  public function erase():bool {
    return $this->write(pack("x{$this->size}"));
  }
  
  public function isEmpty():bool {
    return $this->read() === '';
  }
  
  public function get():mixed {
    return empty($this->filter) ? $this->read() ?? null : $this->filter->apply($this->read() ?? '', 'get');
  }
  
  public function put( $var ):bool {
    return $this->erase() && $this->write(empty($this->filter) ? $var : $this->filter->apply($var, 'put'));
  }
  
  public function destroy():bool {
    return shmop_delete($this->shm);
  }
  
  public function detach():bool {
    return $this->close();
  }
  
  public function attach():bool {
    return $this->open();
  }
}
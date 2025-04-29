<?php

namespace Takuya\SysV;

use Takuya\ValueFilter\ValueFilterSupport;

class ShmArrayStore implements \ArrayAccess, \Countable, \IteratorAggregate {
  
  protected ShmOperator  $shm;
  protected IPCSemaphore $sem;
  
  /**
   * @param string $name unique name
   * @param int    $size shared memory size to allocate.
   * @param int    $perm IPC permission default is 0770
   */
  public function __construct( public string $name,
                               public int    $size = 1024*10,
                               public int    $perm = 0770,
                               public string $mode = 'c' ) {
    ['shmem-key'=>$shm_key,'semaphore-key'=>$sem_key] = $this->ipc_key();
    $this->shm = new ShmOperator($shm_key, $this->size, $this->perm, $this->mode);
    $this->sem = new IPCSemaphore(name: $sem_key, perm: $this->perm);
    $this->setFilter();
  }
  
  public function setFilter( string $filter_names = 'serialize' ):void {
    $this->shm->setFilter(new ValueFilterSupport($filter_names));
  }
  
  public function ipc_key():array {
    return [
      'semaphore-key' => ShmOperator::str_to_key($this->name.'_sem'),
      'shmem-key'     => ShmOperator::str_to_key($this->name),
    ];
  }
  
  ////////////////////////////////////////
  /// --- Shortcut methods
  ////////////////////////////////////////
  protected function withLock( callable $fn ) {
    return $this->sem->withLock(fn() => $fn($this));
  }
  
  protected function load():array {
    return $this->shm->get() ?? [];
  }
  
  protected function save( array $items ):bool {
    return $this->shm->put($items);
  }
  
  protected function reset():bool {
    return $this->shm->erase();
  }
  
  /**
   * @param callable(\Takuya\SysV\ShmArrayStore $this):mixed $fn
   * @return mixed
   */
  public function runWithLock( callable $fn ):mixed {
    return $this->withLock($fn);
  }
  
  public function destroy():bool {
    return $this->withLock(fn() => $this->shm->destroy()) && $this->sem->destroy();
  }
  
  public function size():int {
    return sizeof($this->all());
  }
  
  public function isEmpty():bool {
    return empty($this->all());
  }
  
  ////////////////////////////////////////
  /// --- Basic CRUD methods
  ////////////////////////////////////////
  public function all():array {
    return $this->withLock(fn() => $this->load());
  }
  
  public function clear():bool {
    return $this->withLock(fn() => $this->reset());
  }
  
  public function store( array $items ):bool {
    return $this->withLock(fn() => $this->save($items));
  }
  
  public function del( string|int $key ):bool {
    return $this->withLock(function () use ( $key ):bool {
      $items = $this->load();
      unset($items[$key]);
      
      return $this->save($items);
    });
  }
  
  public function set( string|int|null $key, mixed $val ):bool {
    return $this->withLock(function () use ( $key, $val ):bool {
      $items = $this->load();
      is_null($key) ? $items[] = $val : $items[$key] = $val;
      
      return $this->save($items);
    });
  }
  
  public function get( string|int $key ):mixed {
    return $this->withLock(fn() => $this->load()[$key] ?? null);
  }
  
  public function has( string|int $key ):bool {
    return $this->withLock(fn() => isset($this->load()[$key]));
  }
  
  public function add( mixed $val ):bool {
    return $this->set(null, $val);
  }
  ///////////////////////////////////////////////////
  //-- Interface implementations.
  ///////////////////////////////////////////////////
  public function offsetExists( mixed $offset ):bool {
    return $this->has($offset);
  }
  
  public function offsetGet( mixed $offset ):mixed {
    return $this->get($offset);
  }
  
  public function offsetSet( mixed $offset, mixed $value ):void {
    $this->set($offset, $value);
  }
  
  public function offsetUnset( mixed $offset ):void {
    $this->del($offset);
  }
  
  public function getIterator():\Traversable {
    return new \ArrayIterator($this->all());
  }
  
  public function count():int {
    return $this->size();
  }
}
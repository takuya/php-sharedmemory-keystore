# php-sysv-ipc-shared-memory 

This package is wrapper for php sysv shm_xxx.
## Installing 
from Packagist 
```shell
composer require takuya/php-sharedmemory-keystore
```
from GitHub
```shell
name='takuya/php-sharedmemory-keystore'
repo=git@github.com:$name.git
composer config repositories.$name vcs $repo
composer require $name
```
## Examples

```php
<?php
$uniq_name = 'uniq_name_for_shm'
$store = new ShmArrayStore($uniq_name);
$store['key']=new stdClass();
$obj = $shm->get('key');// instance of MyClass;
// remove ipc
$shm->destroy()
```
## Safer access by sem
```php
<?php
$store = new ShmArrayStore('my-shm', 1024);
$store->runWithLock(function($store)use($idx){
  $store->set(0,($store->get(0) ?? 0)+$idx);
});
}
```
## More easy usage : Array Access.

This package offers KVS style access to Shared Memory.
```php
<?php
$store = new ShmArrayStore('kvs-like', 1024*1024);
// Set by key
$store->set('key',['msg'=>'Auxakai3']);
// Get by key
$store->get('key')['msg']; // => Auxakai3 
```

This package has simple shmop wrapper.
```php
<?php
 $shm = new ShmOperator(str_rand(), 100);
 $shm->put($msg);
 $shm->get();
 $shm->erase();
 $shm->isEmpty();
 $shm->get();
 $shm->destroy();
```

Limitation: ShmOperator does not have `serialization`;



### comparison to shm_put_var

Sysv function (ex `shm_put_var`) has auto serialization. but `shmop_write` does not.

shm_put_var is very confusing when you want to add json_encode or encrypt before write.

To make use of encryption or json_encode into SharedMemory, shmop_write is better.

## See Also.

I wrote these php code.

- [PHP SysV IPC SharedMemory Wrapper](https://github.com/takuya/php-sysv-ipc-shm-cache)
- [PHP SysV IPC Semaphore Wrapper ](https://github.com/takuya/php-sysv-ipc-semaphore)
- [PHP SysV IPC Message Queue](https://github.com/takuya/php-sysv-ipc-message-queue)
- [PHP SharedMemory Operation](https://github.com/takuya/php-sharedmemory-keystore) This package.



### remove ipc by manually 

If unused ipc remains. use SHELL command to remove.

```shell
ipcs -m | grep $USER | grep -oE '0x[a-f0-9]+' | xargs -I@ ipcrm --shmem-key @
```






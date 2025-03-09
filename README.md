# php-sysv-ipc-shared-memory 

This package is wrapper for php sysv shm_xxx.
## Installing 
from Packagist 
```shell
composer require takuya/php-sysv-ipc-shared-memory
```
from GitHub
```shell
name='php-sysv-ipc-shared-memory'
composer config repositories.$name \
vcs https://github.com/takuya/$name  
composer require takuya/$name:master
composer install
```
## Examples

```php
<?php
$uniq_name = 'shm_name';
$shm = new IPCSharedMem($uniq_name);
$shm->put(new MyClass());
//
$obj = $shm->get();// instance of MyClass;
// remove ipc
$shm->destroy()
```

## More easy usage : Array Access.

This package offers KVS style access to Shared Memory.
```php
<?php
$store = new IPCShmKeyStore('kvs-like', 1024*1024);
// Set by key
$store->set('key',['msg'=>'Auxakai3']);
// Get by key
$store->get('key')['msg']; // => Auxakai3 
```

This package offers ArrayAccess style to use Shared Memory.
```php
<?php
$arr = new IPCShmKeyStore('array-like', 100);
$arr[] = 'a';
$arr[] = 'b';
$arr[] = 'c';
foreach($arr as $e){
  echo "$e,";
}
// => "a,b,c,"
```

Limitation: ArrayAccess is not a real 'array'. Array functions ( ex `array_map()` ) cannot be applied for this.


### comparison to shm_open

Compare to shared memory functions ( ex `shm_open()`) , One big advantage SysV functions has.

Sysv function (ex `shm_put_var`) has auto serialization.

### remove ipc by manually 

If unused ipc remains. use SHELL command to remove.

```shell
ipcs -m | grep $USER | grep -oE '0x[a-f0-9]+' | xargs -I@ ipcrm --shmem-key @
```





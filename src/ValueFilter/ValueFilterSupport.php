<?php

namespace Takuya\ValueFilter;

use Takuya\ValueFilter\Traits\FilterZlib;
use Takuya\ValueFilter\Traits\FilterJson;
use Takuya\ValueFilter\Traits\FilterBase64;
use Takuya\ValueFilter\Traits\FilterSerialize;
use Takuya\ValueFilter\Traits\FilterEncryption;

class ValueFilterSupport {
  
  use FilterEncryption;
  use FilterSerialize;
  use FilterBase64;
  use FilterZlib;
  use FilterJson;
  
  public function __construct( string $filter_names = 'serialize|base64' ) {
    $this->filters ??= ['put' => [], 'get' => []];
    $this->addFilters($filter_names);
  }
  
  protected function addFilters( $filter_names ) {
    if( empty($filter_names) ) {
      return;
    }
    $filter_names = preg_split('/\|/', $filter_names);
    foreach ($filter_names as $name) {
      $name = preg_split('/,/', $name);
      [$name, $args] = [$name[0], array_slice($name, 1)??[]];
      $this->{'enable'.ucfirst($name)}(...$args);
    }
  }
  
  protected array $filters;
  
  public function append( callable $put, callable $get ):void {
    array_push($this->filters['put'], $put);
    array_unshift($this->filters['get'], $get);
  }
  
  public function apply( $value, $name ) {
    return array_reduce($this->filters[$name], fn( $c, $f ) => $f($c), $value) ?: null;
  }
}
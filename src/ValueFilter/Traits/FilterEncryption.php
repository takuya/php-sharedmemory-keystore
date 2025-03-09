<?php

namespace Takuya\ValueFilter\Traits;

trait FilterEncryption {
  
  public function enableEncryption( $pass, $iter=1000*1000, $cipher= "AES-256-CBC" ) {
    $openssl_equivalent_encrypt = function ( string $data,
                                             string $pass,
                                             int    $iter = 1000*1000,
                                             string $cipher = "AES-256-CBC" ):string {
      $iv_len = openssl_cipher_iv_length($cipher);
      $salt = random_bytes($iv_len - strlen('Salted__')); // Salted__ を考慮したソルトサイズ
      $key_len = strlen('Salted__'.$salt) + $iv_len;
      $key_and_iv = openssl_pbkdf2($pass, $salt, $key_len + $iv_len, $iter, 'sha256');
      $key = substr($key_and_iv, 0, $key_len);
      $iv = substr($key_and_iv, $key_len, $iv_len);
      $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
      
      return base64_encode("Salted__".$salt.$encrypted);
    };
    $openssl_equivalent_decrypt = function ( string $encrypted_base64,
                                             string $pass,
                                             int    $iter = 1000*1000,
                                             string $cipher = "AES-256-CBC" ):bool|string {
      $encrypted_base64 = file_exists($encrypted_base64) ? file_get_contents($encrypted_base64) : $encrypted_base64;
      $encrypted = base64_decode($encrypted_base64);
      $iv_len = openssl_cipher_iv_length($cipher);
      $data = substr($encrypted, $iv_len);
      if( ! str_starts_with($encrypted, 'Salted__') ) {
        return false;
      }
      //
      $salt = substr($encrypted, strlen('Salted__'), $iv_len - strlen('Salted__'));
      $key_len = strlen('Salted__'.$salt) + $iv_len;
      $key_and_iv = openssl_pbkdf2($pass, $salt, $key_len + $iv_len, $iter, 'sha256');
      $key = substr($key_and_iv, 0, $key_len);
      $iv = substr($key_and_iv, $key_len, $iv_len);
      
      //
      return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    };
    $this->append(
      fn( $value ) => $openssl_equivalent_encrypt($value, $pass, $iter, $cipher),
      fn( $value ) => $openssl_equivalent_decrypt($value, $pass, $iter, $cipher)
    );
  }
}
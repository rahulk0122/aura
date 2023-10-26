<?php

namespace Drupal\aura_enrollment\Helper;

/**
 * Class Secure Text.
 */
class SecureText {

  /**
   * Encrypt given value by a key.
   *
   * @param mixed $str
   *   String that need to encrypt.
   * @param mixed $key
   *   Key used for encryption.
   *
   * @return mixed[]
   *   Encrypted value.
   */
  public static function encrypt(mixed $str, mixed $key): array|bool|string {
    $str = self::pkcs5Pad($str);
    $iv = substr($key, 0, 16);
    $encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
    $encrypted = base64_decode($encrypted);
    $encrypted = unpack('C*', ($encrypted));
    $encrypted = self::byteArray2Hex($encrypted);
    $encrypted = urlencode($encrypted);
    return $encrypted;
  }

  /**
   * Internal method for encryption.
   *
   * @param mixed $text
   *   Text as input.
   *
   * @return string
   *   result string.
   */
  protected static function pkcs5Pad(mixed $text): string {
    $blockSize = 16;
    $pad = $blockSize - (strlen($text) % $blockSize);
    return $text . str_repeat(chr($pad), $pad);
  }

  /**
   * Internal method for encryption.
   *
   * @param mixed $byteArray
   *   Input value.
   *
   * @return string
   *   Result string.
   */
  protected static function byteArray2Hex(mixed $byteArray): string {
    $chars = array_map('chr', $byteArray);
    $bin = implode('', $chars);
    return bin2hex($bin);
  }

  /**
   * Decrypt given value by a key.
   *
   * @param mixed $code
   *   Code to decrypt.
   * @param mixed $key
   *   Key used for decryption.
   *
   * @return mixed
   *   Decrypted response.
   */
  public static function decrypt(mixed $code, mixed $key): mixed {
    $code = self::hex2ByteArray(trim($code));
    $code = self::byteArray2String($code);
    $iv = substr($key, 0, 16);
    $code = base64_encode($code);
    $decrypted = openssl_decrypt($code, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
    return self::pkcs5Unpad($decrypted);
  }

  /**
   * Internal method for decryption.
   *
   * @param mixed $hexString
   *   Input value.
   *
   * @return mixed
   *   Output value.
   */
  protected static function hex2ByteArray(mixed $hexString): mixed {
    $string = hex2bin($hexString);
    return unpack('C*', $string);
  }

  /**
   * Internal method for decryption.
   *
   * @param mixed $byteArray
   *   Input value.
   *
   * @return mixed
   *   Output value.
   */
  protected static function byteArray2String(mixed $byteArray): mixed {
    $chars = array_map('chr', $byteArray);
    return implode('', $chars);
  }

  /**
   * Internal method for decryption.
   *
   * @param mixed $text
   *   Input value.
   *
   * @return mixed
   *   Output value.
   */
  protected static function pkcs5Unpad(mixed $text): mixed {
    $pad = ord($text[strlen($text) - 1]);
    if ($pad > strlen($text)) {
      return FALSE;
    }
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
      return FALSE;
    }
    return substr($text, 0, -1 * $pad);
  }

}

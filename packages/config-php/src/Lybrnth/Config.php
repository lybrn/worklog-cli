<?php
namespace Lybrnth;
class Config {
  public static function read($namespace,$key) {
    return \Lybrnth\Config\Read\Key::read($namespace,$key);
  }
}

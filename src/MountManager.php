<?php
/**
 * @link https://github.com/sup-ham/flysystem
 * @copyright Copyright (c) 2019 Yusup Hambali
 * @license MIT
 */
namespace Supham\Flysystem;

/**
 * MountManager
 *
 * @author Yusup Hambali <supalpuket@gmail.com>
 */
class MountManager extends \League\Flysystem\MountManager {

    public $defaultAdapter = 'local';

    /** {@inheritdoc} */
    protected function getPrefixAndPath($path) {
        if (\strpos($path, ':')) {
          return \explode(':', $path, 2);
        }
        return [$this->defaultAdapter, \ltrim($path, '/')];
    }

}

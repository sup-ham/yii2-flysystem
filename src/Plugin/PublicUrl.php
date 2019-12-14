<?php
namespace Supham\Flysystem\Plugin;

use League\Flysystem\Plugin\AbstractPlugin;
use League\Flysystem\Config;

class PublicUrl extends AbstractPlugin {

    public function getMethod() {
        return 'getPublicUrl';
    }

    public function handle($path, $config=[]) {
      return $this->filesystem->getAdapter()->getPublicUrl($path, new Config($config));
    }
}

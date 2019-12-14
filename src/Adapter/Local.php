<?php
namespace Supham\Flysystem\Adapter;

use Yii;
/**
 * Local Filesystem Adapter
 *
 * @author Yusup Hambali <supalpuket@gmail.com>
 */
class Local extends \League\Flysystem\Adapter\Local {

  public $baseUrl;

  public function getPublicUrl($path) {
    return Yii::$app->urlManager->getHostInfo() . Yii::getAlias($this->baseUrl. $path);
  }

  public function readStream($path) {
    if (strpos("/$path", $this->pathPrefix) !== false) {
        $path = "/$path";
        $stream = fopen($path, 'rb');
        return ['type' => 'file', 'path' => $path, 'stream' => $stream];
    }
    return parent::readStream($path);
  }
}

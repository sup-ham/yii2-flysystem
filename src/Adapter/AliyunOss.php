<?php
namespace Supham\Flysystem\Adapter;

use Yii;
use League\Flysystem\Config;

class AliyunOss extends \Xxtime\Flysystem\Aliyun\OssAdapter {

  private $bucket;
  private $endpoint = 'oss-cn-hangzhou.aliyuncs.com';

  public function __construct($config=[]) {
    parent::__construct($config);
    $this->bucket = $config['bucket'];
    empty($config['endpoint']) OR $this->endpoint = $config['endpoint'];
  }

  public function getPublicUrl($fileName, Config $config) {
    $params = null;
    $h = $config->get('height');
    if ($w = $config->get('width') OR $h) {
      $params = "?x-oss-process=image/resize";
      $w && $params .= ",w_". $w;
      $h && $params .= ",w_". $h;
    }
    return sprintf('https://%s.%s/%s', $this->bucket, $this->endpoint, $fileName) . $params;
  }

  public function write($path, $contents, Config $config) {
    try {
      return parent::write($path, $contents, $config);
    } catch(\Exception $exc) {
      Yii::warning($exc);
    }
    return false;
  }

  public function delete($path) {
    try {
      return parent::delete($path);
    } catch(\Exception $exc) {
      'Delete : '. Yii::warning($exc);
    }
    return false;
  }

  public function setVisibility($path, $visibility) {
    try {
      return parent::setVisibility($path, $visibility);
    } catch(\Exception $exc) {
      Yii::warning($exc);
    }
    return false;
  }
}

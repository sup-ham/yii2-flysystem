<?php
namespace Supham\Flysystem\Adapter;

use Yii;
use League\Flysystem\Config;
use League\Flysystem\Util;
use Alfresco\Configuration;
use Alfresco\Model\NodeBodyCreate;
use Exception;

/**
 * Alfresco adapter
 *
    'mounts' => [
      'cloud' => [
          'class' => 'Supham\Flysystem\Yii2\fs\Adapter\Alfresco',
          'apiUrl' => 'http://192.168.100.99:8080/alfresco/api',
          'username' => 'username',
          'password' => 'password',
          'getNodeId' => function($path) {},
          'postWrite' => function($respon) {},
          'getSharedId' => function($path) {},
          'postShared' => function($respon) {},
      ],
 *
 * @author Supham <supalpuket@gmail.com>
 */
class Alfresco extends \League\Flysystem\Adapter\AbstractAdapter {

  public $pathPrefix = '-my-';
  public $apiUrl = 'http://192.168.100.99:8080/alfresco/api';
  public $username;
  public $password;
  public $getNodeId;
  public $postWrite;
  public $getSharedId;
  public $postShared;

  private static $apis = [];

  public function init() {
    $this->setApiClient();

    if (YII_DEBUG) {
      Configuration::getDefaultConfiguration()->setDebug(true);
      Configuration::getDefaultConfiguration()->setDebugFile(Yii::getAlias('@runtime/curl-verbose.log'));
    }
  }

  public function getPublicUrl($fileName, Config $config) {
    if (!$id = \call_user_func($this->getSharedId, $fileName)) {
      /** @var \Alfresco\Api\SharedlinksApi $api */
      $api = $this->getApi('Sharedlinks');

      try {
        $body = new \Alfresco\Model\SharedLinkBodyCreate([
          'node_id' => \call_user_func($this->getNodeId, $fileName),
          'expires_at' => $config->get('expires_at'),
        ]);
        $entry = $api->createSharedLink($body, 'id')->getEntry();
        $id = $entry->getId();
        \call_user_func($this->postShared, ['entry'=>$entry, 'path'=>$fileName, 'body'=>$body]);
      } catch(\Exception $e) {
        return '';
      }
    }
    $attachment = $config->get('attachment') ? 'true' : 'false';
    return $this->getBaseUrl() .'/share/proxy/alfresco-noauth/api/internal/shared/node/'. $id .'/content/'. basename($fileName) .'?c=force&noCache=1573533271260&a='. $attachment;
  }

  protected function getBaseUrl() {
    $urlParts = parse_url($this->apiUrl);
    return $urlParts['scheme'] .'://'. $urlParts['host'] . ($urlParts['port'] ? ':'. $urlParts['port'] : '');
  }

  /**
   * @inheritdoc
   */
  public function createDir($dirname, Config $config) {
    try {
      $nodeConfig = $this->getNodeCreateConfig($dirname);
      $postData = new NodeBodyCreate($nodeConfig);
      $postData->setNodeType('cm:folder');
      $node = $this->getApi('Nodes')->createNode($nodeConfig['context'], $postData, true)->getEntry();

      return [
        'path' => $nodeConfig['context'] . '/' . $postData->getRelativePath() . '/' . $node->getName(),
        'type' => 'dir',
      ];
    } catch(Exception $exc) {
      Yii::error($exc);
    }
  }

  /**
   * @inheritdoc
   */
  public function write($path, $contents, Config $config) {
    $tempFile = new TempFile();
    $tempFile->fwrite($contents);
    $tempFile->rewind();

    if ($result = $this->writeStream($path, $tempFile->getResource(), $config)) {
      $tempFile->rewind();
      $result['size'] = $tempFile->fstat()['size'];
      $result['contents'] = $tempFile->fread($result['size']);

      return $result;
    }
    return false;
  }

  /**
   * @inheritdoc
   */
  public function writeStream($path, $resource, Config $config) {
    try {
      $postData = $this->getNodeCreateConfig($path);
      $postData->setFiledata($resource);
      $postData->setNodeType('cm:content');
      $autoRename = $config->get('autoRename');
      $nodeId = $this->getApi('Nodes')->createNode($this->pathPrefix, $postData, $autoRename, 'id')->getEntry()->getId();

      $result['type'] = 'file';
      $result['path'] = $path;
      $result['node_id'] = $nodeId;

      if ($visibility = $config->get('visibility')) {
        $this->setVisibility($path, $visibility);
        $result['visibility'] = $visibility;
      }

      if (is_callable($fn = $this->postWrite)) {
        $fn($result, $config);
      }
      return $result;
    } catch(\Exception $ex) {
      YII_DEBUG && \Yii::error($ex, __METHOD__);
    }
  }

  /**
   * @inheritdoc
   */
  public function readStream($path) {
    try {
      $nodeId = call_user_func($this->getNodeId, $path);
      $outStream = $this->getApi('Nodes')->getNodeContent($nodeId);
      return ['type' => 'file', 'path' => $path, 'stream' => $outStream];
    } catch(Exception $exc) {
      \Yii::error(print_r($exc, 1), __METHOD__);
    }
  }

  public function updateStream($path, $resource, Config $config)
  {
  }

  public function delete($path) {
    try {
      $nodeId = \call_user_func($this->getNodeId, $path);
      $this->getApi('Nodes')->deleteNode($nodeId, $permanent=true);
      return true;
    } catch(Exception $exc) {}
    return false;
  }

  public function deleteDir($dirname)
  {
    # code...
  }

  public function setVisibility($path, $visibility)
  {
    # code...
  }

  public function has($path)
  {
    # code...
  }

  public function listContents($directory = '', $recursive = false)
  {
    # code...
  }

  public function getMetaData($path)
  {
    # code...
  }

  public function getSize($path)
  {
    # code...
  }

  public function getMimeType($path)
  {
    # code...
  }

  public function getTimestamp($path)
  {
    # code...
  }

  public function getVisibility($path)
  {
    # code...
  }

  /**
   * @inheritdoc
   */
  public function update($path, $contents, Config $config) {
    $location = $this->applyPathPrefix($path);
    $size = file_put_contents($location, $contents, $this->writeFlags);

    if ($size === false) {
      return false;
    }

    $type = 'file';

    $result = compact('type', 'path', 'size', 'contents');

    if ($mimetype = Util::guessMimeType($path, $contents)) {
      $result['mimetype'] = $mimetype;
    }

    return $result;
  }


  /**
   * @inheritdoc
   */
  public function read($path) {
    try {
      /** @var \Alfresco\Model\Node $node */
      $node = $this->getApi('Nodes')->getNode($path)->getEntry();

      if ($node->getIsFile()) {
        return ['type' => 'file', 'path' => $path, 'contents' => $node->getContent()];
      }
    } catch(Exception $exc) {
      throw $exc;
    }
    return false;
  }

  /**
   * @inheritdoc
   */
  public function rename($path, $newpath) {
    throw new \yii\base\NotSupportedException('Pull Requests are welcome.');
  }

  /**
   * @inheritdoc
   */
  public function copy($path, $newpath) {
    throw new \yii\base\NotSupportedException('Pull Requests are welcome.');
  }

  public function getApi($id) {
    $class = "Alfresco\Api\\{$id}Api";

    if (!isset(self::$apis[$id])) {
      self::$apis[$id] = new $class();
    }
    return self::$apis[$id];
  }

  public function setApiClient() {
    Configuration::getDefaultConfiguration()->setHost($this->apiUrl);
    Configuration::getDefaultConfiguration()->setPassword($this->password);
    Configuration::getDefaultConfiguration()->setUsername($this->username);
  }

  /** @return NodeBodyCreate */
  protected function getNodeCreateConfig($path) {
    return new NodeBodyCreate([
      'relative_path' => dirname($path),
      'name' => basename($path),
    ]);
  }
}

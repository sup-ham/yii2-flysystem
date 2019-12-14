<?php
/**
 * @link https://github.com/sup-ham/flysystem
 * @copyright Copyright (c) 2019 Yusup Hambali
 * @license MIT
 */
namespace Supham\Flysystem;

use Yii;
use Supham\Flysystem\MountManager;

/**
 * Filesystem
 *
 * @method \League\Flysystem\FilesystemInterface addPlugin(\League\Flysystem\PluginInterface $plugin)
 * @method void assertAbsent(string $path)
 * @method void assertPresent(string $path)
 * @method boolean copy(string $path, string $newpath)
 * @method boolean copyFileIn(string $localPath, string $destPath)
 * @method boolean createDir(string $dirname, array $config = null)
 * @method boolean delete(string $path)
 * @method boolean deleteDir(string $dirname)
 * @method \League\Flysystem\Handler get(string $path, \League\Flysystem\Handler $handler = null)
 * @method \League\Flysystem\AdapterInterface getAdapter()
 * @method \League\Flysystem\Config getConfig()
 * @method array|false getMetadata(string $path)
 * @method string|false getMimetype(string $path)
 * @method integer|false getSize(string $path)
 * @method integer|false getTimestamp(string $path)
 * @method string|false getVisibility(string $path)
 * @method array getWithMetadata(string $path, array $metadata)
 * @method boolean has(string $path)
 * @method array listContents(string $directory = '', boolean $recursive = false)
 * @method array listFiles(string $path = '', boolean $recursive = false)
 * @method array listPaths(string $path = '', boolean $recursive = false)
 * @method array listWith(array $keys = [], $directory = '', $recursive = false)
 * @method boolean put(string $path, string $contents, array $config = [])
 * @method boolean putStream(string $path, resource $resource, array $config = [])
 * @method string|false read(string $path)
 * @method string|false readAndDelete(string $path)
 * @method resource|false readStream(string $path)
 * @method boolean rename(string $path, string $newpath)
 * @method boolean setVisibility(string $path, string $visibility)
 * @method boolean update(string $path, string $contents, array $config = [])
 * @method boolean updateStream(string $path, resource $resource, array $config = [])
 * @method boolean write(string $path, string $contents, array $config = [])
 * @method boolean writeStream(string $path, resource $resource, array $config = [])
 *
 * @author Yusup Hambali <supalpuket@gmail.com>
 */
class Filesystem extends \yii\base\Component {

  /** @var \League\Flysystem\Config|array|string|null */
  public $config = ['disable_asserts' => true];
  public $plugins = [];
  public $defaultAdapter;
  public $mounts = [];

  /** @var MountManager */
  private $manager;

  /** @inheritdoc */
  public function init() {
    $this->manager = Yii::createObject([
        '__class' => MountManager::class,
        'defaultAdapter' => $this->defaultAdapter,
    ]);
    $this->registerPlugins();

    foreach ($this->mounts as $prefix => $adapter) {
      $args = $adapter['args'] ?? [];
      unset($adapter['args']);
      /** @var \League\Flysystem\Adapter\AbstractAdapter $adapter */
      $adapter = Yii::createObject($adapter, $args);
      @call_user_func([$adapter, 'init']);
      $this->manager->mountFilesystem($prefix, new \League\Flysystem\Filesystem($adapter, $this->config));
    }
  }

  /**
   * @param string $method
   * @param array $parameters
   * @return mixed
   */
  public function __call($method, $parameters) {
    return \call_user_func_array([$this->manager, $method], $parameters);
  }

  protected function registerPlugins() {
    foreach ($this->plugins as $plugin) {
      $this->manager->addPlugin(Yii::createObject($plugin));
    }
  }
}

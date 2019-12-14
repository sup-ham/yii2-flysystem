# Flysystem + Yii2

## Install

```bash
composer config repos.fs vcs https://github.com/sup-ham/yii2-flysystem.git
composer require supham/yii2-flysystem:@dev --prefer-dist -o
```

Jika mau pakai alfresco
```bash
composer config repos.alfres vcs https://github.com/sup-ham/alfresco-api-php-client.git
composer require supham/alfresco:@dev --prefer-dist -o
```

Jika mau pakai Alibaba OSS
```bash
composer require xxtime/flysystem-aliyun-oss --prefer-dist -o
```

## Config yii2
```php
$config['components']['fs'] = [
            '__class' => 'Supham\Flysystem\Filesystem',
            'plugins' => ['Supham\Flysystem\Plugin\PublicUrl'],
            'defaultAdapter' => 'cloud',
            'mounts' => [
              'tmp' => [
                '__class' => 'Supham\Flysystem\Adapter\Local',
                'args' => [ini_get('upload_tmp_dir') ?: sys_get_temp_dir()],
              ],
              'alfresco' => [
                '__class' => 'Supham\Flysystem\Adapter\Alfresco',
                'pathPrefix' => '-my-', // pilih satu [-root-, -my-, -shared-]
                'apiUrl' => 'http://127.0.0.1:8080/alfresco/api/',
                'username' => 'username',
                'password' => 'password',
              ],
              'aliyun-oss' => [
                '__class' => 'app\components\fs\Adapter\AliyunOss',
                'args'=>[[
                  'bucket'         => aliyun_oss_bucket_name,
                  'endpoint'       => aliyun_oss_endpoint_address,
                  'accessId'       => aliyun_access_id,
                  'accessSecret'   => aliyun_access_secret,
                  // 'timeout'        => 3600,
                  // 'connectTimeout' => 10,
                  // 'isCName'        => false,
                  // 'token'          => '',
                ]],
              ],
            ],
],
```

## Penggunaan
```php
      // Download file dari cloud
      $stream = Yii::$app->fs->readStream('path/to/file.txt');
      return Yii::$app->response->sendStreamAsFile($stream, 'downloaded-file.txt');

      // Upload content string
      $content = file_get_contents('/home/xubuncup/Documents/Akademi_VSGA_flyer.pdf');
      $a = Yii::$app->fs->write('arsip/filex.txt', $content);

      // Upload stream resource
      $stream = fopen('/home/xubuncup/Documents/Akademi_VSGA_flyer.pdf', 'r');
      $a = Yii::$app->fs->write('arsip/filex.txt', $stream);


      // Copy dari local /tmp ke alfresco
      Yii::$app->fs->copy("tmp:{$file->tempName}", "alfresco:$filePath", $config);

      // createDir
      $b = Yii::$app->fs->createDir('create_foldir_via_flysystem');
```

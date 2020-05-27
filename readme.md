# [Flysystem](/thephpleague/flysystem) + Yii2

## Install

```bash
composer require supham/yii2-flysystem --prefer-dist -o
```

Tersedia adapter API untuk Alfresco document management software
```bash
composer config repos.alfresco vcs https://github.com/sup-ham/alfresco-api-php-client.git
composer require supham/alfresco:@dev --prefer-dist -o
```

Tersedia Flysystem plugin `Supham\Flysystem\Plugin\PublicUrl`. untuk menggunakannya disyaratkan membuat dahulu implementasi realnya di masing-masing subclass adapter. lihat [list adapters](/thephpleague/flysystem#adapters).
contohnya jika akan menggunaan adapter AliyunOss maka install dulu [xxtime/flysystem-aliyun-oss](/xxtime/flysystem-aliyun-oss) lalu pakai subclass adapter `Supham\Flysystem\Adapter\AliyunOss` yang telah tersedia.

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
                '__class' => 'Supham\Flysystem\Adapter\AliyunOss',
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
Please refer to [Filesystem API](https://flysystem.thephpleague.com/v1/docs/usage/filesystem-api/)
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

# Flysystem + Yii2

## Install

```bash
composer config repos.fs vcs https://github.com/sup-ham/yii2-flysystem.git
composer require supham/flysystem:@dev --prefer-dist -o
```

Jika mau pakai alfresco
```bash
composer config repos.alfres vcs https://github.com/sup-ham/alfresco-api-php-client.git
composer require supham/alfresco-api:@dev --prefer-dist -o
```

Jika mau pakai Alibaba OSS
```bash
composer require xxtime/flysystem-aliyun-oss --prefer-dist -o
```

// config yii2
```
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

# Request

## 安装
```bash
coomposer require mu/juyuan
```


## 用法

```php
use Mu\Juyuan\Request;
# 第一步：声明网关地址
Request::$gateway = 'http://api.douban.com';

# 第二步：按照指定格式请求参数
$params = array(
	'action' => 'movie.page',
);
# 第三部：确定请求方式 GET|POST

# GET请求
Request::get($params);

# POST
Request::post($params);

```

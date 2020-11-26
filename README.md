# 一键完成疫情防控健康上报
<p align="center">
    <img width="300" src="http://qiniu.yunxiaozhi.cn/%E5%BE%AE%E4%BF%A1%E6%88%AA%E5%9B%BE_20201126140455.png" />
</p>

## 免责声明
本项目仅限学习使用，请如实上报疫情防控健康信息，配合国家疫情防控工作，产生的一切后果由使用者自负。

## 支持院校
只要是中控集团的学校都支持，只需要把域名修改为指定学校即可，本项目默认学校是广东白云学院。   
理论上，下面的学校都可以使用：   
![](http://qiniu.yunxiaozhi.cn/%E5%BE%AE%E4%BF%A1%E6%88%AA%E5%9B%BE_20201125134024.png)
## 分支说明
目前仅支持扫码登录，账号登录方式在开发过程中遇到了一点麻烦，一直解决。   
`main` 分支是可运行的分支，目前支持扫码登录   
`master` 分支是`beta`分支，包括未开发完成的账号登录

## 如何使用
如果你本地已经部署了`PHP`环境，直接克隆项目到本地
```shell script
git clone git@github.com:danbaixi/submitHealth.git
```
修改`service.php`的`DOMAIN`常量为你的学校登录的域名，如：
```php
const DOMAIN = 'byu.educationgroup.cn';
```
在浏览器访问`/scan.php`即可   

## 在线使用
访问`https://www.yunxiaozhi.cn/submitHealth/scan.php`   
[点击直达](https://www.yunxiaozhi.cn/submitHealth/scan.php)

## 开源许可证
[MIT](https://github.com/danbaixi/submitHealth/blob/main/LICENSE)

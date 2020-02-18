# videoapp web - 提供APP的api和后台管理


#### 架构
服务器环境
* PHP 7.2 + MySQL 5.6/5.7 + Nginx 

源码说明:
* 使用laravel框架 + composer 依赖开发
* 项目根目录：public/index.php
* 接口路由：routes/api.php
* 后台管理路由设置：routes/web.php
* 公共文件存放在 public 目录下
* 数据库配置文件1: .env    配置数据库前缀等
* 数据库配置文件2:  config/database.php   配置数据库前缀等
* 数据库声明在 app 目录下

 
#### 数据库

* 数据库名称：videoapp
* 数据库脚本：/文档/mysql-videoapp.sql


#### 后台入口
* /admintv
* 用户名 admin 密码 123456


#### Mac Nginx 下 Laravel 配置 

```
server {
    listen 80;
    server_name www.test.com;
    set $root_path '/home/web/videoappweb/public';  
    root $root_path;
    client_max_body_size 50m;
    
    index index.php index.html index.htm;
    
    try_files $uri $uri/ @rewrite;    
    
    location @rewrite {    
        rewrite ^/(.*)$ /index.php?_url=/$1;    
        client_max_body_size 50m;
    }    
    
    location ~ \.php {    
    
        fastcgi_pass 127.0.0.1:9000;    
        fastcgi_index /index.php;    
        fastcgi_split_path_info       ^(.+\.php)(/.+)$;    
        fastcgi_param PATH_INFO       $fastcgi_path_info;    
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;    
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;    
        include                       fastcgi_params;  
        client_max_body_size 50m;
    }

    location ~* ^/(css|img|js|flv|swf|download)/(.+)$ {    
        root $root_path;    
    }
}
````
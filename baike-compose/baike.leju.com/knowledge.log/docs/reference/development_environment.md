## 开发环境配置

>	Development Environment.md


## 使用 Nginx + PHP-fpm 模式

> 配置参考可见 ../webserver/nginx/ 的文件

## (推荐) 使用 Docker 构建自己的开发环境

### 1. 准备 Docker 环境

先安装好 Docker 环境，具体流程可通过网络搜索

#### Windows 主机

建议使用 VirtualBox 安装 Ubuntu Server 来进行开发。或导入已有的虚拟开发服务器。
在 Ubuntu 虚拟机中安装 Docker。

#### Ubuntu 主机

直接安装 Docker

确保在命令行下 root 身份时，docker -v 能查看正确的版本信息。

### 2. 安装 Docker Compose 命令工具

给 Docker 服务环境安装 `docker-compose` 命令，可以通过网络进行搜索安装。


### 3. 登录 `乐居私有 Docker Registry`

__注意:__ 乐居私有 Docker Registry 的配置 可参考本文的附录。

### 4. 下载 乐居知识百科的 Docker 编排文件

在 Docker 服务环境中，克隆下面的集装箱配置文件

通过 HTTP 访问 http://scrd.intra.leju.com/Searcher/knowledge-box 并下载 Zip 包。并解压为 knowledge-box


### 5. 最终效果

参考配置目录

/data/Cells/leju/ 为 Docker 编排配置文件所在目录 即 `/data/Cells/leju/knowledge-box`
/data/vhosts/knowledge/ 为项目源码目录 即 `/data/vhosts/knowledge/knowledge`

### 6. 启动项目服务

```
cd /data/Cells/leju/knowledge-box
docker-compose up
```

### 7. 绑定开发域名

使用虚拟机时，Ubuntu 虚拟机的 IP 假设为 192.168.99.99， 或使用 Ubuntu Desktop 做为主机开发环境时， IP 地址应该为 127.0.0.1。这里用 $ADDRESS 代表

hosts 文件
```
10.207.0.202	cdn.leju.com				# 测试开发使用的静态资源地址
$ADDRESS		ld.baike.leju.com			# PC端百科前台入口
$ADDRESS		ld.m.baike.leju.com			# 移动端百科前台入口
$ADDRESS		ld.admin.baike.leju.com		# 管理后台入口
$ADDRESS		ld.api.baike.leju.com		# 百科服务接口
```

绑定完成，即可通过浏览器对业务进行访问了。

### 8. 关于开发建议

如果使用虚拟机方式进行开发的时候

将 /data/vhosts/knowledge 通过 samba 共享给主机，然后在主机上，使用自己熟悉的开发工具或代码编辑器进行代码开发。

并在本机通过 ld.* 的域名上进行开发测试。

开发人员对自己的功能和代码测试通过，则通过 git 将代码提供并推送到代码仓库。

```
cd /data/vhosts/knowledge/knowledge
git pull # 拉取远端代码
git add * # 本行命令可选，在代码文件或目录存在未托管的情况下，执行添加命令，将新文件或目录添加到托管列表中
git commit -am '本次修改的内容' # 在本地代码中创建提交，并不会把本地的更新推送到服务端。
git push # 这个命令执行完，将会把本地代码推送到服务端。
```

## 开发环境中，使用 XDEBUG 时的配置参考

>	2016-10-30 羊阳贡献

1. 打开docker-compose.yml

在 `services` 的 `baike.leju.com-phpfpm` 中，将原有 `baike.leju.com-php7-fpm` 替换为 `baike.leju.com-php7-fpm-xdebug`

```
image:registry.leju.com:5000/dev/leju.com-php7-fpm-xdebug
```

2. 打开runtimephpfpm/php目录中的php.ini配置文件，加入如下配置

```ini
xdebug.default_enable = On
xdebug.remote_enable = On
; docker中必须配置此项
xdebug.remote_connect_back = 1
; 调试端口
xdebug.remote_port = 19000
xdebug.remote_autostart = 1
; 与netbeans中的名称对应
xdebug.idekey = "xdebug"
```

## 附录

《乐居私有 Docker Registry 的配置》

可先联系 贾世闻 进行资询
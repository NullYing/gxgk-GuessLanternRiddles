# 广科小喵猜灯谜
![gxgk](http://www.gxgk.cc/image/logo.png)
##  关于
        Copyleft © 2016 NULLYING
        Author:白月秋见心
        http://www.lastfighting.com/
        Email: ourweijiang@gmail.com

        关于“广科小喵猜灯谜接口”的声明：
	    1.该代码已成功在2016年2月22日~24日运行使用，参加人数共1012人，活动举办公众号：广科小喵（gxgkcat）
	    2.基于ThinkPHP3.2.3遵守Apache开源协议
	    3.使用该接口代码，请勿修改排行榜底部作者信息
        如不同意该声明请不要使用该软件，谢谢合作。

# PHP接口

* 猜灯谜接口地址：http://localhost/lantern/?s=/Home/Riddle/index
* 排行榜访问地址：http://localhost/lantern/?s=/Home/Riddle/rank
* 开始前获取提示与状态地址：http://localhost/lantern/?s=/Home/Riddle/riddlebegin
* 错误记录日志：\Application\Runtime\Logs
* 必须可读可写文件目录：\Application\Runtime

## 使用说明
    	1.搭建php、mysql运行环境，略
        2.假设服务器在远程
        创建\Application\Home\Conf\server.php文件
        写入
        <?php
            return array(
            'DB_CONFIG'=>array(
            'db_type'    =>   'mysql',
            'db_host'    =>   '数据库连接地址',
            'db_user'    =>   '数据库账号',
            'db_pwd'     =>   '数据库密码',
            'db_port'    =>    数据库端口号,
            'db_name'    =>    'xmlantern',
            'db_charset' =>    'utf8mb4'
             )
        );
        导入根文件xmlantern.sql
        3.设置接口通讯密钥
           \Application\Home\Controller找到函数 _checkReceive设置key变量
        4.发送post到接口地址，post参数为
            openid，nickname，msg

## 待改进

        1.未用宏变量设置喵币（代码所用分数代称）变化
            如兑换明信片所需分数，答对分数，答错分数
        2.自定义错误未输出到日志
        3.应用抽出固定数量难度题目，代替同一难度题目全部抽出

## 莞香广科

[莞香广科](http://www.gxgk.cc)是学生自建的互联网创新团队。团队成立于2012年5月，以校园论坛起步，逐渐发展了校园网资源共享站、媒体中心、微信助手等项目。

# ThinkPHP

## 简介

ThinkPHP 是一个免费开源的，快速、简单的面向对象的 轻量级PHP开发框架 ，创立于2006年初，遵循Apache2开源协议发布，是为了敏捷WEB应用开发和简化企业应用开发而诞生的。ThinkPHP从诞生以来一直秉承简洁实用的设计原则，在保持出色的性能和至简的代码的同时，也注重易用性。并且拥有众多的原创功能和特性，在社区团队的积极参与下，在易用性、扩展性和性能方面不断优化和改进，已经成长为国内最领先和最具影响力的WEB应用开发框架，众多的典型案例确保可以稳定用于商业以及门户级的开发。

## 全面的WEB开发特性支持

最新的ThinkPHP为WEB应用开发提供了强有力的支持，这些支持包括：

*  MVC支持-基于多层模型（M）、视图（V）、控制器（C）的设计模式
*  ORM支持-提供了全功能和高性能的ORM支持，支持大部分数据库
*  模板引擎支持-内置了高性能的基于标签库和XML标签的编译型模板引擎
*  RESTFul支持-通过REST控制器扩展提供了RESTFul支持，为你打造全新的URL设计和访问体验
*  云平台支持-提供了对新浪SAE平台和百度BAE平台的强力支持，具备“横跨性”和“平滑性”，支持本地化开发和调试以及部署切换，让你轻松过渡，打造全新的开发体验。
*  CLI支持-支持基于命令行的应用开发
*  RPC支持-提供包括PHPRpc、HProse、jsonRPC和Yar在内远程调用解决方案
*  MongoDb支持-提供NoSQL的支持
*  缓存支持-提供了包括文件、数据库、Memcache、Xcache、Redis等多种类型的缓存支持

## 大道至简的开发理念

ThinkPHP从诞生以来一直秉承大道至简的开发理念，无论从底层实现还是应用开发，我们都倡导用最少的代码完成相同的功能，正是由于对简单的执着和代码的修炼，让我们长期保持出色的性能和极速的开发体验。在主流PHP开发框架的评测数据中表现卓越，简单和快速开发是我们不变的宗旨。

## 安全性

框架在系统层面提供了众多的安全特性，确保你的网站和产品安全无忧。这些特性包括：

*  XSS安全防护
*  表单自动验证
*  强制数据类型转换
*  输入数据过滤
*  表单令牌验证
*  防SQL注入
*  图像上传检测

## 商业友好的开源协议

ThinkPHP遵循Apache2开源协议发布。Apache Licence是著名的非盈利开源组织Apache采用的协议。该协议和BSD类似，鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为开源或商业软件发布。
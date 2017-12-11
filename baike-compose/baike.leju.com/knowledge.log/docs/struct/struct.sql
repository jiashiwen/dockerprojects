/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;



# Dump of table admins
# 系统管理员数据表
# ------------------------------------------------------------

# passport_id 在什么情况下存在？
# passport_name 是否就是会员体系中的用户名？(昵称)
# city 数字对应表哪里查？(字典)
# user_type 的字典是什么？
# is_certified 这个值是什么情况下为认证，什么情况下为未认证
# account_status 这个值是统一登录端设置的？还是留给接入系统使用的？
# group_id 是否是接入系统中的超级管理员的概念？
# login_time 是统一登录系统登录时间？还是接入系统的登录时间？
# update_time 是在什么情况下的更新时间？
# create_time 是接入系统时，用户的时间点为创建时间？还是这个用户在统一登录系统中的创建时间？
# 是否有反向，从接入系统向统一登录系统更新用户数据的接口？
# 用户是否有头像信息？
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  -- `id` bigint(20) unsigned NOT NULL COMMENT '用户编号',
  `id` smallint(5) unsigned NOT NULL COMMENT '管理员编号 统一登录系统PKID',
  `passport_id` bigint(20) unsigned DEFAULT '0' COMMENT '新浪通行证ID',
  `passport_name` varchar(20) DEFAULT NULL COMMENT '用户名',
  `truename` varchar(32) DEFAULT NULL COMMENT '用户真实姓名',
  `headurl` varchar(250) DEFAULT NULL COMMENT '用户头像',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  `login_time` int(11) unsigned DEFAULT '0' COMMENT '用户最后一次登录时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  `em_email` varchar(64) DEFAULT '' COMMENT '员工邮箱',
  `em_sn` varchar(8) DEFAULT '' COMMENT '员工编号',
  `em_tel` varchar(20) DEFAULT '' COMMENT '座机号码',
  `mobile` varchar(14) DEFAULT '' COMMENT '手机/电话号码',
  `role` tinyint(4) unsigned DEFAULT '10' COMMENT '会员角色 10普通会员 20经济人 30编辑人员 99系统管理员',
  `role_id` smallint(5) unsigned DEFAULT '0' COMMENT '用户所属角色组编号 组对应权限',
  `authorities` mediumtext COMMENT '管理员与其设定的组的权限值差集',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员数据表';

# 角色数据表
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员角色编号',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '角色名称',
  `icount` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '属于此角色的用户数量',
  `description` text COMMENT '角色说明',
  `authorities` mediumtext COMMENT '角色组的权限',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员角色数据表';
# Dump of table answers
# ------------------------------------------------------------

# Dump of table knowledge 知识内容核心业务数据表
# ------------------------------------------------------------

# 定时发布用例
# where : ptime > NOW_TIME && status = 1
# 取置顶数据
# where : top_time > 0 && status = 1
# 版本协调
# condition : editor == _userid && status == 0 ---> save 不进行版本保存，即更新原 id
DROP TABLE IF EXISTS `knowledge`;
CREATE TABLE `knowledge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '知识内容主键编号',
  `cateid` smallint(5) unsigned DEFAULT '0' COMMENT '知识所属的分类',
  `catepath` varchar(20) DEFAULT '0' COMMENT '知识所属的分类路径 0-cid-cid-cid',
  `version` int(10) unsigned NOT NULL COMMENT '版本保存日期',
  `status` tinyint(4) DEFAULT '0' COMMENT '信息状态 0未审核 1审核通过(未发布) 9为已发布 -1为删除',
  `title` varchar(250) NOT NULL COMMENT '知识信息标题',
  `content` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '知识内容',
  `cover` varchar(250) DEFAULT '' COMMENT '知识配图',
  `coverinfo` varchar(250) DEFAULT '' COMMENT '知识配图说明',
  `editorid` bigint(20) unsigned DEFAULT '0' COMMENT '编辑用户编号',
  `editor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '作者姓名,用于显示',
  `ctime` int(10) unsigned NOT NULL COMMENT '知识创建时间',
  `ptime` int(10) unsigned NOT NULL COMMENT '知识定时发布时间 默认与ctime时间一致',
  `utime` int(10) unsigned NOT NULL COMMENT '知识版本更新时间',
  `scope` varchar(20) DEFAULT '' COMMENT '数据所属城市',
  `src_type` tinyint(4) DEFAULT '0' COMMENT '来源类型 0原创 1收录',
  `src_url` varchar(250) DEFAULT '' COMMENT '源地址',
  `top_time` int(10) unsigned DEFAULT '0' COMMENT '置顶时间 0为未置顶',
  `top_title` varchar(250) DEFAULT '' COMMENT '置顶时知识信息标题',
  `top_cover` varchar(250) DEFAULT '' COMMENT '置顶时知识信息配图',
  `top_coverinfo`  varchar(250) DEFAULT '' COMMENT '置顶时知识信息配图说明',
  `rcmd_time` int(10) unsigned DEFAULT '0' COMMENT '推荐时间 0为未推荐',
  `rcmd_title` varchar(250) DEFAULT '0' COMMENT '推荐时知识信息标题',
  `rcmd_cover` varchar(250) DEFAULT '0' COMMENT '推荐时知识信息配图',
  `rcmd_coverinfo` varchar(250) DEFAULT '' COMMENT '推荐时知识信息配图说明',
  `tags` varchar(200) DEFAULT '' COMMENT '标签名称 空格分隔',
  `rel_news` text COLLATE utf8mb4_unicode_ci COMMENT '相关资讯',
  `rel_house` text COLLATE utf8mb4_unicode_ci COMMENT '相关楼盘 {city:,hid:,type:House|Esf|Fitment}',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='知识信息数据表';

DROP TABLE IF EXISTS `knowledge_history`;
CREATE TABLE `knowledge_history` (
  `pkid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '知识历史版本编号',
  `id` int(10) unsigned NOT NULL COMMENT '知识内容主键编号',
  `cateid` smallint(5) unsigned DEFAULT '0' COMMENT '知识所属的分类',
  `catepath` varchar(20) DEFAULT '0' COMMENT '知识所属的分类路径 0-cid-cid-cid',
  `version` int(10) unsigned NOT NULL COMMENT '版本保存日期',
  `status` tinyint(4) DEFAULT '0' COMMENT '信息状态 0未审核 1审核通过(未发布) 9为已发布 -1为删除',
  `title` varchar(250) NOT NULL COMMENT '知识信息标题',
  `content` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '知识内容',
  `cover` varchar(250) DEFAULT '' COMMENT '知识配图',
  `coverinfo` varchar(250) DEFAULT '' COMMENT '知识配图说明',
  `editorid` bigint(20) unsigned DEFAULT '0' COMMENT '编辑用户编号',
  `editor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '作者姓名,用于显示',
  `ctime` int(10) unsigned NOT NULL COMMENT '知识创建时间',
  `ptime` int(10) unsigned NOT NULL COMMENT '知识定时发布时间 默认与ctime时间一致',
  `utime` int(10) unsigned NOT NULL COMMENT '知识版本更新时间',
  `scope` varchar(20) DEFAULT '' COMMENT '数据所属城市',
  `src_type` tinyint(4) DEFAULT '0' COMMENT '来源类型 0原创 1收录',
  `src_url` varchar(250) DEFAULT '' COMMENT '源地址',
  `top_time` int(10) unsigned DEFAULT '0' COMMENT '置顶时间 0为未置顶',
  `top_title` varchar(250) DEFAULT '' COMMENT '置顶时知识信息标题',
  `top_cover` varchar(250) DEFAULT '' COMMENT '置顶时知识信息配图',
  `top_coverinfo`  varchar(250) DEFAULT '' COMMENT '置顶时知识信息配图说明',
  `rcmd_time` int(10) unsigned DEFAULT '0' COMMENT '推荐时间 0为未推荐',
  `rcmd_title` varchar(250) DEFAULT '0' COMMENT '推荐时知识信息标题',
  `rcmd_cover` varchar(250) DEFAULT '0' COMMENT '推荐时知识信息配图',
  `rcmd_coverinfo`  varchar(250) DEFAULT '' COMMENT '推荐时知识信息配图说明',
  `tags` varchar(200) DEFAULT '' COMMENT '标签名称 空格分隔',
  `rel_news` text COLLATE utf8mb4_unicode_ci COMMENT '相关资讯',
  `rel_house` text COLLATE utf8mb4_unicode_ci COMMENT '相关楼盘 {city:,hid:,type:House|Esf|Fitment}',
  PRIMARY KEY (`pkid`),
  KEY `FK_HISTORY` (`id`,`ptime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='知识信息历史数据表';


# 索引结构
PUT /knowledge_logic
{
  "mappings": {
     "knowledge": {
        "dynamic": "strict",
        "_all": {
           "enabled": false
        },
        "properties": {
           "_category": {
              "type": "string",
              "index": "not_analyzed",
              "store": true
           },
           "_content": {
              "type": "string",
              "store": true,
              "analyzer": "ik_max_word"
           },
           "_deleted": {
              "type": "boolean",
              "store": true
           },
           "_doccreatetime": {
              "type": "date",
              "store": true,
              "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
           },
           "_docupdatetime": {
              "type": "date",
              "store": true,
              "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
           },
           "_hits": {
              "type": "integer",
              "store": true
           },
           "_multi": {
              "properties": {
                 "editor": {
                    "type": "string",
                    "store": true,
                    "term_vector": "with_positions_offsets",
                    "analyzer": "keyword"
                 },
                 "editorid": {
                    "type": "integer",
                    "store": true
                 },
                 "cateid": {
                    "type": "integer",
                    "store": true
                 },
                 "catepath": {
                    "type": "string",
                    "store": true,
                    "term_vector": "with_positions_offsets",
                    "analyzer": "keyword"
                 },
                 "rcmd_time": {
                    "type": "date",
                    "store": true,
                    "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
                 },
                 "src_type": {
                    "type": "integer",
                    "store": true
                 },
                 "title_prefix": {
                    "type": "string",
                    "store": true,
                    "term_vector": "with_positions_offsets",
                    "analyzer": "keyword"
                 },
                 "title_firstletter": {
                    "type": "string",
                    "store": true,
                    "analyzer": "pinyin_first_lette_analyzer",
                    "search_analyzer": "keyword"
                 },
                 "title_pinyin": {
                    "type": "string",
                    "store": true,
                    "analyzer": "pinyin_analyzer"
                 },
                 "top_time": {
                    "type": "date",
                    "store": true,
                    "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
                 }
              }
           },
           "_origin": {
              "type": "object",
              "enabled": false
           },
           "_scope": {
              "type": "string",
              "index": "not_analyzed",
              "store": true
           },
           "_tags": {
              "type": "string",
              "store": true,
              "analyzer": "whitespace"
           },
           "_title": {
              "type": "string",
              "store": true,
              "analyzer": "ik_max_word"
           },
           "_url": {
              "type": "string",
              "index": "no",
              "store": true
           }
        }
     }
  },
  "settings": {
     "index": {
        "number_of_shards": "3",
        "requests": {
           "cache": {
              "enable": "true"
           }
        },
        "analysis": {
           "filter": {
              "my_synonym": {
                 "type": "synonym",
                 "synonyms_path": "../plugins/elasticsearch-analysis-ik-1.9.3/config/ik/custom/synonym.dic"
              }
           },
           "analyzer": {
              "searchanalyzer": {
                 "filter": "my_synonym",
                 "type": "custom",
                 "tokenizer": "ik_smart"
              },
              "pinyin_first_lette_analyzer": {
                 "filter": [
                    "standard"
                 ],
                 "tokenizer": "pinyin_first_letter"
              },
              "indexanalyzer": {
                 "filter": "my_synonym",
                 "type": "custom",
                 "tokenizer": "ik_max_word"
              },
              "pinyin_analyzer": {
                 "filter": [
                    "standard"
                 ],
                 "tokenizer": "pinyin_full"
              }
           },
           "tokenizer": {
              "pinyin_first_letter": {
                 "padding_char": "",
                 "type": "pinyin",
                 "first_letter": "only"
              },
              "pinyin_full": {
                 "padding_char": "",
                 "type": "pinyin",
                 "first_letter": "none"
              }
           }
        },
        "number_of_replicas": "1"
     }
  }
}


# Dump of table wiki 百科词条核心业务数据表
# ------------------------------------------------------------
TRUNCATE TABLE `wiki`;

DROP TABLE IF EXISTS `wiki`;
CREATE TABLE `wiki` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '百科词条主键编号',
  `version` int(10) unsigned NOT NULL COMMENT '版本保存日期',
  `status` tinyint(4) DEFAULT '0' COMMENT '信息状态 0未审核 1审核通过 9为已发布 -1为删除',
  `hits` int(10) DEFAULT '0' COMMENT '总访问量',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '百科词条名称',
  `pinyin` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '百科词条拼音全拼字母',
  `firstletter` char(1) COLLATE utf8mb4_unicode_ci DEFAULT '#' COMMENT '百科词条拼音首字母，在26个英文字母内的，使用大写，否则为#',
  `content` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '百科词条内容',
  `cateid` smallint(5) unsigned DEFAULT '0' COMMENT '百科词条所属的分类',
  `cover` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '百科词条配图',
  `coverinfo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '百科词条配图说明',
  `editorid` bigint(20) unsigned DEFAULT '0' COMMENT '编辑用户编号',
  `editor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '作者姓名,用于显示',
  `ctime` int(10) unsigned NOT NULL COMMENT '百科词条创建时间',
  `ptime` int(10) unsigned NOT NULL COMMENT '百科词条定时发布时间 默认与ctime时间一致',
  `utime` int(10) unsigned NOT NULL COMMENT '百科词条版本更新时间',
  `src_type` tinyint(4) DEFAULT '0' COMMENT '来源类型 0原创 1收录',
  `src_url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '源地址',
  `focus_time` int(10) unsigned DEFAULT '0' COMMENT '推荐到相关栏目的时间 0为未置顶',
  `focus_title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐百科词条焦点图标题',
  `focus_pic` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐百科词条焦点图配图',
  `focus_coverinfo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐时百科词条焦点图配图说明',
  `celebrity_time` int(10) unsigned DEFAULT '0' COMMENT '首页推荐时间 0为未推荐',
  `celebrity_title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '推荐时百科词条名人名称',
  `celebrity_pic` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '推荐时百科词条名人配图',
  `celebrity_coverinfo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐时百科词条名人配图说明',
  `company_time` int(10) DEFAULT '0' COMMENT '首页推荐时间 0为未推荐',
  `company_title` varchar(250) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '推荐时百科词条名企名称',
  `company_pic` varchar(250) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '推荐时百科词条名企配图',
  `company_coverinfo` varchar(250) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '推荐时百科词条名企配图说明',
  `tags` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '标签名称 空格分隔',
  `rel_news` text COLLATE utf8mb4_unicode_ci COMMENT '相关资讯',
  `rel_house` text COLLATE utf8mb4_unicode_ci COMMENT '相关楼盘 {city:,hid:,type:House|Esf|Fitment}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_WIKI` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='百科词条数据表';

DROP TABLE IF EXISTS `wiki_history`;
CREATE TABLE `wiki_history` (
  `pkid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '百科词条历史版本编号',
  `id` int(10) unsigned NOT NULL COMMENT '百科词条主键编号',
  `version` int(10) unsigned NOT NULL COMMENT '版本保存日期',
  `status` tinyint(4) DEFAULT '0' COMMENT '信息状态 0未审核 1审核通过 9为已发布 -1为删除',
  `hits` int(10) DEFAULT '0' COMMENT '总访问量',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '百科词条名称',
  `pinyin` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '百科词条拼音全拼字母',
  `firstletter` char(1) COLLATE utf8mb4_unicode_ci DEFAULT '#' COMMENT '百科词条拼音首字母，在26个英文字母内的，使用大写，否则为#',
  `content` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '百科词条内容',
  `cateid` smallint(5) unsigned DEFAULT '0' COMMENT '百科词条所属的分类',
  `cover` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '百科词条配图',
  `coverinfo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '百科词条配图说明',
  `editorid` bigint(20) unsigned DEFAULT '0' COMMENT '编辑用户编号',
  `editor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '作者姓名,用于显示',
  `ctime` int(10) unsigned NOT NULL COMMENT '百科词条创建时间',
  `ptime` int(10) unsigned NOT NULL COMMENT '百科词条定时发布时间 默认与ctime时间一致',
  `utime` int(10) unsigned NOT NULL COMMENT '百科词条版本更新时间',
  `src_type` tinyint(4) DEFAULT '0' COMMENT '来源类型 0原创 1收录',
  `src_url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '源地址',
  `focus_time` int(10) unsigned DEFAULT '0' COMMENT '推荐到相关栏目的时间 0为未置顶',
  `focus_title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐百科词条焦点图标题',
  `focus_pic` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐百科词条焦点图配图',
  `focus_coverinfo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐时百科词条焦点图配图说明',
  `celebrity_time` int(10) unsigned DEFAULT '0' COMMENT '首页推荐时间 0为未推荐',
  `celebrity_title` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '推荐时百科词条名人名称',
  `celebrity_pic` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '推荐时百科词条名人配图',
  `celebrity_coverinfo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '推荐时百科词条名人配图说明',
  `company_time` int(10) DEFAULT '0' COMMENT '首页推荐时间 0为未推荐',
  `company_title` varchar(250) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '推荐时百科词条名企名称',
  `company_pic` varchar(250) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '推荐时百科词条名企配图',
  `company_coverinfo` varchar(250) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '推荐时百科词条名企配图说明',
  `tags` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '标签名称 空格分隔',
  `rel_news` text COLLATE utf8mb4_unicode_ci COMMENT '相关资讯',
  `rel_house` text COLLATE utf8mb4_unicode_ci COMMENT '相关楼盘 {city:,hid:,type:House|Esf|Fitment}',
  PRIMARY KEY (`pkid`),
  KEY `FK_HISTORY` (`id`,`ptime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='百科词条历史数据表';

# 百科词条的索引结构
PUT /wiki_logic
{
   "mappings": {
      "wiki": {
         "dynamic": "strict",
         "_all": {
            "enabled": false
         },
         "properties": {
            "_category": {
               "type": "string",
               "index": "not_analyzed",
               "store": true
            },
            "_content": {
               "type": "string",
               "store": true,
               "analyzer": "ik_max_word"
            },
            "_deleted": {
               "type": "boolean",
               "store": true
            },
            "_doccreatetime": {
               "type": "date",
               "store": true,
               "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
            },
            "_docupdatetime": {
               "type": "date",
               "store": true,
               "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
            },
            "_hits": {
               "type": "integer",
               "store": true
            },
            "_multi": {
               "properties": {
                  "cateid": {
                     "type": "integer",
                     "store": true
                  },
                  "editor": {
                     "type": "string",
                     "store": true,
                     "term_vector": "with_positions_offsets",
                     "analyzer": "keyword"
                  },
                  "editorid": {
                     "type": "integer",
                     "store": true
                  },
                  "src_type": {
                     "type": "integer",
                     "store": true
                  },
                  "title_firstletter": {
                     "type": "string",
                     "store": true,
                     "analyzer": "pinyin_first_lette_analyzer",
                     "search_analyzer": "keyword"
                  },
                  "title_pinyin": {
                     "type": "string",
                     "store": true,
                     "analyzer": "pinyin_analyzer"
                  },
                  "title_prefix": {
                     "type": "string",
                     "store": true,
                     "term_vector": "with_positions_offsets",
                     "analyzer": "keyword"
                  },
                  "focus_time": {
                     "type": "date",
                     "store": true,
                     "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
                  },
                  "celebrity_time": {
                     "type": "date",
                     "store": true,
                     "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
                  },
                  "company_time": {
                     "type": "date",
                     "store": true,
                     "format": "strict_date_optional_time||epoch_millis||yyyy/MM/dd HH:mm:ss||yyyy/MM/dd"
                  }
               }
            },
            "_origin": {
               "type": "object",
               "enabled": false
            },
            "_scope": {
               "type": "string",
               "index": "not_analyzed",
               "store": true
            },
            "_tags": {
               "type": "string",
               "store": true,
               "analyzer": "whitespace"
            },
            "_title": {
               "type": "string",
               "store": true,
               "analyzer": "ik_max_word"
            },
            "_url": {
               "type": "string",
               "index": "no",
               "store": true
            }
         }
      }
   },
   "settings": {
      "index": {
         "number_of_shards": "3",
         "requests": {
            "cache": {
               "enable": "true"
            }
         },
         "analysis": {
            "filter": {
               "my_synonym": {
                  "type": "synonym",
                  "synonyms_path": "../plugins/elasticsearch-analysis-ik-1.9.3/config/ik/custom/synonym.dic"
               }
            },
            "analyzer": {
               "searchanalyzer": {
                  "filter": "my_synonym",
                  "type": "custom",
                  "tokenizer": "ik_smart"
               },
               "pinyin_first_lette_analyzer": {
                  "filter": [
                     "standard"
                  ],
                  "tokenizer": "pinyin_first_letter"
               },
               "indexanalyzer": {
                  "filter": "my_synonym",
                  "type": "custom",
                  "tokenizer": "ik_max_word"
               },
               "pinyin_analyzer": {
                  "filter": [
                     "standard"
                  ],
                  "tokenizer": "pinyin_full"
               }
            },
            "tokenizer": {
               "pinyin_first_letter": {
                  "padding_char": "",
                  "type": "pinyin",
                  "first_letter": "only"
               },
               "pinyin_full": {
                  "padding_char": "",
                  "type": "pinyin",
                  "first_letter": "none"
               }
            }
         },
         "number_of_replicas": "1"
      }
   }
}
PUT /suggest.wiki
{
  "mappings": {
    "word": {
      "dynamic": "false",
      "properties": {
        "hits": {
          "type": "integer",
          "store": true
        },
        "refs": {
          "type": "nested"
        },
        "scores": {
          "type": "integer",
          "store": true
        },
        "word": {
          "type": "string",
          "term_vector": "with_positions_offsets",
          "fields": {
            "firstletter": {
              "type": "string",
              "analyzer": "pinyin_first_lette_analyzer",
              "search_analyzer": "keyword"
            },
            "primitive": {
              "type": "string",
              "store": true,
              "analyzer": "keyword"
            }
          },
          "analyzer": "pinyin_analyzer"
        }
      }
    }
  },
  "settings": {
    "index": {
      "number_of_shards": "1",
      "requests": {
        "cache": {
          "enable": "true"
        }
      },
      "analysis": {
        "analyzer": {
          "pinyin_first_lette_analyzer": {
            "filter": [
              "standard"
            ],
            "tokenizer": "pinyin_first_letter"
          },
          "pinyin_analyzer": {
            "filter": [
              "standard"
            ],
            "tokenizer": "pinyin_full"
          }
        },
        "tokenizer": {
          "pinyin_first_letter": {
            "padding_char": "",
            "type": "pinyin",
            "first_letter": "only"
          },
          "pinyin_full": {
            "padding_char": "",
            "type": "pinyin",
            "first_letter": "none"
          }
        }
      },
      "number_of_replicas": "1"
    }
  }
}


# Dump of table categories
# ------------------------------------------------------------
# 获取 顶级可见栏目 操作用例
# SELECT `id`, `path`, `name`, `iorder`
# FROM `categories` 
# WHERE `type`='kb' AND `status`=0 AND `parent`=0
# ORDER BY `iorder` ASC

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目主键编号',
  `type` enum('kb','qa','wiki') DEFAULT 'kb' COMMENT '栏目类型 kb为知识 qa为问答 wiki为百科',
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '栏目状态 0为可用 1为隐藏',
  `parent` smallint(5) unsigned DEFAULT '0' COMMENT '栏目父级编号 0表示父级为根',
  `level` tinyint(3) unsigned DEFAULT '1' COMMENT '栏目级别序号 自然数',
  `iorder` tinyint(3) unsigned DEFAULT '1' COMMENT '栏目在同一分类下的顺序编号',
  `name` varchar(20) DEFAULT NULL COMMENT '栏目名称',
  `code` varchar(10) DEFAULT NULL COMMENT '栏目url代号，可用于伪静态地址使用',
  `path` varchar(20) DEFAULT NULL COMMENT '栏目栏目全路径 0-id-id-id这样的格式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='栏目分类数据';


# Dump of table members
# 会员
# ------------------------------------------------------------

DROP TABLE IF EXISTS `members`;

CREATE TABLE `members` (
  `uid` bigint(20) unsigned NOT NULL COMMENT '会员主键编号',
  `username` varchar(20) DEFAULT NULL COMMENT '用户名',
  `realname` varchar(20) DEFAULT NULL COMMENT '用户姓名',
  `headurl` varchar(250) DEFAULT NULL COMMENT '用户头像',
  `ctime` int(10) unsigned DEFAULT '0' COMMENT '用户创建时间',
  `phone` varchar(20) DEFAULT '' COMMENT '电话号码',
  `sign` char(32) DEFAULT '' COMMENT '签名',
  `expire` int(10) unsigned DEFAULT '0' COMMENT '过期时间',
  `score` int(10) unsigned DEFAULT '0' COMMENT '积分',
  `role` tinyint(4) unsigned DEFAULT '10' COMMENT '会员角色 10普通会员 20经济人 30编辑人员 99系统管理员',
  `level` tinyint(4) unsigned DEFAULT '0' COMMENT '会员级别',
  `last_post` int(10) unsigned DEFAULT '0' COMMENT '最后提问时间',
  `last_reply` int(10) unsigned DEFAULT '0' COMMENT '最后回答时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员业务数据表';



# Dump of table oplogs
# 用于问答系统
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oplogs`;

CREATE TABLE `oplogs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '业务主键',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作用户',
  `act` tinyint(4) unsigned DEFAULT NULL COMMENT '操作类型 11问题关注 21最佳回复 31好评 32差评',
  `relid` int(10) unsigned NOT NULL COMMENT '操作信息编号',
  `ctime` int(10) unsigned NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNI_INDEX_KEY` (`uid`,`act`,`relid`),
  KEY `FK_QUESTIONID` (`act`,`relid`,`ctime`),
  KEY `INX_USERACTION` (`uid`,`act`,`ctime`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='问答操作日志表';



# Dump of table questions
# 用于问答系统
# ------------------------------------------------------------
DROP TABLE IF EXISTS `question`;

CREATE TABLE `question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '业务主键',
  `_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '唯一主键',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '问题标题',
  `desc` text COLLATE utf8mb4_unicode_ci COMMENT '问题描述',
  `tags` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '问题标签, 以空格分隔',
  `scope` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '问题涉及的地域 默认全部',
  `ctime` int(10) unsigned NOT NULL COMMENT '提问时间',
  `utime` int(10) unsigned DEFAULT NULL COMMENT '最新回复时间',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '提问的用户',
  `status` tinyint(4) DEFAULT '0' COMMENT '问题状态 0正常已审核(可显示) 1未审核(不显示) 9已删除(不显示)',
  `source` tinyint(4) DEFAULT '0' COMMENT '数据来源 0为乐居 1链家 2搜房',
  `is_crawl` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '是否爬取回来的数据',
  `i_attention` smallint(5) unsigned DEFAULT '0' COMMENT '关注数量',
  `i_hits` int(10) unsigned DEFAULT '0' COMMENT '点击次数',
  `i_replies` smallint(5) unsigned DEFAULT '0' COMMENT '有效回复数量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_id` (`_id`),
  KEY `INX_STATUS` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=42035 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='问题数据表';

# Dump of table answers
# 用于问答系统
# ------------------------------------------------------------
DROP TABLE IF EXISTS `answers`;
CREATE TABLE `answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '业务主键',
  `question_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '问题唯一主键关联',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '回复的用户',
  `reply` text COLLATE utf8mb4_unicode_ci COMMENT '问题回复内容',
  `ctime` int(10) unsigned NOT NULL COMMENT '回复时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '回复状态 0正常已审核(可显示) 1未审核(不显示) 9已删除(不显示)',
  `is_best` tinyint(4) unsigned DEFAULT '0' COMMENT '是否最佳回复',
  `i_good` smallint(5) unsigned DEFAULT '0' COMMENT '好评数量',
  `i_bad` smallint(5) unsigned DEFAULT '0' COMMENT '差评数量',
  `source` tinyint(4) DEFAULT '0' COMMENT '数据来源 0为乐居 1链家 2搜房',
  `is_crawl` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '是否爬取回来的数据',
  PRIMARY KEY (`id`),
  KEY `FK_QUESTIONID` (`question_id`),
  KEY `INX_STATUS` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=51806 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='问题回复数据表';



# Dump of table search_stats
# 搜索统计
# ------------------------------------------------------------

DROP TABLE IF EXISTS `search_stats`;
CREATE TABLE `search_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '业务主键',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作用户',
  `reltype` enum('_','kb','qa','wiki') DEFAULT '_' COMMENT '被搜索的数据类型 _为通用 kb为知识 qa为问答 wiki为百科',
  `source` tinyint(4) unsigned DEFAULT NULL COMMENT '数据来源 0通用入口 11移动 12PC',
  `keyword` varchar(50) NOT NULL COMMENT '搜索时的关键词',
  `ctime` int(10) unsigned NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `INX_SRCTIMESTAT` (`source`,`ctime`,`keyword`),
  KEY `INX_SRCWORDSTAT` (`source`,`keyword`,`ctime`),
  KEY `INX_WORDTIMESTAT` (`keyword`,`ctime`),
  KEY `INX_TIMEWORDSTAT` (`ctime`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关键词数据统计数据表';


# Dump of table visit_stats
# 访问统计 @2016-10-25
# ------------------------------------------------------------

DROP TABLE IF EXISTS `visit_stats`;
CREATE TABLE `visit_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '访问统计主键',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作用户',
  `reltype` enum('kb','qa','wiki') DEFAULT 'kb' COMMENT '被访问的数据类型 kb为知识 qa为问答 wiki为百科',
  `relid` int(10) unsigned DEFAULT '0' COMMENT '被访问的数据编号',
  `relcateid` varchar(20) DEFAULT '0-' COMMENT '同 knowledge.catepath 被访问的数据所在分类',
  `ctime` int(10) unsigned NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  # 按时间划分的子系统内容统计数据
  # 用于支持管理后台的数据图表 1. 七天热度趋势 2. 七天热门排行
  KEY `INX_STATTYPE` (`ctime`,`reltype`,`relid`),
  # 用于支持管理后台的数据图表 1. 七天栏目热度趋势
  KEY `INX_STATCATE` (`ctime`,`reltype`,`relcateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='访问统计数据表';



# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '业务主键',
  `name` varchar(20) DEFAULT NULL COMMENT '标签名称',
  `i_total` int(10) unsigned DEFAULT '0' COMMENT '标签关联的问题数量',
  `source` tinyint(4) unsigned DEFAULT '0' COMMENT '标签来源 0为乐居自有 1为乐居标签库 150为链家 151为搜房',
  `status` tinyint(4) DEFAULT '0' COMMENT '标签状态 0可用 1禁用 9删除',
  PRIMARY KEY (`id`),
  KEY `INX_TAGNAME` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COMMENT='标签数据表';


# Dump of table data_statistics
# 统计结果数据表 @2016-11-05
# ------------------------------------------------------------

DROP TABLE IF EXISTS `data_statistics`;
CREATE TABLE `data_statistics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '统计数据主键',
  `reltype` enum('kb','qa','wiki') NOT NULL DEFAULT 'kb' COMMENT '统计分类 kb为知识 qa为问答 wiki为百科',
  `ctime` int(10) unsigned NOT NULL COMMENT '统计数据生成时间 使用strtotime(Y-m-d)',
  `chartid` varchar(20) NOT NULL COMMENT '图表编号 通过业务逻辑自定义',
  `data` mediumtext NOT NULL COMMENT '统计数据结果',
  PRIMARY KEY (`id`),
  KEY `TYPETIME` (`reltype`,`ctime`),
  KEY `TIME` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='统计数据表';



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

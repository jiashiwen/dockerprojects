# Indeces Definition

## 结构定义

### 知识索引结构定义

业务名称 : `knowledge`

* _id 规则使用知识 id
* 索引中只保留正常可见已发布的知识内容

Mapping Scheme :
```
{
   "knowledge_logic": {
      "aliases": {},
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
            "creation_date": "1476946425647",
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
            "number_of_replicas": "1",
            "uuid": "ZZn-FIcTQ52nbJ_S0XRKYg",
            "version": {
               "created": "2030399"
            }
         }
      },
      "warmers": {}
   }
}
```


### 问答系统索引结构定义

业务名称 : `question`

Mapping Scheme :
```
{
   "question_logic": {
      "aliases": {},
      "mappings": {
         "question": {
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
               },
               "_multi": {
                  "properties": {
                     "status": {
                        "type": "integer",
                        "store": true
                     },
                     "i_replies": {
                        "type": "integer",
                        "store": true
                     },
                     "i_attentions": {
                        "type": "integer",
                        "store": true
                     },
                     "uid": {
                        "type": "string",
                        "index": "not_analyzed"
                     }
                  }
               },
               "_origin": {
                  "type": "object",
                  "enabled": false
               }
            }
         }
      },
      "settings": {
         "index": {
            "number_of_shards": "3",
            "creation_date": "1474276239096",
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
                  "indexanalyzer": {
                     "filter": "my_synonym",
                     "type": "custom",
                     "tokenizer": "ik_max_word"
                  }
               }
            },
            "number_of_replicas": "1"
         }
      }
   }
}
```

----

## 调用接口

1. 添加知识内容

2. 查询知识内容

3. 更新知识内容 (状态更新, 假删除)



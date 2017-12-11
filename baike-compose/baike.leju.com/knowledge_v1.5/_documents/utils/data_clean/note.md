# note

## DB Clean

```
truncate table `knowledge_history`;
truncate table `knowledge`;
truncate table `categories`;
truncate table `oplogs`;
truncate table `visit_stats`;
truncate table `data_statistics`;
truncate table `wiki_history`;
truncate table `wiki`;
truncate table `search_stats`;
```

## 缓存

```
flushall
```


## Indeces Clean

1. 删除

```
DELETE knowledge_logic
```

2. 重建

```
PUT knowledge_logic {
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
                  "title_prefix": {
                     "type": "string",
                     "store": true,
                     "term_vector": "with_positions_offsets",
                     "analyzer": "keyword"
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
```

3. 参考

``` 
GET /knowledge_logic
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
                     "title_prefix": {
                        "type": "string",
                        "store": true,
                        "term_vector": "with_positions_offsets",
                        "analyzer": "keyword"
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
            "creation_date": "1479870495280",
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
            "uuid": "gqV3S3m9QjuFRi3lM2w_Rg",
            "version": {
               "created": "2030399"
            }
         }
      },
      "warmers": {}
   }
}
```

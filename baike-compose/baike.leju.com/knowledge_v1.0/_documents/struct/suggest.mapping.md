# suggest mapping setting

PUT suggest.lejutag
{
	"aliases": {},
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
				"firstletter": {
					"type": "string",
					"store": true,
					"analyzer": "pinyin_first_lette_analyzer",
					"search_analyzer": "keyword"
				},
				"pinyin": {
					"type": "string",
					"store": true,
					"analyzer": "pinyin_analyzer"
				},
				"word": {
					"type": "string",
					"store": true,
					"term_vector": "with_positions_offsets",
					"analyzer": "keyword"
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
			"number_of_replicas": "1",
		}
	}
}

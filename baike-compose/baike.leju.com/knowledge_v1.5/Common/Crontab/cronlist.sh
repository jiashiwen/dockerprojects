#!/bin/bash

TIME_NOW=`date +"%Y-%m-%d %H:%M:%S"`
export PROJECT_DOMAIN="dev.admin.baike.leju.com"
export CRON_LOGFILE="/tmp/crontab.log"

# 定时任务 - 知识 - 定时发布
echo "[$TIME_NOW] 知识-定时发布"
API="http://$PROJECT_DOMAIN/cron/timerpublish"
echo $API
curl $API >> $CRON_LOGFILE

# 定时任务 - 知识 - 统计数据
echo "[$TIME_NOW] 知识-统计数据"
API="http://$PROJECT_DOMAIN/cron/trendchart"
echo $API
curl $API >> $CRON_LOGFILE



#!/bin/bash

export HOST="http://10.204.12.21:8010"
export DOMAIN="api.baike.leju.com"
export CURRENT=$(date +'%Y%m%d')
export LOG="/home/leju/baike/logs/cron_$CURRENT.log"


# 业务使用的 Crontab 设定
# 1. 定时发布处理
#* * * * * sh /home/leju/baike/baike.leju.com/knowledge/_documents/utils/crontab/cron_prod.sh publish
#* 1 * * * sh /home/leju/baike/baike.leju.com/knowledge/_documents/utils/crontab/cron_prod.sh count

arg="$1"
case $arg in
count)
	echo "stats knowledge data for dashboard chart"
	# 2. update dashboard chart data ( 1:00 am in every day )
	# * 1 * * * 
	#echo "curl -s $HOST/Cron/trendchart -H 'Host: $DOMAIN'"
	curl -s "$HOST/Cron/trendchart" -H "Host: $DOMAIN" >> $LOG
	echo " | trendchart Done!" >> $LOG
	echo "" >> $LOG
	curl -s "$HOST/Cron/hotWords" -H "Host: $DOMAIN" >> $LOG
	echo " | hotWords Done!" >> $LOG
	echo "" >> $LOG
	echo ""
	;;
publish)
	echo "check the on-time publish"
	# 1. on time publish document ( every minute )
	# * * * * * 
	#echo "curl -s $HOST/Cron/timerpublish -H 'Host: $DOMAIN'"
	curl -s "$HOST/Cron/timerpublish" -H "Host: $DOMAIN" >> $LOG
	echo " | timerpublish Done" >> $LOG
	echo "" >> $LOG
	echo ""
	;;
*)	# undefined $arg
	echo "unkonw argument"
	exit 1
esac


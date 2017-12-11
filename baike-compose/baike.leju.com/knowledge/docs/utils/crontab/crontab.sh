#!/bin/bash

export HOST="http://10.204.12.34"
export DOMAIN="dev.api.baike.leju.com"
export CURRENT=$(date +'%Y%m%d')
export LOG="/data/vhosts/test-knowledge/logs/cron_$CURRENT.log"



arg="$1"
case $arg in
count)
	echo "stats knowledge data for dashboard chart"
	# 2. update dashboard chart data ( 1:00 am in every day )
	# * 1 * * * 
	#echo "curl -s $HOST/Cron/trendchart -H 'Host: $DOMAIN'"
	curl -s "$HOST/Cron/trendchart" -H "Host: $DOMAIN"
	curl -s "$HOST/Cron/hotWords" -H "Host: $DOMAIN"
	echo ""
	;;
publish)
	echo "check the on-time publish"
	# 1. on time publish document ( every minute )
	# * * * * * 
	#echo "curl -s $HOST/Cron/timerpublish -H 'Host: $DOMAIN'"
	curl -s "$HOST/Cron/timerpublish" -H "Host: $DOMAIN"
	echo ""
	;;
*)	# undefined $arg
	echo "unkonw argument"
	exit 1
esac


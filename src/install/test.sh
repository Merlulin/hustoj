#!/bin/bash
DATE=`date +%Y%m%d`
OLD=`date -d"1 day ago" +"%Y%m%d"`
OLD3=`date -d"3 day ago" +"%Y%m%d"`
config=`find -name judge.conf`
SERVER=`cat $config|grep 'OJ_HOST_NAME' |awk -F= '{print $2}'`
USER=`cat $config|grep 'OJ_USER_NAME' |awk -F= '{print $2}'`
PASSWORD=`cat $config|grep 'OJ_PASSWORD' |awk -F= '{print $2}'`
DATABASE=`cat $config|grep 'OJ_DB_NAME' |awk -F= '{print $2}'`
mysql -h $SERVER $DATABASE -u$USER -p$PASSWORD

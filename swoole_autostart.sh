#!/bin/bash
count=`ps -fe |grep "swoole" | grep -v "grep" | grep "Master" | wc -l`
if [ $count -lt 1 ];
then
	/home/myswoole/killswoole.sh
	/usr/local/php/bin/php  /home/myswoole/server.php
	#echo $(date +%Y-%m-%d_%H:%M:%S) >/root/html/swoole.myinit.com/logs/restart.log
	echo "start ok!!"
else
	echo '已启动'
fi

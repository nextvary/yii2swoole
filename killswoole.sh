#!/bin/bash
for i in `ps -eaf |grep swoole |grep -v sh |grep -v grep | awk '{print $2}'`
do
	echo "kill swoole pid: [ $i ]"
	kill -9 $i
done
ulimit -c unlimited

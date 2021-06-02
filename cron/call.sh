#!/bin/sh

cd `dirname $0`

FILE=./run/stamp
L=`cat $FILE`
D=`date "+%H:%M"`

if [ "$L" = "$D" ]; then
  cd /home/banchou/www/hook
  php h.php
else
	:
fi


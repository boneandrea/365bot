#!/bin/zsh


cd `dirname $0`

FILE=./run/stamp

N=`expr $RANDOM % 60`
NN=$(( 60*21 + 30 + $N ))

AH=$(( $NN / 60))
AM=$(( $NN % 60))
M=`printf "%s:%02d\n" $AH $AM`
echo "$M" >| $FILE
exit


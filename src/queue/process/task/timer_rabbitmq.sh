#!/bin/bash  
pgrep -x rabbitmq-server &> /dev/null

if [ $? -ne 0 ]

then

echo "At time: `date` :rabbitmq error stop .">> /var/log/rabbitmqCheck.log

/etc/init.d/rabbitmq-server start
#echo "At time: `date` :rabbitmq server is stop."  

else
echo "rabbitmq server is running ." >> /var/log/rabbitmqCheck.log  
fi
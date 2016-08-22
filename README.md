# phpmon
A lite weight server monitoring daemon written in php.


==MASTER DEPLOYMENT==

<pre>

1. clone app from git
git clone git@github.com:teamleadstech/phpmon.git

2. config app
cp config.example.php config.php
vim config.php

3. vhost config
<VirtualHost *:80>
	ServerName mon.tldemo.us
	DocumentRoot /var/www/vhost/sam/phpmon/www
	ErrorLog logs/mon.tldemo.us-error_log
	CustomLog logs/mon.tldemo.us-access_log combinedextended
	KeepAlive On
	<Directory /var/www/vhost/sam/phpmon/www>
		DirectoryIndex index.php
		AllowOverride All
		Order allow,deny
		Allow from all
	</Directory>
</VirtualHost>

4. sudo vim /etc/crontab
*/5 * * * * root cd /var/www/vhost/sam/phpmon/cron && php run.php pullnode >> /tmp/phpmon/node_load_pulling.log 2>&1

</pre>

==NODE DEPLOYMENT==

<pre>

1. clone app from git
git clone git@github.com:teamleadstech/phpmon.git

2. config app

cp config.example.php config.php

vim config.php
# Node only need to fill [Basic config] + [Node Config]

cd cron

php run.php genbash

#copy the output, you need it in step 3. eg. /var/www/vhost/sam/phpmon/cron/get_load

3. config xinetd
yum install xinetd

cat /etc/xinetd.conf

vim /etc/services
phpmon           3333/tcp               # phpmon

vim /etc/xinetd.d/phpmon

service phpmon
{
    disable = no
    socket_type = stream
    protocol = tcp
    only_from = 104.196.29.218 localhost
    port = 3333
    wait = no
    user = root
    server = /var/www/app/phpmon/cron/get_load
    instances = 20
}

service xinetd restart

netstat -na | grep 3333

vim /etc/sysconfig/iptables

-A MOJO-INPUT -m tcp -p tcp -s 104.196.29.218 --dport 3333 -m comment --comment PROD_LOAD_MON -j ACCEPT

service iptables restart

</pre>
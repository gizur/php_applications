Introduction
-----------

This repo contains the bulk of of the Gizur Saas setup. Some parts of managed
through the nodejs_applications repo.

 * Install with `make install2`
 * Build documentation with `make docs2`
 * Run unit tests with `make tests2`
 * Check code quality with `make lint2`
 * Check test coverage with `make coverage2`


See the Wiki for more information: https://github.com/gizur/gizurcloud/wiki


NOTE: The old README file has been placed here:
https://github.com/gizur/gizurcloud/wiki/Development_Guide%23old_php_applications_readme


Setup development environment
------------------------------

Pre-requisites:

 * Virtualbox
 * Vagrant (found at vagrantup.com)

Install and start a development envinment running `vagrant up vb`. Stop the virtual machine with `vagrant halt vb`.
The machine has docker.io and hipache installed. A sciprt is used for simplfying the management of docker and hipache,
see https://github.com/colmsjo/jacc.


It is also possible to run the machine in AWS:

```
# Install AWS plusin
vagrant plugin install vagrant-aws
vagrant plugin list

# A dummy box is needed
vagrant box add dummy https://github.com/mitchellh/vagrant-aws/raw/master/dummy.box

# Set this variable to use AWS, it should bot be set to use a local Virtualbox instead
export VAGRANT_AWS='Yes'

# These environment variables need to be set, put in bashrc/bach_profile env
# NOTE: Only the region us-east-1 seams to work at the moment.
export AWS_API_KEY=...
export AWS_API_SECRET=...
export AWS_PRIVATE_KEY_PATH=...
export AWS_KEYAIR_NAME=...
export AWS_REGION=...

vagrant up aws
```


Troubleshooting
---------------


Q: The Elastic Beanstalk application server runs out of disk space and has to be restarted.

A: The file `/var/log/httpd/error_log` grows quickly. The reason is that vtiger
print this error message over and over:
`[Mon Mar 23 10:20:31 2015] [error] [client 127.0.0.1] PHP Notice:  Undefined index: module in /var/app/current/lib/vtiger-5.4.0/include/utils/utils.php on line 1018`

One solution is to reduce the level of logging in apache. Change `LogLevel warn`
and `LogLevel info` entries to `LogLevel emerg` in `/etc/httpd/conf/httpd.conf`

Another is to setup a cronjob that removes the error log and then reboots like
this:

    # Run job every five minutes - only for testing
    echo '*/2 * * * *  /bin/bash -c "rm /var/log/httpd/error_log"' > ~/mycron
    echo '*/2 * * * *  /bin/bash -c "/sbin/reboot"' >> ~/mycron
    sudo crontab ~/mycron
    sudo crontab -l

    # Run job 00:00 every day and then reboot
    echo '0 0 * * *  /bin/bash -c "rm /var/log/httpd/error_log"' > ~/mycron
    echo '1 0 * * *  /bin/bash -c "/sbin/reboot"' >> ~/mycron
    sudo crontab ~/mycron
    sudo crontab -l


See `cron` output in `/var/spool/mail/root`


Q: Is it possible to install enhanced monitoring?

A: Use webmin. Install by logging in to the EBT server and put this in
`setup.sh` and run the script:

```
#!/bin/bash

cat <<EOF > /server-monitor.sh
#!/bin/bash
who -a > /tmp/monitor.txt
df >> /tmp/monitor.txt
cat /tmp/monitor.txt|mutt -s "Monitor alert!" admin@example.com
EOF

chmod +x /server-monitor.sh

yum install -y mutt
yum install -y perl-Authen-PAM
wget -O /webmin-1.740.tar.gz "http://downloads.sourceforge.net/project/webadmin/webmin/1.740/webmin-1.740.tar.gz?r=http%3A%2F%2Fwww.webmin.com%2Fdownload.html&ts=1427200686&use_mirror=heanet"
sleep 5
tar -xvzf /webmin-1.740.tar.gz
echo -e "\n\n\n10000\nadmin\nadmin\nadmin\ny\n" | ./webmin-1.740/setup.sh
```
Make sure that the port `10000` is open in the
firewall. Login at port `10000` and select `Others->System and Server Status` and add
monitor of type `disk space`. Set `percentage of total` to `20% `etc. The script
`/server-monitor.sh` will send a mail with extended information.
Update the script with the mail-address you want to use. Select
`Scheduled Monitoring` and setup a monitoring schedule. Also set
`send mail any time service is down` to make sure mails are sent.

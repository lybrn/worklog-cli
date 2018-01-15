#! /bin/bash
composer install
chmod +x "`pwd`/worklog"
ln -s "`pwd`/worklog" "/usr/local/bin/worklog"
